<?php
session_start();
require_once 'includes/connect.php';

$page_title = "Форматы файлов - 3D Model Shop";
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
                    <h1 class="page-title">Форматы файлов 3D-моделей</h1>

                    <div class="content-block">
                        <h2>Поддерживаемые форматы</h2>

                        <div class="format-item">
                            <h3>FBX (Filmbox)</h3>
                            <p>Универсальный формат, поддерживаемый большинством 3D-приложений и игровых движков (Unity, Unreal Engine).</p>
                            <p><strong>Лучше всего подходит для:</strong> Игровых движков, анимации</p>
                        </div>

                        <div class="format-item">
                            <h3>OBJ (Wavefront)</h3>
                            <p>Простой и широко поддерживаемый формат. Идеален для статичных моделей.</p>
                            <p><strong>Лучше всего подходит для:</strong> Рендеринга, 3D-печати</p>
                        </div>

                        <div class="format-item">
                            <h3>GLTF (GL Transmission Format)</h3>
                            <p>Современный формат для веб-приложений и real-time рендеринга.</p>
                            <p><strong>Лучше всего подходит для:</strong> WebGL, AR/VR приложений</p>
                        </div>

                        <div class="format-item">
                            <h3>BLEND (Blender)</h3>
                            <p>Нативный формат Blender с сохранением всех настроек материалов и освещения.</p>
                            <p><strong>Лучше всего подходит для:</strong> Работы в Blender</p>
                        </div>

                        <h2>Текстуры и материалы</h2>
                        <p>Все модели поставляются с текстурами в высоком разрешении. Форматы текстур: PNG, JPG. В комплект входят карты: Diffuse, Normal, Roughness, Metallic.</p>

                        <h2>Совместимость</h2>
                        <p>Перед покупкой убедитесь, что выбранный формат совместим с вашим программным обеспечением. На странице каждой модели указаны доступные форматы.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
