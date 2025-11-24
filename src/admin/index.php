<?php
session_start();
require_once '../includes/connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php?error=Доступ запрещен");
    exit;
}

// Получаем статистику
$stats = [];

// Общее количество товаров
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
$stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Общее количество заказов
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Общее количество пользователей
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Общая сумма заказов
$stmt = $pdo->query("SELECT SUM(total_price) as total FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Последние заказы
$stmt = $pdo->query("
    SELECT o.*, u.username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Популярные товары
$stmt = $pdo->query("
    SELECT p.id, p.name, p.price, p.preview_image, SUM(oi.quantity) as total_sold
    FROM products p
    JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
$popular_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Яшин стаффчик</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php include '../includes/header.php' ?>

    <main>
        <section class="admin-panel">
            <div class="container">
                <div class="admin-header">
                    <h1>Панель администратора</h1>
                    <p>Добро пожаловать, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
                </div>

                <!-- Навигация админки -->
                <nav class="admin-nav">
                    <a href="index.php" class="admin-nav__link active">📊 Дашборд</a>
                    <a href="add_products.php" class="admin-nav__link">➕ Добавить товар</a>
                    <a href="manage_products.php" class="admin-nav__link">📦 Управление товарами</a>
                    <a href="manage_orders.php" class="admin-nav__link">🛒 Заказы</a>
                    <a href="../catalog.php" class="admin-nav__link">🏠 На сайт</a>
                </nav>

                <!-- Статистика -->
                <div class="stats-grid">
                    <div class="stat-card stat-card--blue">
                        <div class="stat-card__icon">📦</div>
                        <div class="stat-card__info">
                            <h3>Товаров</h3>
                            <p class="stat-card__value"><?= $stats['products'] ?></p>
                        </div>
                    </div>

                    <div class="stat-card stat-card--green">
                        <div class="stat-card__icon">🛒</div>
                        <div class="stat-card__info">
                            <h3>Заказов</h3>
                            <p class="stat-card__value"><?= $stats['orders'] ?></p>
                        </div>
                    </div>

                    <div class="stat-card stat-card--purple">
                        <div class="stat-card__icon">👥</div>
                        <div class="stat-card__info">
                            <h3>Пользователей</h3>
                            <p class="stat-card__value"><?= $stats['users'] ?></p>
                        </div>
                    </div>

                    <div class="stat-card stat-card--orange">
                        <div class="stat-card__icon">💰</div>
                        <div class="stat-card__info">
                            <h3>Выручка</h3>
                            <p class="stat-card__value"><?= number_format($stats['revenue'], 0, ',', ' ') ?> ₽</p>
                        </div>
                    </div>
                </div>

                <!-- Две колонки -->
                <div class="admin-content">
                    <!-- Последние заказы -->
                    <div class="admin-section">
                        <h2>Последние заказы</h2>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Клиент</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_orders)): ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 40px;">
                                                Заказов пока нет
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?= $order['id'] ?></td>
                                                <td><?= htmlspecialchars($order['username']) ?></td>
                                                <td><?= number_format($order['total_price'], 0, ',', ' ') ?> ₽</td>
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
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Популярные товары -->
                    <div class="admin-section">
                        <h2>Популярные товары</h2>
                        <div class="popular-products">
                            <?php if (empty($popular_products)): ?>
                                <p style="text-align: center; padding: 40px; color: #6c757d;">
                                    Статистика появится после первых заказов
                                </p>
                            <?php else: ?>
                                <?php foreach ($popular_products as $product): ?>
                                    <div class="popular-product">
                                        <div class="popular-product__image">
                                            <img src="../<?= htmlspecialchars($product['preview_image']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                                 onerror="this.src='../assets/img/placeholder.jpg'">
                                        </div>
                                        <div class="popular-product__info">
                                            <h4><?= htmlspecialchars($product['name']) ?></h4>
                                            <p>Продано: <?= $product['total_sold'] ?> шт.</p>
                                        </div>
                                        <div class="popular-product__price">
                                            <?= number_format($product['price'], 0, ',', ' ') ?> ₽
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php' ?>
</body>
</html>