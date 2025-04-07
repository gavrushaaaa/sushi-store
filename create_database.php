<?php
try {
    // Подключение к базе данных SQLite
    $pdo = new PDO("sqlite:database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблицы пользователей
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY,
        username TEXT UNIQUE,
        password TEXT,
        is_admin INTEGER DEFAULT 0
    )");

    // Создание таблицы заказов
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY,
        username TEXT,
        name TEXT,
        address TEXT,
        phone TEXT,
        products TEXT,
        total_price INTEGER,
        order_date TEXT
    )");

    // Создание таблицы отзывов
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY,
        username TEXT,
        product_id INTEGER,
        rating INTEGER,
        comment TEXT,
        review_date TEXT
    )");

    // Создание таблицы товаров
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY,
        name TEXT,
        price INTEGER,
        description TEXT,
        image TEXT,
        weight TEXT,
        quantity INTEGER,
        proteins REAL,
        fats REAL,
        carbohydrates REAL,
        calories INTEGER
    )");

    // Добавление учетной записи администратора
    $adminUsername = 'admin';
    $adminPassword = password_hash('adminpassword', PASSWORD_DEFAULT);
    $pdo->exec("INSERT OR IGNORE INTO users (username, password, is_admin) VALUES ('$adminUsername', '$adminPassword', 1)");

    echo "База данных и таблицы успешно созданы.";
} catch (PDOException $e) {
    echo "Ошибка при создании базы данных: " . $e->getMessage();
    exit();
}
?>

<?php
try {
    // Подключение к базе данных SQLite
    $pdo = new PDO("sqlite:database.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Добавление недостающих столбцов в таблицу products
    $pdo->exec("ALTER TABLE products ADD COLUMN weight TEXT");
    $pdo->exec("ALTER TABLE products ADD COLUMN quantity INTEGER");
    $pdo->exec("ALTER TABLE products ADD COLUMN proteins REAL");
    $pdo->exec("ALTER TABLE products ADD COLUMN fats REAL");
    $pdo->exec("ALTER TABLE products ADD COLUMN carbohydrates REAL");
    $pdo->exec("ALTER TABLE products ADD COLUMN calories INTEGER");

    echo "Таблица products успешно обновлена.";
} catch (PDOException $e) {
    echo "Ошибка при обновлении таблицы products: " . $e->getMessage();
    exit();
}
?>