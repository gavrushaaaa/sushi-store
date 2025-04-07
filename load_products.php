<?php
// filepath: /c:/xampp/htdocs/проектик/load_products.php
try {
    $pdo = new PDO("sqlite:database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($products);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>