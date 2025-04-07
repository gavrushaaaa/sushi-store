<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Подключение к базе данных SQLite
try {
    $pdo = new PDO("sqlite:database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка при подключении к базе данных: " . $e->getMessage();
    exit();
}

$message = '';

// Обновление информации о пользователе
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $newUsername = trim($_POST['username']);
    $newPassword = $_POST['password'];

    if (empty($newUsername) || empty($newPassword)) {
        $message = "Ошибка: все поля должны быть заполнены!";
    } else {
        $result = updateUser($username, $newUsername, $newPassword);
        $message = $result;

        if ($result == "Информация обновлена успешно!") {
            $_SESSION['username'] = $newUsername; // Обновляем имя пользователя в сессии
            header("Location: profile.php?success=1"); // Перенаправление для предотвращения повторной отправки формы
            exit();
        }
    }
}

function updateUser($username, $newUsername, $newPassword) {
    try {
        global $pdo;

        // Проверяем, существует ли пользователь с новым именем пользователя
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :newUsername");
        $stmt->execute(['newUsername' => $newUsername]);

        if ($stmt->fetch() && $newUsername !== $username) {
            return "Ошибка: пользователь с таким именем уже существует!";
        }

        // Хешируем новый пароль
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Обновляем данные пользователя
        $stmt = $pdo->prepare("UPDATE users SET username = :newUsername, password = :newPassword WHERE username = :username");
        $stmt->execute(['newUsername' => $newUsername, 'newPassword' => $hashedPassword, 'username' => $username]);

        return "Информация обновлена успешно!";
    } catch (PDOException $e) {
        return "Ошибка: " . $e->getMessage();
    }
}

// Получение заказов пользователя
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE username = :username ORDER BY order_date DESC");
$stmt->execute(['username' => $username]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение информации о товарах из базы данных
$stmt = $pdo->query("SELECT * FROM products");
$productsInfo = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $productsInfo[$row['id']] = $row;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head> 
<body>
    <div class="navbar">
        <a href="main.html">Главная</a>
        <a href="about.html">О нас</a>
        <a href="sale.html">Акции </a>
        <a href="reviews.php">Отзывы</a>
        <a href="cart.php">Корзина🛒</a>
        <a href="logout.php" class="transparent-button">Выйти</a>
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

    <div class="content">
        <h2>Профиль</h2>
        <p>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        
        <form action="profile.php" method="post">
            <p id="heading">Обновление профиля</p>
            <div class="field">
                <input name="username" autocomplete="off" placeholder="Новое имя пользователя" class="input-field" type="text" required value="<?php echo htmlspecialchars($_SESSION['username']); ?>">
            </div>
            <div class="field">
                <input name="password" placeholder="Новый пароль" class="input-field" type="password" required>
            </div>
            <div class="btn">
                <button type="submit" class="button1">Обновить данные</button>
            </div>
            <?php if (!empty($message)) { echo '<p>' . $message . '</p>'; } ?>
        </form>

        <h2>Ваши заказы</h2>
        <?php if (empty($orders)): ?>
            <p>У вас нет заказов.</p>
        <?php else: ?>
            <ul class="orders">
                <?php foreach ($orders as $order): ?>
                    <li class="order">
                        <p><strong>Дата заказа:</strong> <?php echo $order['order_date']; ?></p>
                        <p><strong>Имя:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
                        <p><strong>Адрес:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                        <p><strong>Телефон:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        <p><strong>Товары:</strong>
                            <ul>
                                <?php
                                $products = json_decode($order['products'], true);
                                foreach ($products as $productId => $quantity) {
                                    if (isset($productsInfo[$productId])) {
                                        $product = $productsInfo[$productId];
                                        echo "<li>{$product['name']} x {$quantity} шт. ({$product['price']} ₽)</li>";
                                    } else {
                                        echo "<li>Товар с ID {$productId} больше не доступен</li>";
                                    }
                                }
                                ?>
                            </ul>
                        </p>
                        <p><strong>Итоговая цена:</strong> <?php echo $order['total_price']; ?> ₽</p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>