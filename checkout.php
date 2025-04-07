<?php
session_start();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Подключение к базе данных SQLite
try {
    $pdo = new PDO("sqlite:database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение всех товаров
    $stmt = $pdo->query("SELECT * FROM products");
    $products = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $products[$row['id']] = $row;
    }
} catch (PDOException $e) {
    echo "Ошибка при подключении к базе данных: " . $e->getMessage();
    exit();
}

$totalPrice = 0;
foreach ($_SESSION['cart'] as $productId => $quantity) {
    if (isset($products[$productId])) {
        $totalPrice += $products[$productId]['price'] * $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="main.html">Главная</a>
        <a href="about.html">О нас</a>
        <a href="sale.html">Акции </a>
        <a href="profile.php" class="transparent-button">Личный кабинет</a>
        <a href="cart.php">Корзина🛒</a>
    </div>

    <div class="header">
        <div class="logo-text">
            <img src="images/logo.png" alt="sushi" class="logo">
            <div class="text-container">
                <h1>Кайдзен</h1>
                <h1 class="phone-number">+7 996 437 2020</h1>
            </div>
        </div>
    </div>
    <div class="checkout-container">
        <h2>Оформление заказа</h2>
        <p>Итоговая цена: <?php echo $totalPrice; ?> ₽</p>
        <form action="process_order.php" method="post" class="checkout-form">
            <div class="field">
                <label for="name">Имя:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="field">
                <label for="address">Адрес:</label>
                <input type="text" id="address" name="address" required>
            </div>
            <div class="field">
                <label for="phone">Телефон:</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <button type="submit" class="button1">Оформить заказ</button>
        </form>
    </div>
</body>
</html>