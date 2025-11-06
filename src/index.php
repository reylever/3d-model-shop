<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Яшин стаффчик - 3д модели</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero_content">
                    <h1>Покупайте профессиональные 3D-модели</h1>   
                    <p>Найдите нужный 3D-контент именно под ваши задачи</p>
                    <form class="hero__search d-flex justify-content-center">
                        <input type="text" placeholder="Поиск среди множества 3D-моделей" class="form-control me-2">
                        <button type="submit" class="btn btn-info">
                        <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="model_showcase">
            <div class="container">
                <div class="assets_showcase-content">

                    <div class="assets_showcase-header">
                        <h3>Исследуйте 3D-ассеты</h3>   
                        <button class="default_button">Посмотреть все 3д-модели</button>
                    </div>
                        
                    <div class="assets_showcase-main">
                        <div class="assets_grid">
                            <div class="grid_item interior wide tall"><span>Интерьер</span></div>
                            <div class="grid_item cars"><span>Машины</span></div>
                            <div class="grid_item exterior"><span>Экстерьер</span></div>
                            <div class="grid_item weapons"><span>Оружие</span></div>
                            <div class="grid_item characters tall"><span>Персонажи</span></div>
                            <div class="grid_item props"><span>Реквизит</span></div>
                            <div class="grid_item env"><span>Окружение</span></div>
                            <div class="grid_item sci-fi"><span>Сай-фай</span></div>
                        </div>
                    </div>
                </div>
                <div class="printready_showcase-content">

                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php' ?>
</body>
</html>