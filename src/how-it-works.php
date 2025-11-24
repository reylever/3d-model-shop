<?php
session_start();
require_once 'includes/connect.php';

$page_title = "Как это работает - 3D Model Shop";
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
                    <h1 class="page-title">Как это работает</h1>

                    <div class="content-block">
                        <h2>Процесс покупки 3D-моделей</h2>

                        <div class="step-item">
                            <h3>Шаг 1: Выбор модели</h3>
                            <p>Просмотрите наш каталог и выберите понравившуюся 3D-модель. На странице модели вы найдете детальное описание, превью и информацию о форматах.</p>
                        </div>

                        <div class="step-item">
                            <h3>Шаг 2: Добавление в корзину</h3>
                            <p>Нажмите кнопку "Добавить в корзину". Вы можете продолжить покупки или сразу перейти к оформлению заказа.</p>
                        </div>

                        <div class="step-item">
                            <h3>Шаг 3: Оформление заказа</h3>
                            <p>Заполните контактную информацию и выберите способ оплаты. Мы принимаем банковские карты и электронные кошельки.</p>
                        </div>

                        <div class="step-item">
                            <h3>Шаг 4: Получение файлов</h3>
                            <p>После успешной оплаты вы мгновенно получите ссылку на скачивание файлов. Ссылка также будет отправлена на вашу электронную почту.</p>
                        </div>

                        <div class="step-item">
                            <h3>Шаг 5: Использование</h3>
                            <p>Скачайте файлы и используйте модель в своем проекте. Все модели поставляются с лицензией на коммерческое использование.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
