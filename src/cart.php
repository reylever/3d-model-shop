<?php
session_start();
require_once 'includes/connect.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Необходимо войти в систему");
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем товары из корзины
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.preview_image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Считаем общую сумму
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - Яшин стаффчик</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main>
        <section class="cart">
            <div class="container">
                <h1 class="cart__title">Корзина</h1>

                <?php if (empty($cart_items)): ?>
                    <div class="cart-empty">
                        <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <h2>Ваша корзина пуста</h2>
                        <p>Добавьте товары из каталога</p>
                        <a href="catalog.php" class="btn btn--primary">Перейти в каталог</a>
                    </div>
                <?php else: ?>
                    <div class="cart-content">
                        <!-- Список товаров -->
                        <div class="cart-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item" data-product-id="<?= $item['product_id'] ?>">
                                    <div class="cart-item__image">
                                        <img src="<?= htmlspecialchars($item['preview_image']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             onerror="this.src='assets/img/placeholder.jpg'">
                                    </div>

                                    <div class="cart-item__info">
                                        <h3 class="cart-item__title">
                                            <a href="product.php?id=<?= $item['product_id'] ?>">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                        </h3>
                                        <p class="cart-item__price">
                                            <?= number_format($item['price'], 0, ',', ' ') ?> ₽
                                        </p>
                                    </div>

                                    <div class="cart-item__quantity">
                                        <button class="quantity-btn quantity-minus" data-product-id="<?= $item['product_id'] ?>">−</button>
                                        <input type="number" 
                                               class="quantity-input" 
                                               value="<?= $item['quantity'] ?>" 
                                               min="1" 
                                               max="99"
                                               data-product-id="<?= $item['product_id'] ?>"
                                               readonly>
                                        <button class="quantity-btn quantity-plus" data-product-id="<?= $item['product_id'] ?>">+</button>
                                    </div>

                                    <div class="cart-item__total">
                                        <span class="item-total-price">
                                            <?= number_format($item['price'] * $item['quantity'], 0, ',', ' ') ?> ₽
                                        </span>
                                    </div>

                                    <button class="cart-item__remove" data-product-id="<?= $item['product_id'] ?>">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Итого -->
                        <div class="cart-summary">
                            <h2>Итого</h2>
                            <div class="summary-row">
                                <span>Товары (<?= count($cart_items) ?>)</span>
                                <span class="cart-subtotal"><?= number_format($total, 0, ',', ' ') ?> ₽</span>
                            </div>
                            <div class="summary-row">
                                <span>Доставка</span>
                                <span>Бесплатно</span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-row summary-total">
                                <span>Всего</span>
                                <span class="cart-total"><?= number_format($total, 0, ',', ' ') ?> ₽</span>
                            </div>
                            <a href="checkout.php" class="btn btn--primary btn--block">
                                Оформить заказ
                            </a>
                            <a href="catalog.php" class="btn btn--secondary btn--block">
                                Продолжить покупки
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php' ?>

    <script src="assets/js/cart.js"></script>
</body>
</html>