<?php
session_start();
require_once 'includes/connect.php';

$page_title = "Сотрудничество - 3D Model Shop";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <section class="page-content">
            <div class="container">
                <div class="content-wrapper">
                    <h1 class="page-title">Сотрудничество</h1>

                    <div class="content-block">
                        <h2>Станьте нашим партнером</h2>
                        <p>Мы приглашаем талантливых 3D-художников к сотрудничеству!</p>

                        <h3>Для 3D-художников</h3>
                        <ul>
                            <li>Размещайте свои модели на нашей платформе</li>
                            <li>Получайте пассивный доход от продаж</li>
                            <li>Конкурентные условия комиссии</li>
                            <li>Помощь в продвижении ваших работ</li>
                        </ul>

                        <h3>Требования к моделям</h3>
                        <ul>
                            <li>Оригинальность и качество исполнения</li>
                            <li>Чистая топология и правильные UV-развертки</li>
                            <li>Несколько форматов файлов (FBX, OBJ, Blender)</li>
                            <li>Качественные текстуры и материалы</li>
                        </ul>

                        <h3>Условия сотрудничества</h3>
                        <p>Комиссия платформы составляет 30% от стоимости продажи. Выплаты производятся ежемесячно.</p>

                        <h3>Как начать</h3>
                        <p>Отправьте свое портфолио и примеры работ на: <strong>partnership@3dmodelshop.com</strong></p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
