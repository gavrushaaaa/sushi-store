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

// Получение заказанных товаров пользователя
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT products FROM orders WHERE username = :username");
$stmt->execute(['username' => $username]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$orderedProducts = [];
foreach ($orders as $order) {
    $products = json_decode($order['products'], true);
    foreach ($products as $productId => $quantity) {
        if (!isset($orderedProducts[$productId])) {
            $orderedProducts[$productId] = $quantity;
        } else {
            $orderedProducts[$productId] += $quantity;
        }
    }
}

// Получение информации о продуктах из базы данных
$stmt = $pdo->query("SELECT * FROM products");
$productsInfo = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $productsInfo[$row['id']] = $row;
}

// Обработка формы добавления отзыва
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];
    $productIds = $_POST['product_ids'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    if (empty($rating) || empty($comment)) {
        $message = "Ошибка: все поля должны быть заполнены!";
    } else {
        $reviewDate = date('Y-m-d H:i:s');
        foreach ($productIds as $productId) {
            $stmt = $pdo->prepare("INSERT INTO reviews (username, product_id, rating, comment, review_date) VALUES (:username, :product_id, :rating, :comment, :review_date)");
            $stmt->execute([
                'username' => $username,
                'product_id' => $productId,
                'rating' => $rating,
                'comment' => $comment,
                'review_date' => $reviewDate
            ]);
        }
        $message = "Отзывы успешно добавлены!";
    }
}

// Получение всех отзывов с информацией о товарах
$stmt = $pdo->prepare("
    SELECT reviews.*, products.name AS product_name 
    FROM reviews 
    LEFT JOIN products ON reviews.product_id = products.id 
    ORDER BY reviews.review_date DESC
");
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы</title>
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

    <div class="content">
        <h2>Отзывы о товаре</h2>
        <?php if (!empty($message)) { echo '<p>' . $message . '</p>'; } ?>

        <form action="reviews.php" method="post">
            <div class="field">
                <label for="product_ids">Выберите товары:</label>
                <select id="product_ids" name="product_ids[]" multiple required>
                    <?php foreach ($orderedProducts as $productId => $quantity): ?>
                        <?php if (isset($productsInfo[$productId])): ?>
                            <option value="<?php echo $productId; ?>"><?php echo $productsInfo[$productId]['name']; ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="rating">Рейтинг:</label>
                <select id="rating" name="rating" required>
                    <option value="5">5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                </select>
            </div>
            <div class="field">
                <label for="comment">Комментарий:</label>
                <textarea id="comment" name="comment" rows="4" required></textarea>
            </div>
            <button type="submit" class="button1">Добавить отзывы</button>
        </form>

        <h3>Все отзывы</h3>
        <ul class="reviews">
            <?php foreach ($reviews as $review): ?>
                <li class="review">
                    <p><strong>Пользователь:</strong> <?php echo htmlspecialchars($review['username']); ?></p>
                    <p><strong>Рейтинг:</strong> <?php echo $review['rating']; ?></p>
                    <p><strong>Комментарий:</strong> <?php echo htmlspecialchars($review['comment']); ?></p>
                    <p><strong>Дата:</strong> <?php echo $review['review_date']; ?></p>
                    <p><strong>Товар:</strong> 
                        <?php if (!empty($review['product_name'])): ?>
                            <?php echo htmlspecialchars($review['product_name']); ?>
                        <?php else: ?>
                            <em>Товар удален</em>
                        <?php endif; ?>
                    </p>
                    <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                        <form action="admin_dashboard.php" method="post">
                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                            <button type="submit" name="delete_review" class="button2">Удалить отзыв</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>