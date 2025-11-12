<?php
// Подключаем БД для работы с корзиной
require_once __DIR__ . '/connect.php';
?>
<link rel="stylesheet" href="assets/css/style.css">
<header class="header">
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="assets/img/logo/logo.png" alt="Яшин стаффчик" class="logo__img">
                <span class="logo__text">Яшин Стаффчик</span>
            </a>
            
            <button class="navbar__toggle" id="navToggle" aria-label="Открыть меню">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="navbar__menu" id="navMenu">
                <li class="navbar__item">
                    <a href="index.php" class="navbar__link navbar__link--active">Главная</a>
                </li>
                <li class="navbar__item navbar__item--dropdown">
                    <a href="catalog.php" class="navbar__link">
                        Каталог
                        <svg class="dropdown__icon" width="12" height="8" viewBox="0 0 12 8" fill="none">
                            <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </a>
                    <ul class="dropdown__menu">
                        <li class="dropdown__header">Категории 3D моделей</li>
                        <li><a href="catalog.php?category=1" class="dropdown__link">
                            <svg width="16" height="16" fill="currentColor" class="dropdown__icon-item">
                                <use href="#icon-person"/>
                            </svg>
                            Персонажи
                        </a></li>
                        <li><a href="catalog.php?category=2" class="dropdown__link">
                            <svg width="16" height="16" fill="currentColor" class="dropdown__icon-item">
                                <use href="#icon-weapon"/>
                            </svg>
                            Оружие
                        </a></li>
                        <li><a href="catalog.php?category=3" class="dropdown__link">
                            <svg width="16" height="16" fill="currentColor" class="dropdown__icon-item">
                                <use href="#icon-house"/>
                            </svg>
                            Мебель
                        </a></li>
                        <li class="dropdown__divider"></li>
                        <li><a href="catalog.php" class="dropdown__link dropdown__link--all">Все категории</a></li>
                    </ul>
                </li>
                <li class="navbar__item">
                    <a href="cart.php" class="navbar__link cart-link">
                        Корзина
                        <?php
                        // Получаем количество товаров в корзине
                        if (isset($_SESSION['user_id'])) {
                            $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $cart_total = $stmt->fetch(PDO::FETCH_ASSOC);
                            $cart_count = (int)$cart_total['total'];
                            
                            if ($cart_count > 0) {
                                echo '<span class="cart-badge" id="cartBadge">' . $cart_count . '</span>';
                            }
                        }
                        ?>
                    </a>
                </li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Меню для авторизованного пользователя -->
                    <li class="navbar__item navbar__item--dropdown">
                        <a href="#" class="navbar__link">
                            👤 <?= htmlspecialchars($_SESSION['username']) ?>
                            <svg class="dropdown__icon" width="12" height="8" viewBox="0 0 12 8" fill="none">
                                <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </a>
                        <ul class="dropdown__menu">
                            <li><a href="profile.php" class="dropdown__link">👤 Профиль</a></li>
                            <li><a href="orders.php" class="dropdown__link">🛍️ Мои заказы</a></li>
                            <?php if ($_SESSION['is_admin']): ?>
                                <li class="dropdown__divider"></li>
                                <li><a href="admin/index.php" class="dropdown__link" style="color: #dc3545;">🛡️ Админ-панель</a></li>
                            <?php endif; ?>
                            <li class="dropdown__divider"></li>
                            <li><a href="auth/logout.php" class="dropdown__link">🚪 Выход</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Кнопка входа для неавторизованных -->
                    <li class="navbar__item">
                        <a href="login.php" class="navbar__link">Вход</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>

<!-- SVG спрайты для иконок -->
<svg style="display: none;">
    <symbol id="icon-person" viewBox="0 0 16 16">
        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
    </symbol>
    <symbol id="icon-weapon" viewBox="0 0 16 16">
        <path d="M12.5 3.5a.5.5 0 0 1 0 1h-1v1h1a.5.5 0 0 1 0 1h-1v1h1a.5.5 0 0 1 0 1h-1v1h1a.5.5 0 0 1 0 1h-1v.5a.5.5 0 0 1-1 0V11h-1v.5a.5.5 0 0 1-1 0V11h-1v.5a.5.5 0 0 1-1 0V11h-1v.5a.5.5 0 0 1-1 0V11h-.5a.5.5 0 0 1 0-1h.5v-1h-.5a.5.5 0 0 1 0-1h.5v-1h-.5a.5.5 0 0 1 0-1h.5v-1h-.5a.5.5 0 0 1 0-1h.5v-.5a.5.5 0 0 1 1 0V4h1v-.5a.5.5 0 0 1 1 0V4h1v-.5a.5.5 0 0 1 1 0V4h1v-.5a.5.5 0 0 1 1 0V4h.5z"/>
    </symbol>
    <symbol id="icon-house" viewBox="0 0 16 16">
        <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
    </symbol>
</svg>

<script>
// Мобильное меню
const navToggle = document.getElementById('navToggle');
const navMenu = document.getElementById('navMenu');

navToggle.addEventListener('click', () => {
    navToggle.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Закрытие меню при клике вне его
document.addEventListener('click', (e) => {
    if (!e.target.closest('.navbar')) {
        navToggle.classList.remove('active');
        navMenu.classList.remove('active');
    }
});

// Dropdown меню
document.querySelectorAll('.navbar__item--dropdown').forEach(item => {
    item.addEventListener('click', (e) => {
        if (window.innerWidth <= 992) {
            e.preventDefault();
            item.classList.toggle('active');
        }
    });
});
</script>