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
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Если корзина пуста - редирект
if (empty($cart_items)) {
    header("Location: cart.php");
    exit;
}

// Считаем общую сумму
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа - Яшин стаффчик</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main>
        <section class="checkout">
            <div class="container">
                <h1 class="checkout__title">Оформление заказа</h1>

                <div class="checkout-content">
                    <!-- Форма заказа -->
                    <div class="checkout-form">
                        <form id="checkoutForm" method="POST" action="ajax/checkout.php">
                            <div class="form-section">
                                <h2>Контактные данные</h2>
                                <div class="form-group">
                                    <label for="name">Имя *</label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="<?= htmlspecialchars($user['username']) ?>"
                                           required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           value="<?= htmlspecialchars($user['email']) ?>"
                                           required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Телефон *</label>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           placeholder="+7 (___) ___-__-__"
                                           required>
                                </div>
                            </div>

                            <div class="form-section">
                                <h2>Адрес доставки</h2>
                                <div class="form-group">
                                    <label for="address">Адрес *</label>
                                    <textarea id="address" 
                                              name="address" 
                                              rows="3" 
                                              placeholder="Улица, дом, квартира"
                                              required></textarea>
                                </div>
                            </div>

                            <div class="form-section">
                                <h2>Способ оплаты</h2>
                                <div class="payment-methods">
                                    <label class="payment-method">
                                        <input type="radio" name="payment" value="card" checked>
                                        <span class="payment-label">
                                            <strong>Банковская карта</strong>
                                            <small>Visa, MasterCard, МИР</small>
                                        </span>
                                    </label>
                                    <label class="payment-method">
                                        <input type="radio" name="payment" value="cash">
                                        <span class="payment-label">
                                            <strong>Наличными при получении</strong>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="comment">Комментарий к заказу</label>
                                <textarea id="comment" 
                                          name="comment" 
                                          rows="3" 
                                          placeholder="Дополнительные пожелания"></textarea>
                            </div>

                            <button type="submit" class="btn btn--primary btn--block">
                                Оформить заказ
                            </button>
                        </form>
                    </div>

                    <!-- Сводка заказа -->
                    <div class="order-summary">
                        <h2>Ваш заказ</h2>
                        
                        <div class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-item">
                                    <div class="order-item__image">
                                        <img src="<?= htmlspecialchars($item['preview_image']) ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>"
                                             onerror="this.src='assets/img/placeholder.jpg'">
                                    </div>
                                    <div class="order-item__info">
                                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                                        <p><?= $item['quantity'] ?> × <?= number_format($item['price'], 0, ',', ' ') ?> ₽</p>
                                    </div>
                                    <div class="order-item__price">
                                        <?= number_format($item['price'] * $item['quantity'], 0, ',', ' ') ?> ₽
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-summary__divider"></div>

                        <div class="order-summary__row">
                            <span>Товары</span>
                            <span><?= number_format($total, 0, ',', ' ') ?> ₽</span>
                        </div>
                        <div class="order-summary__row">
                            <span>Доставка</span>
                            <span>Бесплатно</span>
                        </div>

                        <div class="order-summary__divider"></div>

                        <div class="order-summary__total">
                            <span>Итого</span>
                            <span><?= number_format($total, 0, ',', ' ') ?> ₽</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php' ?>

    <script src="assets/js/checkout.js"></script>
</body>
</html>