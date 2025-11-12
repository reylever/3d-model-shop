<?php
session_start();
require_once '../includes/connect.php';

header('Content-Type: application/json');

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходимо войти в систему']);
    exit;
}

// Получаем данные
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$change = isset($_POST['change']) ? (int)$_POST['change'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : 'add';

if ($product_id <= 0 || $change <= 0) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Получаем текущее количество
    $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Товар не найден в корзине']);
        exit;
    }
    
    // Рассчитываем новое количество
    if ($action === 'add') {
        $new_quantity = $cart_item['quantity'] + $change;
    } else {
        $new_quantity = $cart_item['quantity'] - $change;
    }
    
    // Проверяем границы
    if ($new_quantity < 1) {
        $new_quantity = 1;
    }
    if ($new_quantity > 99) {
        $new_quantity = 99;
    }
    
    // Обновляем количество
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$new_quantity, $user_id, $product_id]);
    
    echo json_encode([
        'success' => true,
        'new_quantity' => $new_quantity
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
}