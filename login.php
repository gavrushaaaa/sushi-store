<?php
session_start();

// Подключение к базе данных SQLite
try {
    $pdo = new PDO("sqlite:database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка при подключении к базе данных: " . $e->getMessage();
    exit();
}

// Обработка формы входа
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo "Ошибка: все поля должны быть заполнены!";
    } else {
        $result = loginUser($username, $password);
        echo $result;

        if ($result == "Вход успешен!") {
            // Установка сессии для пользователя
            $_SESSION['username'] = $username;

            // Проверка, является ли пользователь администратором
            $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['is_admin']) {
                $_SESSION['admin'] = true;
                header("Location: admin_dashboard.php");
            } else {
                header("Location: profile.php");
            }
            exit();
        }
    }
}

function loginUser($username, $password) {
    try {
        global $pdo;

        // Получение данных пользователя из базы данных
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return "Вход успешен!";
        } else {
            return "Ошибка: неверное имя пользователя или пароль!";
        }
    } catch (PDOException $e) {
        return "Ошибка: " . $e->getMessage();
    }
}
?>