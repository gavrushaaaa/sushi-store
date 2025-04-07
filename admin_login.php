<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Подключение к базе данных SQLite
    try {
        $pdo = new PDO("sqlite:database.db");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Ошибка при подключении к базе данных: " . $e->getMessage();
        exit();
    }

    // Проверка учетных данных администратора
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND is_admin = 1");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $message = "Неверное имя пользователя или пароль!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход администратора</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="main.html">Главная</a>
        <a href="about.html">О нас</a>
        <a href="sale.html">Акции </a>
        <a class="transparent-button" onclick="openModal()">Регистрация</a>
        <a href="reviews.php">Отзывы</a>
        <a href="admin_login.php">Админ</a>
        <a href="profile.php" class="transparent-button">Личный кабинет</a>
        <a href="cart.php">Корзина🛒</a>
    </div>
    <div class="content">
        <h2>Вход администратора</h2>
        <?php if (!empty($message)) { echo '<p>' . $message . '</p>'; } ?>
        <form action="admin_login.php" method="post">
            <div class="field">
                <label for="username">Имя пользователя:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="field">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="button1">Войти</button>
        </form>
    </div>
</body>
</html>