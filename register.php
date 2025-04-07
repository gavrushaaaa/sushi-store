<?php
// Создание/подключение к базе данных SQLite и создание таблицы
try {
    $pdo = new PDO("sqlite:database.db"); // Изменено на database.db
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблицы
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY,
        username TEXT UNIQUE,
        password TEXT,
        is_admin INTEGER DEFAULT 0
    )");

    echo "База данных и таблица успешно созданы.<br>";
} catch (PDOException $e) {
    echo "Ошибка при создании базы данных: " . $e->getMessage() . "<br>";
    exit();
}

// Регистрация пользователя
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: main.html?error=empty_fields");
        exit();
    } else {
        $result = registerUser($username, $password);

        if ($result == "Регистрация успешна!") {
            header("Location: main.html?success=registered");
            exit();
        } else {
            header("Location: main.html?error=existing_user");
            exit();
        }
    }
}

function registerUser($username, $password) {
    try {
        global $pdo;

        // Проверяем, существует ли пользователь
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);

        if ($stmt->fetch()) {
            return "Ошибка: пользователь уже зарегистрирован!";
        }

        // Хешируем пароль
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Записываем пользователя
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->execute(['username' => $username, 'password' => $hashedPassword]);

        return "Регистрация успешна!";
    } catch (PDOException $e) {
        return "Ошибка: " . $e->getMessage();
    }
}
?>