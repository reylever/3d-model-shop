<?php
session_start();
require_once 'includes/connect.php';

$page_title = "О нас - 3D Model Shop";
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
                    <h1 class="page-title">О нас</h1>

                    <div class="content-block">
                        <p>Добро пожаловать в 3D Model Shop — ваш надежный источник качественных 3D-моделей для различных проектов.</p>

                        <h2>Наша миссия</h2>
                        <p>Мы стремимся предоставить дизайнерам, разработчикам игр и 3D-художникам доступ к высококачественным 3D-моделям по доступным ценам.</p>

                        <h2>Что мы предлагаем</h2>
                        <ul>
                            <li>Широкий выбор 3D-моделей различных категорий</li>
                            <li>Модели в популярных форматах (FBX, OBJ, GLTF, Blender)</li>
                            <li>Регулярное обновление каталога</li>
                            <li>Профессиональная поддержка клиентов</li>
                            <li>Гибкая система лицензирования</li>
                        </ul>

                        <h2>Наша команда</h2>
                        <p>Мы — команда энтузиастов 3D-графики, которые понимают потребности профессионалов и любителей в этой области.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
