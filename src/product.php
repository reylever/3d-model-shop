<?php
session_start();
require_once 'includes/connect.php';

// Получаем ID товара
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header("Location: catalog.php");
    exit;
}

// Получаем информацию о товаре
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.id as category_id
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: catalog.php");
    exit;
}

// Получаем похожие товары из той же категории
$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product_id]);
$similar_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Яшин стаффчик</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/product.css">
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main>
        <section class="product">
            <div class="container">
                <!-- Хлебные крошки -->
                <nav class="breadcrumbs">
                    <a href="index.php">Главная</a>
                    <span>/</span>
                    <a href="catalog.php">Каталог</a>
                    <span>/</span>
                    <a href="catalog.php?category=<?= $product['category_id'] ?>">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </a>
                    <span>/</span>
                    <span><?= htmlspecialchars($product['name']) ?></span>
                </nav>

                <div class="product-detail">
                    <!-- Изображение товара -->
                    <div class="product-detail__image">
                        <?php if ($product['preview_image']): ?>
                            <img src="<?= htmlspecialchars($product['preview_image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.src='assets/img/placeholder.jpg'">
                        <?php else: ?>
                            <img src="assets/img/placeholder.jpg" alt="Нет изображения">
                        <?php endif; ?>
                    </div>

                    <!-- Информация о товаре -->
                    <div class="product-detail__info">
                        <div class="product-detail__category">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </div>
                        
                        <h1 class="product-detail__title">
                            <?= htmlspecialchars($product['name']) ?>
                        </h1>

                        <div class="product-detail__price">
                            <?= number_format($product['price'], 0, ',', ' ') ?> ₽
                        </div>

                        <div class="product-detail__description">
                            <h3>Описание</h3>
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        </div>

                        <div class="product-detail__meta">
                            <div class="meta-item">
                                <strong>Формат файла:</strong>
                                <span>.GLB (GLTF Binary)</span>
                            </div>
                            <div class="meta-item">
                                <strong>Дата добавления:</strong>
                                <span><?= date('d.m.Y', strtotime($product['created_at'])) ?></span>
                            </div>
                        </div>

                        <div class="product-detail__actions">
                            <button class="btn btn--primary btn-add-cart" data-product-id="<?= $product['id'] ?>">
                                Добавить в корзину
                            </button>
                            <a href="catalog.php" class="btn btn--secondary">
                                Вернуться к каталогу
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Похожие товары -->
                <?php if (!empty($similar_products)): ?>
                    <section class="similar-products">
                        <h2>Похожие товары</h2>
                        <div class="products-grid">
                            <?php foreach ($similar_products as $similar): ?>
                                <div class="product-card">
                                    <a href="product.php?id=<?= $similar['id'] ?>" class="product-card__link">
                                        <div class="product-card__image">
                                            <img src="<?= htmlspecialchars($similar['preview_image']) ?>" 
                                                 alt="<?= htmlspecialchars($similar['name']) ?>"
                                                 onerror="this.src='assets/img/placeholder.jpg'">
                                        </div>
                                        <div class="product-card__info">
                                            <h3 class="product-card__title">
                                                <?= htmlspecialchars($similar['name']) ?>
                                            </h3>
                                            <div class="product-card__footer">
                                                <span class="product-card__price">
                                                    <?= number_format($similar['price'], 0, ',', ' ') ?> ₽
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php' ?>

    <script>
        // AJAX добавление в корзину
        document.querySelector('.btn-add-cart').addEventListener('click', function() {
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
                    // Успешно добавлено
                    button.textContent = '✓ Добавлено в корзину';
                    button.style.background = '#28a745';
                    
                    setTimeout(() => {
                        button.textContent = 'Добавить в корзину';
                        button.style.background = '';
                    }, 2000);
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Произошла ошибка');
            });
        });
    </script>
</body>
</html>