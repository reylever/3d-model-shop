<?php
session_start();
require_once '../includes/connect.php';

// Получаем данные из формы
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Валидация
if (empty($email) || empty($password)) {
    header("Location: ../login.php?error=Заполните все поля");
    exit;
}

// Ищем пользователя по email
$stmt = $pdo->prepare("SELECT id, username, email, password, is_admin FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверяем существование пользователя и пароль
if (!$user || !password_verify($password, $user['password'])) {
    header("Location: ../login.php?error=Неверный email или пароль");
    exit;
}

// Записываем данные пользователя в сессию
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['is_admin'] = $user['is_admin'];

// Перенаправляем на главную
header("Location: ../index.php");
exit;
?>