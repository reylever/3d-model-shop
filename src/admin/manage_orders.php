<?php
session_start();
require_once '../includes/connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php?error=Доступ запрещен");
    exit;
}

// Обработка изменения статуса заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $success_message = "Статус заказа обновлен";
    }
}

// Получаем все заказы
$stmt = $pdo->query("
    SELECT o.*, u.username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами - Админ-панель</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php' ?>

    <main>
        <section class="admin-panel">
            <div class="container">
                <div class="admin-header">
                    <h1>Управление заказами</h1>
                    <p>Всего заказов: <?= count($orders) ?></p>
                </div>

                <!-- Навигация админки -->
                <nav class="admin-nav">
                    <a href="index.php" class="admin-nav__link">📊 Дашборд</a>
                    <a href="add_products.php" class="admin-nav__link">➕ Добавить товар</a>
                    <a href="manage_products.php" class="admin-nav__link">📦 Управление товарами</a>
                    <a href="manage_orders.php" class="admin-nav__link active">🛒 Заказы</a>
                    <a href="../catalog.php" class="admin-nav__link">🏠 На сайт</a>
                </nav>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <!-- Таблица заказов -->
                <div class="admin-section">
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>№ заказа</th>
                                    <th>Клиент</th>
                                    <th>Email</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 40px;">
                                            Заказов пока нет
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong>#<?= $order['id'] ?></strong></td>
                                            <td><?= htmlspecialchars($order['username']) ?></td>
                                            <td><?= htmlspecialchars($order['email']) ?></td>
                                            <td><strong><?= number_format($order['total_price'], 0, ',', ' ') ?> ₽</strong></td>
                                            <td>
                                                <span class="status-badge status-<?= $order['status'] ?>">
                                                    <?php
                                                    $statuses = [
                                                        'pending' => 'Ожидает',
                                                        'processing' => 'В обработке',
                                                        'completed' => 'Выполнен',
                                                        'cancelled' => 'Отменен'
                                                    ];
                                                    echo $statuses[$order['status']] ?? $order['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn-small btn-view" 
                                                            onclick="viewOrder(<?= $order['id'] ?>)">
                                                        👁️ Детали
                                                    </button>
                                                    <button class="btn-small btn-status" 
                                                            onclick="changeStatus(<?= $order['id'] ?>, '<?= $order['status'] ?>')">
                                                        🔄 Статус
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Модальное окно просмотра заказа -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div id="orderDetails"></div>
        </div>
    </div>

    <!-- Модальное окно изменения статуса -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeStatusModal()">&times;</span>
            <h2>Изменить статус заказа</h2>
            <form method="POST" id="statusForm">
                <input type="hidden" name="order_id" id="statusOrderId">
                <div class="form-group">
                    <label>Новый статус:</label>
                    <select name="status" id="statusSelect" class="form-control">
                        <option value="pending">Ожидает</option>
                        <option value="processing">В обработке</option>
                        <option value="completed">Выполнен</option>
                        <option value="cancelled">Отменен</option>
                    </select>
                </div>
                <button type="submit" class="btn btn--primary">Сохранить</button>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php' ?>

    <script src="../assets/js/admin.js"></script>
</body>
</html>