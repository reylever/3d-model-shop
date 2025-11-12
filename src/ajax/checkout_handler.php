<?php
session_start();
require_once '../includes/connect.php';

header('Content-Type: application/json');

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходимо войти в систему']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные формы
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$payment = $_POST['payment'] ?? 'card';
$comment = trim($_POST['comment'] ?? '');

// Валидация
if (empty($name) || empty($email) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Заполните все обязательные поля']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email']);
    exit;
}

try {
    // Начинаем транзакцию
    $pdo->beginTransaction();
    
    // Получаем товары из корзины
    $stmt = $pdo->prepare("
        SELECT c.product_id, c.quantity, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cart_items)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Корзина пуста']);
        exit;
    }
    
    // Рассчитываем общую сумму
    $total_price = 0;
    foreach ($cart_items as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }
    
    // Создаем заказ
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, phone, comment, payment_method, total_price, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$user_id, $phone, $comment, $payment, $total_price]);
    $order_id = $pdo->lastInsertId();
    
    // Добавляем товары в заказ
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($cart_items as $item) {
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);
    }
    
    // Очищаем корзину
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Фиксируем транзакцию
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Заказ успешно оформлен',
        'order_id' => $order_id
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Ошибка при оформлении заказа']);
}