<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="main.html">Главная</a>
        <a href="about.html">О нас</a>
        <a href="sale.html">Акции </a>
        <a href="reviews.php">Отзывы</a>
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
    <ul class="cart-items">
        <?php foreach ($_SESSION['cart'] as $productId => $quantity): ?>
            <?php if (isset($products[$productId])): ?>
                <?php $totalPrice += $products[$productId]['price'] * $quantity; ?>
                <li class="cart-item">
                    <img src="<?php echo $products[$productId]['image']; ?>" alt="<?php echo $products[$productId]['name']; ?>" class="cart-item-image">
                    <div class="cart-item-info">
                        <h2><?php echo $products[$productId]['name']; ?></h2>
                        <p><?php echo $products[$productId]['price']; ?> ₽ x <?php echo $quantity; ?></p>
                        <form action="update_cart.php" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                            <button type="submit" name="action" value="decrease">-</button>
                            <span><?php echo $quantity; ?></span>
                            <button type="submit" name="action" value="increase">+</button>
                        </form>
                    </div>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <div class="total-price">
        <h2>Итоговая цена: <?php echo $totalPrice; ?> ₽</h2>
    </div>
    <a href="main.html" class="continue-shopping-button">Продолжить покупки</a>
    <a href="checkout.php" class="checkout-button">Оформить заказ</a>
</body>
</html>