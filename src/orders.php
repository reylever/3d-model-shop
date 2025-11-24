<?php
session_start();
require_once 'includes/connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Необходимо войти в систему");
    exit;
}

// Получаем заказы пользователя
$stmt = $pdo->prepare("
    SELECT o.*
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем детали заказа при клике
$order_details = null;
if (isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];

    // Проверяем, принадлежит ли заказ пользователю
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $selected_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selected_order) {
        // Получаем товары в заказе
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name, p.preview_image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_details = [
            'order' => $selected_order,
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
}

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
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заказы - Яшин стаффчик</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .orders-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .orders-header h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }

        .orders-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .order-card {
            padding: 25px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
            cursor: pointer;
        }

        .order-card:last-child {
            border-bottom: none;
        }

        .order-card:hover {
            background-color: #f8f9fa;
        }

        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }

        .order-status {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            color: #6c757d;
        }

        .order-info-item {
            display: flex;
            flex-direction: column;
        }

        .order-info-label {
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .order-info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-orders h2 {
            margin-bottom: 20px;
            color: #333;
        }

        /* Модальное окно деталей заказа */
        .order-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .order-modal.active {
            display: flex;
        }

        .modal-content-order {
            background: white;
            border-radius: 12px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            padding: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-body {
            padding: 30px;
        }

        .order-items {
            margin-top: 20px;
        }

        .order-item {
            display: flex;
            gap: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .order-item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .order-item-info {
            flex: 1;
        }

        .order-item-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .order-item-details {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .order-item-price {
            text-align: right;
            font-weight: bold;
            color: #667eea;
        }

        .modal-close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            cursor: pointer;
            color: #6c757d;
            background: none;
            border: none;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close-btn:hover {
            color: #333;
        }

        .order-summary {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            text-align: right;
        }

        .order-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        @media (max-width: 768px) {
            .order-info {
                grid-template-columns: 1fr;
            }

            .order-item {
                flex-direction: column;
            }

            .order-item-price {
                text-align: left;
            }
        }

        /* Темная тема */
        [data-theme="dark"] .orders-container {
            color: var(--text-primary);
        }

        [data-theme="dark"] .orders-list {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        [data-theme="dark"] .order-card {
            border-color: var(--border-color);
        }

        [data-theme="dark"] .order-card:hover {
            background-color: var(--bg-secondary);
        }

        [data-theme="dark"] .order-number {
            color: var(--text-primary);
        }

        [data-theme="dark"] .order-info {
            color: var(--text-secondary);
        }

        [data-theme="dark"] .order-info-value {
            color: var(--text-primary);
        }

        [data-theme="dark"] .no-orders {
            color: var(--text-secondary);
        }

        [data-theme="dark"] .no-orders h2 {
            color: var(--text-primary);
        }

        [data-theme="dark"] .modal-content-order {
            background: var(--card-bg);
        }

        [data-theme="dark"] .modal-header {
            border-color: var(--border-color);
        }

        [data-theme="dark"] .order-item {
            border-color: var(--border-color);
            background: var(--bg-secondary);
        }

        [data-theme="dark"] .order-item-name {
            color: var(--text-primary);
        }

        [data-theme="dark"] .order-item-details {
            color: var(--text-secondary);
        }

        [data-theme="dark"] .order-summary {
            border-color: var(--border-color);
        }

        [data-theme="dark"] .order-total {
            color: var(--text-primary);
        }

        [data-theme="dark"] .modal-close-btn {
            color: var(--text-secondary);
        }

        [data-theme="dark"] .modal-close-btn:hover {
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main>
        <div class="orders-container">
            <div class="orders-header">
                <h1>Мои заказы</h1>
                <p>Всего заказов: <?= count($orders) ?></p>
            </div>

            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <h2>У вас пока нет заказов</h2>
                    <p>Начните с просмотра нашего каталога 3D моделей!</p>
                    <a href="catalog.php" class="btn btn--primary" style="margin-top: 20px; display: inline-block; padding: 12px 30px; text-decoration: none;">
                        Перейти в каталог
                    </a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                            <div class="order-card-header">
                                <div class="order-number">Заказ #<?= $order['id'] ?></div>
                                <div class="order-status" style="background-color: <?= $status_colors[$order['status']] ?>">
                                    <?= $status_labels[$order['status']] ?>
                                </div>
                            </div>
                            <div class="order-info">
                                <div class="order-info-item">
                                    <div class="order-info-label">Дата заказа</div>
                                    <div class="order-info-value">
                                        <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="order-info-item">
                                    <div class="order-info-label">Сумма</div>
                                    <div class="order-info-value">
                                        <?= number_format($order['total_price'], 0, ',', ' ') ?> ₽
                                    </div>
                                </div>
                                <div class="order-info-item">
                                    <div class="order-info-label">Способ оплаты</div>
                                    <div class="order-info-value" style="font-size: 0.9rem;">
                                        <?php
                                        $payment_methods = [
                                            'card' => 'Карта',
                                            'crypto' => 'Крипто',
                                            'sbp' => 'СБП'
                                        ];
                                        echo $payment_methods[$order['payment_method']] ?? 'Карта';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 30px; text-align: center;">
                <a href="profile.php" class="btn btn--secondary" style="display: inline-block; padding: 12px 30px; text-decoration: none;">
                    Вернуться в профиль
                </a>
            </div>
        </div>
    </main>

    <!-- Модальное окно деталей заказа -->
    <div id="orderModal" class="order-modal">
        <div class="modal-content-order">
            <button class="modal-close-btn" onclick="closeOrderModal()">&times;</button>
            <div id="orderDetailsContent">
                <!-- Контент загружается через AJAX -->
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php' ?>

    <script>
        function viewOrderDetails(orderId) {
            // Показываем модальное окно
            const modal = document.getElementById('orderModal');
            modal.classList.add('active');

            // Загружаем детали заказа
            fetch('ajax/get_order_details.php?order_id=' + orderId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('orderDetailsContent').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('orderDetailsContent').innerHTML =
                        '<div style="padding: 40px; text-align: center; color: #dc3545;">Ошибка загрузки деталей заказа</div>';
                });
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        // Закрытие модального окна при клике вне его
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                closeOrderModal();
            }
        }

        // Закрытие модального окна при нажатии Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeOrderModal();
            }
        });
    </script>
</body>
</html>
