<?php
$dsn = 'mysql:dbname=shop;host=127.0.0.1;charset=utf8mb4';
$user = 'user';
$password = 'password';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // В реальном проекте не показывай детали ошибки пользователю!
    die("Connection failed: " . $e->getMessage());
}
?>

    