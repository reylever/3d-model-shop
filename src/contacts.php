<?php
session_start();
require_once 'includes/connect.php';

$page_title = "Контакты - 3D Model Shop";
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
                    <h1 class="page-title">Контакты</h1>

                    <div class="content-block">
                        <h2>Свяжитесь с нами</h2>
                        <p>Мы всегда рады ответить на ваши вопросы и помочь с выбором 3D-моделей.</p>

                        <div class="contact-info">
                            <h3>Электронная почта</h3>
                            <p><strong>Общие вопросы:</strong> info@3dmodelshop.com</p>
                            <p><strong>Техническая поддержка:</strong> support@3dmodelshop.com</p>
                            <p><strong>Сотрудничество:</strong> partnership@3dmodelshop.com</p>

                            <h3>Время работы</h3>
                            <p>Понедельник - Пятница: 9:00 - 18:00 (МСК)</p>
                            <p>Суббота - Воскресенье: Выходной</p>

                            <h3>Социальные сети</h3>
                            <p>Следите за нашими новостями и обновлениями в социальных сетях.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
