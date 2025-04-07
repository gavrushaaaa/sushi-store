<?php
session_start();
session_unset(); // Удалить все переменные сессии
session_destroy(); // Уничтожить сессию
header("Location: main.html"); // Перенаправить на главную страницу
exit();
?>
