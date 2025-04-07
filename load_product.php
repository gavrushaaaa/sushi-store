<?php
// filepath: /c:/xampp/htdocs/проектик/load_product.php
try {
    $pdo = new PDO("sqlite:database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $productId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($product);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>