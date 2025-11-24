<?php
session_start();
require_once '../includes/connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../login.php?error=Доступ запрещен");
    exit;
}

// Получаем категории
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    
    // Валидация
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Введите название товара";
    }
    
    if (empty($description)) {
        $errors[] = "Введите описание товара";
    }
    
    if ($price <= 0) {
        $errors[] = "Укажите корректную цену";
    }
    
    if ($category_id <= 0) {
        $errors[] = "Выберите категорию";
    }
    
    // Обработка загрузки множественных изображений
    $uploaded_images = [];
    $preview_image = '';

    if (isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

        // Создаем уникальный ID для товара (будет использован как имя папки)
        $product_folder = uniqid('product_');
        $upload_dir = '../uploads/preview/' . $product_folder . '/';

        // Создаем директорию для изображений товара
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Обрабатываем каждое загруженное изображение
        foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['product_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_type = $_FILES['product_images']['type'][$key];

                if (!in_array($file_type, $allowed_types)) {
                    $errors[] = "Файл " . $_FILES['product_images']['name'][$key] . " не является изображением";
                    continue;
                }

                $file_extension = pathinfo($_FILES['product_images']['name'][$key], PATHINFO_EXTENSION);
                $file_name = uniqid('img_') . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($tmp_name, $file_path)) {
                    $uploaded_images[] = 'uploads/preview/' . $product_folder . '/' . $file_name;
                } else {
                    $errors[] = "Ошибка загрузки файла " . $_FILES['product_images']['name'][$key];
                }
            }
        }

        if (empty($uploaded_images)) {
            $errors[] = "Не удалось загрузить ни одного изображения";
        } else {
            // Первое изображение становится основным
            $preview_image = $uploaded_images[0];
        }
    } else {
        $errors[] = "Загрузите хотя бы одно изображение товара";
    }

    // Обработка загрузки 3D модели
    $model_file = null;
    if (isset($_FILES['model_file']) && $_FILES['model_file']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES['model_file']['name'], PATHINFO_EXTENSION));

        // Проверяем расширение файла (GLB, GLTF, FBX, OBJ)
        if (!in_array($file_extension, ['glb', 'gltf', 'fbx', 'obj'])) {
            $errors[] = "Разрешены только 3D модели (GLB, GLTF, FBX, OBJ)";
        } else {
            $models_dir = '../uploads/models/';

            // Создаем директорию если не существует
            if (!is_dir($models_dir)) {
                mkdir($models_dir, 0777, true);
            }

            $model_filename = uniqid('model_') . '.' . $file_extension;
            $model_path = $models_dir . $model_filename;

            if (move_uploaded_file($_FILES['model_file']['tmp_name'], $model_path)) {
                $model_file = 'uploads/models/' . $model_filename;
            } else {
                $errors[] = "Ошибка загрузки 3D модели";
            }
        }
    }
    
    // Если нет ошибок - добавляем товар
    if (empty($errors)) {
        try {
            // Начинаем транзакцию
            $pdo->beginTransaction();

            // Добавляем товар
            $stmt = $pdo->prepare("
                INSERT INTO products (name, description, price, category_id, preview_image, model_file)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name,
                $description,
                $price,
                $category_id,
                $preview_image,
                $model_file
            ]);

            $product_id = $pdo->lastInsertId();

            // Добавляем изображения в галерею
            if (!empty($uploaded_images)) {
                $stmt = $pdo->prepare("
                    INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
                    VALUES (?, ?, ?, ?)
                ");

                foreach ($uploaded_images as $index => $image_path) {
                    $is_primary = ($index === 0) ? 1 : 0; // Первое изображение - основное
                    $stmt->execute([$product_id, $image_path, $is_primary, $index]);
                }
            }

            // Фиксируем транзакцию
            $pdo->commit();

            $success_message = "Товар успешно добавлен!";
        } catch (PDOException $e) {
            // Откатываем транзакцию при ошибке
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = "Ошибка базы данных: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить товар - Админ-панель</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <?php include '../includes/header.php' ?>

    <main>
        <section class="admin-panel">
            <div class="container">
                <div class="admin-header">
                    <h1>Добавить товар</h1>
                </div>

                <!-- Навигация админки -->
                <nav class="admin-nav">
                    <a href="index.php" class="admin-nav__link">📊 Дашборд</a>
                    <a href="add_products.php" class="admin-nav__link active">➕ Добавить товар</a>
                    <a href="manage_products.php" class="admin-nav__link">📦 Управление товарами</a>
                    <a href="manage_orders.php" class="admin-nav__link">🛒 Заказы</a>
                    <a href="../catalog.php" class="admin-nav__link">🏠 На сайт</a>
                </nav>

                <!-- Форма добавления -->
                <div class="admin-form-container">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Ошибки:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success_message) ?>
                            <a href="add_products.php">Добавить еще товар</a>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="admin-form">
                        <div class="form-group">
                            <label for="name">Название товара *</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                   required
                                   placeholder="Например: Киберпанк воин">
                        </div>

                        <div class="form-group">
                            <label for="description">Описание *</label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="5" 
                                      required
                                      placeholder="Подробное описание товара"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Цена (₽) *</label>
                                <input type="number" 
                                       id="price" 
                                       name="price" 
                                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                                       min="0" 
                                       step="0.01" 
                                       required
                                       placeholder="1299.00">
                            </div>

                            <div class="form-group">
                                <label for="category_id">Категория *</label>
                                <select id="category_id" name="category_id" required>
                                    <option value="">Выберите категорию</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                                <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="product_images">Изображения товара * (можно выбрать несколько)</label>
                            <input type="file"
                                   id="product_images"
                                   name="product_images[]"
                                   accept="image/*"
                                   multiple
                                   required
                                   onchange="validateImages(this)">
                            <small>Форматы: JPG, PNG, WEBP. Максимум 5 МБ на файл, 20 МБ всего. Первое изображение будет основным.</small>
                            <div id="images-info" style="margin-top: 5px; color: #666;"></div>
                        </div>

                        <div class="form-group">
                            <label for="model_file">3D Модель (необязательно)</label>
                            <input type="file"
                                   id="model_file"
                                   name="model_file"
                                   accept=".glb,.gltf,.fbx,.obj"
                                   onchange="validateModel(this)">
                            <small>Форматы: GLB, GLTF, FBX, OBJ. Максимум 30 МБ. Рекомендуется GLB для лучшей совместимости</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn--primary">
                                Добавить товар
                            </button>
                            <a href="index.php" class="btn btn--secondary">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php' ?>

    <script>
        // Валидация размера изображений
        function validateImages(input) {
            const maxFileSize = 5 * 1024 * 1024; // 5MB на файл
            const maxTotalSize = 20 * 1024 * 1024; // 20MB всего
            const infoDiv = document.getElementById('images-info');

            if (input.files.length === 0) {
                infoDiv.textContent = '';
                return;
            }

            let totalSize = 0;
            let fileCount = input.files.length;
            let errors = [];

            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                totalSize += file.size;

                if (file.size > maxFileSize) {
                    errors.push(`"${file.name}" слишком большой (${formatBytes(file.size)}, макс 5MB)`);
                }
            }

            if (totalSize > maxTotalSize) {
                errors.push(`Общий размер ${formatBytes(totalSize)} превышает лимит в 20MB`);
            }

            if (errors.length > 0) {
                infoDiv.innerHTML = '<span style="color: #dc3545;">⚠️ ' + errors.join('<br>⚠️ ') + '</span>';
                input.value = ''; // Очищаем выбор
                return false;
            }

            // Показываем инфо об успешном выборе
            infoDiv.innerHTML = `<span style="color: #28a745;">✓ Выбрано файлов: ${fileCount}, общий размер: ${formatBytes(totalSize)}</span>`;
            return true;
        }

        // Валидация размера 3D модели
        function validateModel(input) {
            const maxFileSize = 30 * 1024 * 1024; // 30MB для моделей

            if (input.files.length === 0) return;

            const file = input.files[0];

            if (file.size > maxFileSize) {
                alert(`Файл "${file.name}" слишком большой (${formatBytes(file.size)}, макс 30MB)`);
                input.value = '';
                return false;
            }

            return true;
        }

        // Форматирование байтов в читаемый вид
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Предупреждение перед отправкой формы
        document.querySelector('.admin-form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Загрузка... Пожалуйста подождите';
            submitBtn.style.opacity = '0.6';
        });
    </script>
</body>
</html>