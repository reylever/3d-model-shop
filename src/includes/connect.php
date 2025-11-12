<?php
$dsn = 'mysql:dbname=shop;host=db;charset=utf8mb4';
$user = 'user';
$password = 'password';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}