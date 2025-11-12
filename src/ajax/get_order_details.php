<?php
session_start();
require_once '../includes/connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['html'])) {
        echo '<div style="padding: 40px; text-align: center; color: #dc3545;">Доступ запрещен</div>';
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    }
    exit;
}

// Определяем формат ответа
$html_format = isset($_GET['html']) || isset($_GET['order_id']);
if (!$html_format) {
    header('Content-Type: application/json');
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0);

if ($order_id <= 0) {
    if ($html_format) {
        echo '<div style="padding: 40px; text-align: center; color: #dc3545;">Некорректный ID заказа</div>';
    } else {
        echo json_encode(['success' => false, 'message' => 'Некорректный ID заказа']);
    }
    exit;
}

try {
    // Получаем информацию о заказе
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        if ($html_format) {
            echo '<div style="padding: 40px; text-align: center; color: #dc3545;">Заказ не найден</div>';
        } else {
            echo json_encode(['success' => false, 'message' => 'Заказ не найден']);
        }
        exit;
    }

    // Проверка прав доступа: админ может видеть все заказы, пользователь - только свои
    if (!$_SESSION['is_admin'] && $order['user_id'] != $_SESSION['user_id']) {
        if ($html_format) {
            echo '<div style="padding: 40px; text-align: center; color: #dc3545;">Доступ запрещен</div>';
        } else {
            echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
        }
        exit;
    }

    // Получаем товары заказа
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.preview_image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Возвращаем HTML для пользователей
    if ($html_format) {
        $status_labels = [
            'pending' => 'Ожидает обработки',
            'processing' => 'В обработке',
            'completed' => 'Выполнен',
            'cancelled' => 'Отменен'
        ];

        $status_colors = [
            'pending' => '#ffc107',
            'processing' => '#17a2b8',
            'completed' => '#28a745',
            'cancelled' => '#dc3545'
        ];
        ?>
        <div class="modal-header">
            <h2>Заказ #<?= $order['id'] ?></h2>
            <div style="margin-top: 15px;">
                <span style="display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; color: white; background-color: <?= $status_colors[$order['status']] ?>">
                    <?= $status_labels[$order['status']] ?>
                </span>
            </div>
            <div style="margin-top: 15px; color: #6c757d; font-size: 0.9rem;">
                Дата заказа: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
            </div>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 20px;">
                <h3 style="margin-bottom: 10px;">Информация о покупателе</h3>
                <p style="color: #6c757d; margin: 5px 0;"><strong>Имя:</strong> <?= htmlspecialchars($order['username']) ?></p>
                <p style="color: #6c757d; margin: 5px 0;"><strong>Телефон:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                <p style="color: #6c757d; margin: 5px 0;"><strong>Email для отправки:</strong> <?= htmlspecialchars($order['email']) ?></p>
                <?php if (!empty($order['payment_method'])): ?>
                    <p style="color: #6c757d; margin: 5px 0;"><strong>Способ оплаты:</strong>
                        <?php
                        $payment_methods = [
                            'card' => 'Банковская карта',
                            'crypto' => 'Криптовалюта',
                            'sbp' => 'СБП'
                        ];
                        echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                        ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($order['comment'])): ?>
                    <p style="color: #6c757d; margin: 5px 0;"><strong>Комментарий:</strong> <?= htmlspecialchars($order['comment']) ?></p>
                <?php endif; ?>
            </div>

            <h3 style="margin-bottom: 15px;">Товары в заказе</h3>
            <div class="order-items">
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <div class="order-item-image">
                            <img src="<?= htmlspecialchars($item['preview_image']) ?>"
                                 alt="<?= htmlspecialchars($item['product_name']) ?>"
                                 onerror="this.src='assets/img/placeholder.jpg'">
                        </div>
                        <div class="order-item-info">
                            <div class="order-item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                            <div class="order-item-details">
                                Количество: <?= $item['quantity'] ?> шт. × <?= number_format($item['price'], 0, ',', ' ') ?> ₽
                            </div>
                        </div>
                        <div class="order-item-price">
                            <?= number_format($item['quantity'] * $item['price'], 0, ',', ' ') ?> ₽
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-summary">
                <div style="font-size: 1.1rem; margin-bottom: 10px; color: #6c757d;">
                    Итого к оплате:
                </div>
                <div class="order-total">
                    <?= number_format($order['total_price'], 0, ',', ' ') ?> ₽
                </div>
            </div>
        </div>
        <?php
    } else {
        // Возвращаем JSON для админов
        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $items
        ]);
    }

} catch (PDOException $e) {
    if ($html_format) {
        echo '<div style="padding: 40px; text-align: center; color: #dc3545;">Ошибка базы данных</div>';
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
    }
}