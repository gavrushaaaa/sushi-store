<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
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

// Обработка формы добавления товара
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $weight = $_POST['weight'];
    $quantity = $_POST['quantity'];
    $proteins = $_POST['proteins'];
    $fats = $_POST['fats'];
    $carbohydrates = $_POST['carbohydrates'];
    $calories = $_POST['calories'];

    // Обработка загрузки изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Папка для загрузки изображений
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Создаем папку, если она не существует
        }

        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . uniqid() . '_' . $imageName; // Уникальное имя файла

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            // Сохраняем товар в базе данных
            $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, weight, quantity, proteins, fats, carbohydrates, calories) VALUES (:name, :price, :description, :image, :weight, :quantity, :proteins, :fats, :carbohydrates, :calories)");
            $stmt->execute([
                'name' => $name,
                'price' => $price,
                'description' => $description,
                'image' => $imagePath, // Сохраняем путь к изображению
                'weight' => $weight,
                'quantity' => $quantity,
                'proteins' => $proteins,
                'fats' => $fats,
                'carbohydrates' => $carbohydrates,
                'calories' => $calories
            ]);
            $message = "Товар успешно добавлен!";
        } else {
            $message = "Ошибка загрузки изображения.";
        }
    } else {
        $message = "Ошибка: изображение не загружено.";
    }
}

// Обработка удаления товара
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'];

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $productId]);
    $message = "Товар успешно удален!";
}

// Обработка удаления отзыва
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_review'])) {
    $reviewId = $_POST['review_id'];

    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = :id");
    $stmt->execute(['id' => $reviewId]);
    $message = "Отзыв успешно удален!";
}

// Получение всех товаров
$stmt = $pdo->prepare("SELECT * FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение всех отзывов с информацией о товарах
$stmt = $pdo->prepare("
    SELECT reviews.*, products.name AS product_name 
    FROM reviews 
    LEFT JOIN products ON reviews.product_id = products.id 
    ORDER BY reviews.review_date DESC
");
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Подсчет количества товаров
$productCount = count($products);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="navbar">
        <a href="main.html">Главная</a>
        <a href="about.html">О нас</a>
        <a href="sale.html">Акции </a>
        <a href="profile.php" class="transparent-button">Личный кабинет</a>
        <a href="cart.php">Корзина🛒</a>
        <a href="admin_logout.php">Выйти</a>
    </div>

    <div class="content">
        <h2>Панель администратора</h2>
        <?php if (!empty($message)) { echo '<p>' . $message . '</p>'; } ?>

        <div class="admin-dashboard">
            <div class="admin-section">
                <i class="fas fa-boxes"></i>
                <h3>Товары</h3>
                <p>Всего товаров: <?php echo $productCount; ?></p>
                <button onclick="showSection('products')">Просмотр товаров</button>
            </div>
            <div class="admin-section">
                <i class="fas fa-comments"></i>
                <h3>Отзывы</h3>
                <button onclick="showSection('reviews')">Просмотр отзывов</button>
            </div>
        </div>

        <div id="products" class="admin-content">
            <h3>Добавить товар</h3>
            <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="add_product" value="1">
                <div class="field">
                    <label for="name">Название:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="field">
                    <label for="price">Цена:</label>
                    <input type="number" id="price" name="price" required>
                </div>
                <div class="field">
                    <label for="description">Описание:</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="field">
                    <label for="image">Изображение:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>
                <div class="field">
                    <label for="weight">Вес:</label>
                    <input type="text" id="weight" name="weight" required>
                </div>
                <div class="field">
                    <label for="quantity">Количество:</label>
                    <input type="number" id="quantity" name="quantity" required>
                </div>
                <div class="field">
                    <label for="proteins">Белки (г):</label>
                    <input type="number" step="0.1" id="proteins" name="proteins" required>
                </div>
                <div class="field">
                    <label for="fats">Жиры (г):</label>
                    <input type="number" step="0.1" id="fats" name="fats" required>
                </div>
                <div class="field">
                    <label for="carbohydrates">Углеводы (г):</label>
                    <input type="number" step="0.1" id="carbohydrates" name="carbohydrates" required>
                </div>
                <div class="field">
                    <label for="calories">Калорийность (ккал):</label>
                    <input type="number" id="calories" name="calories" required>
                </div>
                <button type="submit" class="button1">Добавить товар</button>
            </form>

            <h3>Все товары</h3>
            <ul class="products">
                <?php foreach ($products as $product): ?>
                    <li class="product">
                        <p><strong>Название:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
                        <p><strong>Цена:</strong> <?php echo $product['price']; ?> ₽</p>
                        <p><strong>Описание:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
                        <p><strong>Изображение:</strong> <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"></p>
                        <p><strong>Вес:</strong> <?php echo htmlspecialchars($product['weight']); ?></p>
                        <p><strong>Количество:</strong> <?php echo htmlspecialchars($product['quantity']); ?> шт.</p>
                        <p><strong>Белки:</strong> <?php echo htmlspecialchars($product['proteins']); ?> г</p>
                        <p><strong>Жиры:</strong> <?php echo htmlspecialchars($product['fats']); ?> г</p>
                        <p><strong>Углеводы:</strong> <?php echo htmlspecialchars($product['carbohydrates']); ?> г</p>
                        <p><strong>Калорийность:</strong> <?php echo htmlspecialchars($product['calories']); ?> ккал</p>
                        <form action="admin_dashboard.php" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="delete_product" class="button2">Удалить товар</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div id="reviews" class="admin-content" style="display: none;">
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
                        <form action="admin_dashboard.php" method="post">
                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                            <button type="submit" name="delete_review" class="button2">Удалить отзыв</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            document.getElementById('products').style.display = 'none';
            document.getElementById('reviews').style.display = 'none';
            document.getElementById(sectionId).style.display = 'block';
        }
    </script>
</body>
</html>