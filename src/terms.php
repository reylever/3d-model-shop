<?php
session_start();
require_once 'includes/connect.php';

$page_title = "Условия использования - 3D Model Shop";
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
                    <h1 class="page-title">Условия использования</h1>

                    <div class="content-block">
                        <p><em>Последнее обновление: <?php echo date('d.m.Y'); ?></em></p>

                        <h2>1. Принятие условий</h2>
                        <p>Используя наш сайт и приобретая 3D-модели, вы соглашаетесь с настоящими условиями использования.</p>

                        <h2>2. Лицензия на использование</h2>
                        <p>При покупке модели вы получаете неисключительную лицензию на использование 3D-модели в своих проектах.</p>

                        <h2>3. Ограничения</h2>
                        <ul>
                            <li>Запрещено перепродавать или распространять приобретенные модели</li>
                            <li>Запрещено использовать модели для создания конкурирующих библиотек 3D-контента</li>
                            <li>Запрещено передавать лицензию третьим лицам без письменного согласия</li>
                        </ul>

                        <h2>4. Авторские права</h2>
                        <p>Все права на 3D-модели принадлежат их создателям. Приобретая модель, вы получаете лицензию на использование, но не права собственности.</p>

                        <h2>5. Возврат и обмен</h2>
                        <p>Из-за цифровой природы товара возврат денежных средств невозможен после получения файлов.</p>

                        <h2>6. Изменение условий</h2>
                        <p>Мы оставляем за собой право изменять данные условия. Изменения вступают в силу после публикации на сайте.</p>

                        <h2>7. Контакты</h2>
                        <p>По всем вопросам обращайтесь: info@3dmodelshop.com</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
