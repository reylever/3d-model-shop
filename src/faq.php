<?php
session_start();
require_once 'includes/connect.php';

$page_title = "FAQ - Часто задаваемые вопросы";
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
                    <h1 class="page-title">Часто задаваемые вопросы (FAQ)</h1>

                    <div class="content-block">
                        <div class="faq-item">
                            <h3>Как приобрести 3D-модель?</h3>
                            <p>Выберите понравившуюся модель в каталоге, добавьте её в корзину и оформите заказ. После оплаты вы получите ссылку на скачивание.</p>
                        </div>

                        <div class="faq-item">
                            <h3>В каких форматах доступны модели?</h3>
                            <p>Большинство моделей доступны в форматах FBX, OBJ, GLTF и Blender. Точный список форматов указан на странице каждой модели.</p>
                        </div>

                        <div class="faq-item">
                            <h3>Можно ли использовать модели в коммерческих проектах?</h3>
                            <p>Да, все модели поставляются с лицензией, позволяющей использование в коммерческих проектах. Подробности в разделе "Лицензирование".</p>
                        </div>

                        <div class="faq-item">
                            <h3>Как происходит оплата?</h3>
                            <p>Мы принимаем оплату банковскими картами, электронными кошельками и другими популярными способами оплаты.</p>
                        </div>

                        <div class="faq-item">
                            <h3>Могу ли я вернуть товар?</h3>
                            <p>Из-за цифровой природы товара возврат невозможен. Однако если модель имеет существенные дефекты, мы рассмотрим вашу проблему индивидуально.</p>
                        </div>

                        <div class="faq-item">
                            <h3>Предоставляете ли вы техническую поддержку?</h3>
                            <p>Да, наша служба поддержки готова помочь вам с любыми вопросами. Напишите нам на support@3dmodelshop.com.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
