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
    
    // Обработка загрузки изображения
    $preview_image = '';
    if (isset($_FILES['preview_image']) && $_FILES['preview_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $file_type = $_FILES['preview_image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Разрешены только изображения (JPG, PNG, WEBP)";
        } else {
            $upload_dir = '../assets/img/products/';
            
            // Создаем директорию если не существует
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['preview_image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('product_') . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['preview_image']['tmp_name'], $file_path)) {
                $preview_image = 'assets/img/products/' . $file_name;
            } else {
                $errors[] = "Ошибка загрузки изображения";
            }
        }
    } else {
        $errors[] = "Загрузите изображение товара";
    }
    
    // Если нет ошибок - добавляем товар
    if (empty($errors)) {
        try {
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
                'models/placeholder.glb' // Заглушка для модели
            ]);
            
            $success_message = "Товар успешно добавлен!";
        } catch (PDOException $e) {
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                    <a href="manage_orders.php" class="admin-nav__link">📦 Заказы</a>
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
                            <label for="preview_image">Изображение товара *</label>
                            <input type="file" 
                                   id="preview_image" 
                                   name="preview_image" 
                                   accept="image/*"
                                   required>
                            <small>Форматы: JPG, PNG, WEBP. Максимум 5 МБ</small>
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
</body>
</html>