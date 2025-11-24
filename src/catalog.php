<?php
session_start();
require_once 'includes/connect.php';

// Получаем выбранную категорию (если есть)
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Получаем поисковый запрос (если есть)
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Получаем список всех категорий для фильтра
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Формируем SQL-запрос для товаров
$sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

// Фильтр по категории
if ($category_id) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
}

// Фильтр по поиску
if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем ID товаров в корзине пользователя
$cart_product_ids = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT product_id FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_product_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог 3D-моделей - Яшин стаффчик</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main>
        <section class="catalog">
            <div class="container">
                <h1 class="catalog__title">
                    <?php if (!empty($search_query)): ?>
                        Результаты поиска: "<?= htmlspecialchars($search_query) ?>"
                    <?php else: ?>
                        Каталог 3D-моделей
                    <?php endif; ?>
                </h1>

                <!-- Фильтр по категориям -->
                <div class="catalog__filters">
                    <a href="catalog.php" class="filter-btn <?= !$category_id && empty($search_query) ? 'active' : '' ?>">
                        Все категории
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="catalog.php?category=<?= $category['id'] ?>" 
                           class="filter-btn <?= $category_id == $category['id'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Сетка товаров -->
                <div class="products-grid">
                    <?php if (empty($products)): ?>
                        <div class="no-products">
                            <?php if (!empty($search_query)): ?>
                                <p>По запросу "<?= htmlspecialchars($search_query) ?>" ничего не найдено</p>
                                <a href="catalog.php" class="btn btn--primary">Показать все товары</a>
                            <?php else: ?>
                                <p>В этой категории пока нет товаров</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <a href="product.php?id=<?= $product['id'] ?>" class="product-card__link">
                                    <div class="product-card__image">
                                        <?php if ($product['preview_image']): ?>
                                            <img src="<?= htmlspecialchars($product['preview_image']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                                 onerror="this.src='assets/img/placeholder.jpg'">
                                        <?php else: ?>
                                            <img src="assets/img/placeholder.jpg" alt="Нет изображения">
                                        <?php endif; ?>
                                        <div class="product-card__category">
                                            <?= htmlspecialchars($product['category_name']) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="product-card__info">
                                        <h3 class="product-card__title">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </h3>
                                        <p class="product-card__description">
                                            <?= htmlspecialchars(mb_substr($product['description'], 0, 80)) ?>...
                                        </p>
                                        <div class="product-card__footer">
                                            <span class="product-card__price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</span>
                                            <?php if (in_array($product['id'], $cart_product_ids)): ?>
                                                <button class="btn-in-cart" disabled>
                                                    ✓ В корзине
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-add-cart" data-product-id="<?= $product['id'] ?>">
                                                    В корзину
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php' ?>

    <script>
        // AJAX добавление в корзину
        document.querySelectorAll('.btn-add-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = this.dataset.productId;
                const button = this;
                
                // Проверяем авторизацию
                <?php if (!isset($_SESSION['user_id'])): ?>
                    alert('Необходимо войти в систему');
                    window.location.href = 'login.php';
                    return;
                <?php endif; ?>
                
                // Отправляем запрос
                fetch('ajax/cart_add.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Заменяем кнопку на "В корзине"
                        button.textContent = '✓ В корзине';
                        button.className = 'btn-in-cart';
                        button.disabled = true;
                        
                        // Обновляем счетчик в header
                        const cartBadge = document.getElementById('cartBadge');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                            cartBadge.style.animation = 'none';
                            setTimeout(() => {
                                cartBadge.style.animation = 'cartPulse 0.3s ease';
                            }, 10);
                        } else if (data.cart_count > 0) {
                            // Создаем badge если его не было
                            const cartLink = document.querySelector('.cart-link');
                            const badge = document.createElement('span');
                            badge.className = 'cart-badge';
                            badge.id = 'cartBadge';
                            badge.textContent = data.cart_count;
                            cartLink.appendChild(badge);
                        }
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка');
                });
            });
        });
    </script>
</body>
</html>