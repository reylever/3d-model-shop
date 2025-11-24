<?php
session_start();
require_once 'includes/connect.php';

$page_title = "Лицензирование - 3D Model Shop";
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
                    <h1 class="page-title">Лицензирование 3D-моделей</h1>

                    <div class="content-block">
                        <h2>Стандартная лицензия</h2>
                        <p>При покупке любой модели вы получаете стандартную лицензию, которая включает:</p>

                        <h3>Что разрешено:</h3>
                        <ul>
                            <li>Использование в коммерческих проектах (игры, фильмы, приложения)</li>
                            <li>Модификация модели под ваши нужды</li>
                            <li>Использование в неограниченном количестве проектов</li>
                            <li>Создание рендеров и изображений для продажи</li>
                        </ul>

                        <h3>Что запрещено:</h3>
                        <ul>
                            <li>Перепродажа исходных файлов модели</li>
                            <li>Распространение модели как отдельного продукта</li>
                            <li>Создание конкурирующих библиотек 3D-контента</li>
                            <li>Передача прав на модель третьим лицам</li>
                        </ul>

                        <h2>Авторские права</h2>
                        <p>Все права на модель остаются у автора. Покупая модель, вы приобретаете лицензию на использование, но не права собственности.</p>

                        <h2>Атрибуция</h2>
                        <p>Указание авторства не требуется, но приветствуется.</p>

                        <h2>Вопросы по лицензированию</h2>
                        <p>Если у вас есть вопросы о лицензии или вам нужна специальная лицензия, свяжитесь с нами: licensing@3dmodelshop.com</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
