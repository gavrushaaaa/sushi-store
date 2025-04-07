<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($address) || empty($phone)) {
        header("Location: checkout.php?error=empty_fields");
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

        // Сохранение заказа в базу данных
        $username = $_SESSION['username'];
        $cart = $_SESSION['cart'];
        $totalPrice = 0;
        foreach ($cart as $productId => $quantity) {
            if (isset($products[$productId])) {
                $totalPrice += $products[$productId]['price'] * $quantity;
            }
        }
        $orderDate = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO orders (username, name, address, phone, products, total_price, order_date) VALUES (:username, :name, :address, :phone, :products, :total_price, :order_date)");
        $stmt->execute([
            'username' => $username,
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'products' => json_encode($cart), // Сохраняем только ID товаров и их количество
            'total_price' => $totalPrice,
            'order_date' => $orderDate
        ]);

        // Очистка корзины после оформления заказа
        unset($_SESSION['cart']);

        header("Location: main.html?success=order_placed");
        exit();
    } catch (PDOException $e) {
        echo "Ошибка при сохранении заказа: " . $e->getMessage();
        exit();
    }
}
?>