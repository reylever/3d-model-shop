<?php
session_start();
require_once '../includes/connect.php';

header('Content-Type: application/json');

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
    exit;
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Неверный метод запроса']);
    exit;
}

// Получаем ID товара
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID товара']);
    exit;
}

try {
    // Получаем информацию о товаре
    $stmt = $pdo->prepare("SELECT preview_image, model_file FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Товар не найден']);
        exit;
    }

    // Получаем все изображения товара из галереи
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Проверяем, есть ли товар в заказах
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$product_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Товар нельзя удалить - он есть в заказах']);
        exit;
    }

    // Начинаем транзакцию
    $pdo->beginTransaction();

    // Удаляем товар из корзин пользователей
    $stmt = $pdo->prepare("DELETE FROM cart WHERE product_id = ?");
    $stmt->execute([$product_id]);

    // Удаляем записи из product_images
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);

    // Удаляем товар из базы данных
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    // Фиксируем транзакцию
    $pdo->commit();

    // Удаляем файлы изображений и папку товара
    if (!empty($product_images)) {
        foreach ($product_images as $image_path) {
            $full_path = '../' . $image_path;
            if (file_exists($full_path)) {
                @unlink($full_path);
            }
        }

        // Пытаемся удалить папку товара (если она пустая)
        if (!empty($product_images[0])) {
            $dir_path = dirname('../' . $product_images[0]);
            if (is_dir($dir_path) && count(scandir($dir_path)) == 2) { // . и ..
                @rmdir($dir_path);
            }
        }
    }

    // Удаляем 3D модель (если существует)
    if ($product['model_file'] && file_exists('../' . $product['model_file'])) {
        @unlink('../' . $product['model_file']);
    }

    echo json_encode(['success' => true, 'message' => 'Товар успешно удален']);

} catch (PDOException $e) {
    // Откатываем транзакцию при ошибке
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
