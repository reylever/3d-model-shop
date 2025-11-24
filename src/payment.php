<?php
session_start();
require_once 'includes/connect.php';

$page_title = "Оплата - 3D Model Shop";
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
                    <h1 class="page-title">Способы оплаты</h1>

                    <div class="content-block">
                        <h2>Доступные способы оплаты</h2>

                        <div class="payment-option">
                            <h3>Банковские карты</h3>
                            <p>Мы принимаем все основные банковские карты: Visa, MasterCard, МИР. Оплата происходит через защищенный платежный шлюз.</p>
                        </div>

                        <div class="payment-option">
                            <h3>Электронные кошельки</h3>
                            <p>Доступна оплата через ЮMoney, QIWI, WebMoney и другие популярные электронные платежные системы.</p>
                        </div>

                        <div class="payment-option">
                            <h3>Онлайн-банкинг</h3>
                            <p>Оплата напрямую через систему онлайн-банкинга вашего банка.</p>
                        </div>

                        <h2>Безопасность платежей</h2>
                        <ul>
                            <li>Все платежи защищены современными протоколами шифрования</li>
                            <li>Мы не храним данные ваших банковских карт</li>
                            <li>Платежи обрабатываются через сертифицированные платежные системы</li>
                        </ul>

                        <h2>Получение чека</h2>
                        <p>После успешной оплаты электронный чек автоматически отправляется на указанную вами электронную почту.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
