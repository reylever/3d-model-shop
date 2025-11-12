<?php
session_start();
require_once 'includes/connect.php';

// Получаем ID товара
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header("Location: catalog.php");
    exit;
}

// Получаем информацию о товаре
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.id as category_id
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: catalog.php");
    exit;
}

// Получаем изображения товара из галереи
$stmt = $pdo->prepare("
    SELECT * FROM product_images
    WHERE product_id = ?
    ORDER BY is_primary DESC, sort_order ASC
");
$stmt->execute([$product_id]);
$product_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Если галерея пустая, используем preview_image
if (empty($product_images) && $product['preview_image']) {
    $product_images = [
        ['image_path' => $product['preview_image'], 'is_primary' => 1]
    ];
}

// Получаем похожие товары из той же категории
$stmt = $pdo->prepare("
    SELECT * FROM products
    WHERE category_id = ? AND id != ?
    ORDER BY RAND()
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product_id]);
$similar_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Яшин стаффчик</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/product.css">
    <script type="importmap">
    {
        "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
            "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
        }
    }
    </script>
    <style>
        #model-viewer {
            width: 100%;
            height: 100%;
            min-height: 500px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }

        #model-viewer canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        .viewer-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.7);
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            z-index: 10;
        }

        .viewer-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 1.2rem;
            text-align: center;
        }

        .viewer-error {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            text-align: center;
            max-width: 80%;
        }

        .fallback-image {
            display: none;
        }

        .viewer-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .viewer-tab {
            padding: 10px 20px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: #6c757d;
            transition: color 0.3s;
            border-bottom: 3px solid transparent;
            margin-bottom: -12px;
        }

        .viewer-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        /* Слайдер изображений */
        .image-gallery {
            position: relative;
        }

        .gallery-slider {
            position: relative;
            width: 100%;
            height: 500px;
            border-radius: 12px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-slider img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 32px;
            cursor: pointer;
            transition: background 0.3s;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .slider-btn:hover {
            background: rgba(0,0,0,0.8);
        }

        .slider-prev {
            left: 20px;
        }

        .slider-next {
            right: 20px;
        }

        .slider-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            z-index: 10;
        }

        /* Темная тема для product.php */
        [data-theme="dark"] .gallery-slider {
            background: var(--bg-secondary, #2d2d2d);
        }

        [data-theme="dark"] .product-detail__info,
        [data-theme="dark"] .product-detail__title,
        [data-theme="dark"] .product-detail__description h3,
        [data-theme="dark"] .meta-item strong {
            color: var(--text-primary, #e0e0e0);
        }

        [data-theme="dark"] .product-detail__category,
        [data-theme="dark"] .product-detail__description p,
        [data-theme="dark"] .meta-item span {
            color: var(--text-secondary, #a0a0a0);
        }

        [data-theme="dark"] .breadcrumbs a,
        [data-theme="dark"] .breadcrumbs span {
            color: var(--text-secondary, #a0a0a0);
        }

        [data-theme="dark"] .similar-products h2 {
            color: var(--text-primary, #e0e0e0);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main>
        <section class="product">
            <div class="container">
                <!-- Хлебные крошки -->
                <nav class="breadcrumbs">
                    <a href="index.php">Главная</a>
                    <span>/</span>
                    <a href="catalog.php">Каталог</a>
                    <span>/</span>
                    <a href="catalog.php?category=<?= $product['category_id'] ?>">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </a>
                    <span>/</span>
                    <span><?= htmlspecialchars($product['name']) ?></span>
                </nav>

                <div class="product-detail">
                    <!-- 3D Viewer / Изображение товара -->
                    <div class="product-detail__image">
                        <div class="viewer-tabs">
                            <button class="viewer-tab active" data-tab="3d">3D Модель</button>
                            <button class="viewer-tab" data-tab="image">Изображение</button>
                        </div>

                        <!-- 3D Viewer -->
                        <div id="model-viewer-container" class="tab-content active">
                            <div id="model-viewer" data-model="<?= $product['model_file'] ? htmlspecialchars($product['model_file']) : '' ?>">
                                <div class="viewer-loading">
                                    <div>⏳ Загрузка 3D модели...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Галерея изображений -->
                        <div id="image-viewer-container" class="tab-content" style="display: none;">
                            <?php if (!empty($product_images)): ?>
                                <div class="image-gallery">
                                    <div class="gallery-slider">
                                        <img src="<?= htmlspecialchars($product_images[0]['image_path']) ?>"
                                             alt="<?= htmlspecialchars($product['name']) ?>"
                                             onerror="this.src='assets/img/placeholder.jpg'"
                                             id="slider-image">

                                        <?php if (count($product_images) > 1): ?>
                                            <button class="slider-btn slider-prev" onclick="prevImage()">‹</button>
                                            <button class="slider-btn slider-next" onclick="nextImage()">›</button>
                                            <div class="slider-counter">
                                                <span id="current-slide">1</span> / <?= count($product_images) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <script>
                                    const images = <?= json_encode(array_column($product_images, 'image_path')) ?>;
                                    let currentIndex = 0;

                                    function showImage(index) {
                                        currentIndex = (index + images.length) % images.length;
                                        document.getElementById('slider-image').src = images[currentIndex];
                                        document.getElementById('current-slide').textContent = currentIndex + 1;
                                    }

                                    function nextImage() {
                                        showImage(currentIndex + 1);
                                    }

                                    function prevImage() {
                                        showImage(currentIndex - 1);
                                    }

                                    // Клавиатура
                                    document.addEventListener('keydown', (e) => {
                                        if (document.getElementById('image-viewer-container').style.display !== 'none') {
                                            if (e.key === 'ArrowLeft') prevImage();
                                            if (e.key === 'ArrowRight') nextImage();
                                        }
                                    });
                                </script>
                            <?php else: ?>
                                <div class="gallery-slider">
                                    <img src="assets/img/placeholder.jpg" alt="Нет изображения">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Информация о товаре -->
                    <div class="product-detail__info">
                        <div class="product-detail__category">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </div>
                        
                        <h1 class="product-detail__title">
                            <?= htmlspecialchars($product['name']) ?>
                        </h1>

                        <div class="product-detail__price">
                            <?= number_format($product['price'], 0, ',', ' ') ?> ₽
                        </div>

                        <div class="product-detail__description">
                            <h3>Описание</h3>
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        </div>

                        <div class="product-detail__meta">
                            <div class="meta-item">
                                <strong>Формат файла:</strong>
                                <span>
                                    <?php
                                    $ext = strtoupper(pathinfo($product['model_file'], PATHINFO_EXTENSION));
                                    echo $ext ?: 'N/A';
                                    ?>
                                </span>
                            </div>
                            <div class="meta-item">
                                <strong>Тип модели:</strong>
                                <span>Цифровая 3D модель</span>
                            </div>
                            <div class="meta-item">
                                <strong>Дата добавления:</strong>
                                <span><?= date('d.m.Y', strtotime($product['created_at'])) ?></span>
                            </div>
                        </div>

                        <div class="product-detail__actions">
                            <button class="btn btn--primary btn-add-cart" data-product-id="<?= $product['id'] ?>">
                                Добавить в корзину
                            </button>
                            <a href="catalog.php" class="btn btn--secondary">
                                Вернуться к каталогу
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Похожие товары -->
                <?php if (!empty($similar_products)): ?>
                    <section class="similar-products">
                        <h2>Похожие товары</h2>
                        <div class="products-grid">
                            <?php foreach ($similar_products as $similar): ?>
                                <div class="product-card">
                                    <a href="product.php?id=<?= $similar['id'] ?>" class="product-card__link">
                                        <div class="product-card__image">
                                            <img src="<?= htmlspecialchars($similar['preview_image']) ?>" 
                                                 alt="<?= htmlspecialchars($similar['name']) ?>"
                                                 onerror="this.src='assets/img/placeholder.jpg'">
                                        </div>
                                        <div class="product-card__info">
                                            <h3 class="product-card__title">
                                                <?= htmlspecialchars($similar['name']) ?>
                                            </h3>
                                            <div class="product-card__footer">
                                                <span class="product-card__price">
                                                    <?= number_format($similar['price'], 0, ',', ' ') ?> ₽
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php' ?>

    <script type="module">
        import * as THREE from 'three';
        import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        // Переключение табов
        document.querySelectorAll('.viewer-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.dataset.tab;

                // Обновляем активные табы
                document.querySelectorAll('.viewer-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                // Показываем нужный контент
                if (targetTab === '3d') {
                    document.getElementById('model-viewer-container').style.display = 'block';
                    document.getElementById('image-viewer-container').style.display = 'none';
                } else {
                    document.getElementById('model-viewer-container').style.display = 'none';
                    document.getElementById('image-viewer-container').style.display = 'block';
                }
            });
        });

        // Инициализация 3D viewer
        const container = document.getElementById('model-viewer');
        const modelPath = container.dataset.model;

        if (!modelPath || modelPath === 'models/placeholder.glb') {
            container.innerHTML = '<div class="viewer-error"><h3>3D модель недоступна</h3><p>Для этого товара пока не загружена 3D модель</p></div>';
        } else {
            // Создаем сцену
            const scene = new THREE.Scene();
            scene.background = new THREE.Color(0x1a1a2e);

            // Камера
            const camera = new THREE.PerspectiveCamera(
                45,
                container.clientWidth / container.clientHeight,
                0.1,
                1000
            );
            camera.position.set(0, 1, 5);

            // Рендерер
            const renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            renderer.outputColorSpace = THREE.SRGBColorSpace;
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1;

            // Очищаем контейнер и добавляем canvas
            container.innerHTML = '';
            container.appendChild(renderer.domElement);

            // Контролы
            const controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.minDistance = 1;
            controls.maxDistance = 20;
            controls.target.set(0, 0, 0);

            // Освещение
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.position.set(5, 10, 7.5);
            scene.add(directionalLight);

            const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.5);
            directionalLight2.position.set(-5, 5, -5);
            scene.add(directionalLight2);

            // Подсветка снизу
            const bottomLight = new THREE.HemisphereLight(0xffffff, 0x444444, 0.5);
            scene.add(bottomLight);

            // Загрузка модели
            const loader = new GLTFLoader();
            loader.load(
                modelPath,
                (gltf) => {
                    const model = gltf.scene;

                    // Центрируем модель
                    const box = new THREE.Box3().setFromObject(model);
                    const center = box.getCenter(new THREE.Vector3());
                    const size = box.getSize(new THREE.Vector3());

                    const maxDim = Math.max(size.x, size.y, size.z);
                    const scale = 2 / maxDim;
                    model.scale.multiplyScalar(scale);

                    model.position.x = -center.x * scale;
                    model.position.y = -center.y * scale;
                    model.position.z = -center.z * scale;

                    scene.add(model);

                    // Добавляем подсказку управления
                    const controlsHint = document.createElement('div');
                    controlsHint.className = 'viewer-controls';
                    controlsHint.innerHTML = '🖱️ Вращайте мышью • 🔍 Зум колесиком';
                    container.appendChild(controlsHint);
                },
                (progress) => {
                    const percent = (progress.loaded / progress.total) * 100;
                    const loading = container.querySelector('.viewer-loading');
                    if (loading) {
                        loading.innerHTML = `<div>⏳ Загрузка ${percent.toFixed(0)}%...</div>`;
                    }
                },
                (error) => {
                    console.error('Ошибка загрузки модели:', error);
                    container.innerHTML = '<div class="viewer-error"><h3>Ошибка загрузки</h3><p>Не удалось загрузить 3D модель</p></div>';
                }
            );

            // Анимация
            function animate() {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            }
            animate();

            // Адаптивность
            window.addEventListener('resize', () => {
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            });
        }
    </script>

    <script>
        // AJAX добавление в корзину
        document.querySelector('.btn-add-cart').addEventListener('click', function() {
            const productId = this.dataset.productId;
            const button = this;
            
            // Проверяем авторизацию
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('Необходимо войти в систему');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>
            
            // Отправляем запрос
            fetch('ajax/cart_add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Успешно добавлено
                    button.textContent = '✓ Добавлено в корзину';
                    button.style.background = '#28a745';

                    // Обновляем счетчик в header
                    const cartBadge = document.getElementById('cartBadge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                        cartBadge.style.animation = 'none';
                        setTimeout(() => {
                            cartBadge.style.animation = 'cartPulse 0.3s ease';
                        }, 10);
                    } else if (data.cart_count > 0) {
                        // Создаем badge если его не было
                        const cartLink = document.querySelector('.cart-link');
                        const badge = document.createElement('span');
                        badge.className = 'cart-badge';
                        badge.id = 'cartBadge';
                        badge.textContent = data.cart_count;
                        cartLink.appendChild(badge);
                    }

                    setTimeout(() => {
                        button.textContent = 'Добавить в корзину';
                        button.style.background = '';
                    }, 2000);
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Произошла ошибка');
            });
        });
    </script>
</body>
</html>