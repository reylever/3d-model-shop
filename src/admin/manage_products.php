<?php
session_start();
require_once '../includes/connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php?error=Доступ запрещен");
    exit;
}

// Получаем все товары с информацией о категориях
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление товарами - Админ-панель</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include '../includes/header.php' ?>

    <main>
        <section class="admin-panel">
            <div class="container">
                <div class="admin-header">
                    <h1>Управление товарами</h1>
                    <p>Всего товаров: <?= count($products) ?></p>
                </div>

                <!-- Навигация админки -->
                <nav class="admin-nav">
                    <a href="index.php" class="admin-nav__link">📊 Дашборд</a>
                    <a href="add_products.php" class="admin-nav__link">➕ Добавить товар</a>
                    <a href="manage_products.php" class="admin-nav__link active">📦 Управление товарами</a>
                    <a href="manage_orders.php" class="admin-nav__link">🛒 Заказы</a>
                    <a href="../catalog.php" class="admin-nav__link">🏠 На сайт</a>
                </nav>

                <!-- Таблица товаров -->
                <div class="admin-section">
                    <div class="products-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Изображение</th>
                                    <th>Название</th>
                                    <th>Категория</th>
                                    <th>Цена</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 40px;">
                                            Товаров пока нет
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr id="product-row-<?= $product['id'] ?>">
                                            <td><strong>#<?= $product['id'] ?></strong></td>
                                            <td>
                                                <div class="product-thumbnail">
                                                    <img src="../<?= htmlspecialchars($product['preview_image']) ?>"
                                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                                         onerror="this.src='../assets/img/placeholder.jpg'">
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                                            <td><strong><?= number_format($product['price'], 0, ',', ' ') ?> ₽</strong></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="../product.php?id=<?= $product['id'] ?>"
                                                       class="btn-small btn-view"
                                                       target="_blank">
                                                        👁️ Просмотр
                                                    </a>
                                                    <button class="btn-small btn-delete"
                                                            onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>')">
                                                        🗑️ Удалить
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

    <!-- Модальное окно подтверждения удаления -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeDeleteModal()">&times;</span>
            <h2>Подтверждение удаления</h2>
            <p>Вы действительно хотите удалить товар <strong id="deleteProductName"></strong>?</p>
            <p style="color: #dc3545; margin-top: 10px;">Это действие необратимо!</p>
            <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <button onclick="closeDeleteModal()" class="btn btn--secondary">Отмена</button>
                <button onclick="confirmDelete()" class="btn btn--danger">Удалить</button>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php' ?>

    <script>
        let productToDelete = null;

        function deleteProduct(productId, productName) {
            productToDelete = productId;
            document.getElementById('deleteProductName').textContent = productName;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            productToDelete = null;
        }

        function confirmDelete() {
            if (!productToDelete) return;

            fetch('../ajax/delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productToDelete
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Удаляем строку из таблицы
                    const row = document.getElementById('product-row-' + productToDelete);
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                    closeDeleteModal();

                    // Показываем уведомление
                    alert('Товар успешно удален!');
                } else {
                    alert('Ошибка при удалении: ' + (data.error || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении товара');
            });
        }

        // Закрытие модального окна при клике вне его
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>
