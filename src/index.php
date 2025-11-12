<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Яшин стаффчик - 3д модели</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero__content">
                    <h1 class="hero__title">Покупайте профессиональные 3D-модели</h1>   
                    <p class="hero__text">Найдите нужный 3D-контент именно под ваши задачи</p>
                    <form class="hero__search" action="#" method="get">
                        <input 
                            type="search" 
                            name="q"
                            placeholder="Поиск среди множества 3D-моделей" 
                            class="hero__search-input"
                            aria-label="Поиск 3D-моделей"
                        >
                        <button type="submit" class="hero__search-btn" aria-label="Найти">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="9" r="7"/>
                                <path d="M14 14l4 4"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="showcase">
            <div class="container">
                <div class="showcase__header">
                    <h2 class="showcase__title">Исследуйте 3D-ассеты</h2>   
                    <button class="btn btn--primary">Посмотреть все 3д-модели</button>
                </div>
                    
                <div class="assets-grid">
                    <a href="#" class="asset-card asset-card--wide asset-card--tall asset-card--interior">
                        <span class="asset-card__label">Интерьер</span>
                    </a>
                    <a href="#" class="asset-card asset-card--cars">
                        <span class="asset-card__label">Машины</span>
                    </a>
                    <a href="#" class="asset-card asset-card--exterior">
                        <span class="asset-card__label">Экстерьер</span>
                    </a>
                    <a href="#" class="asset-card asset-card--weapons">
                        <span class="asset-card__label">Оружие</span>
                    </a>
                    <a href="#" class="asset-card asset-card--tall asset-card--characters">
                        <span class="asset-card__label">Персонажи</span>
                    </a>
                    <a href="#" class="asset-card asset-card--props">
                        <span class="asset-card__label">Реквизит</span>
                    </a>
                    <a href="#" class="asset-card asset-card--env">
                        <span class="asset-card__label">Окружение</span>
                    </a>
                    <a href="#" class="asset-card asset-card--scifi">
                        <span class="asset-card__label">Сай-фай</span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php' ?>
</body>
</html>