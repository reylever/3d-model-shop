<?php
require_once '../includes/connect.php';

// Получаем данные из формы
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Валидация
if (empty($username) || empty($email) || empty($password)) {
    header("Location: ../register.php?error=Заполните все поля");
    exit;
}

if ($password !== $password_confirm) {
    header("Location: ../register.php?error=Пароли не совпадают");
    exit;
}

if (strlen($password) < 8) {
    header("Location: ../register.php?error=Пароль должен быть минимум 8 символов");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../register.php?error=Некорректный email");
    exit;
}

// Проверяем, не занят ли email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    header("Location: ../register.php?error=Email уже зарегистрирован");
    exit;
}

// Хешируем пароль
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Записываем пользователя в БД
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 0)");
    $stmt->execute([$username, $email, $password_hash]);
    
    header("Location: ../login.php?success=Регистрация успешна! Войдите в аккаунт");
    exit;
} catch (PDOException $e) {
    header("Location: ../register.php?error=Ошибка регистрации");
    exit;
}
?>