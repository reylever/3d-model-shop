<?php
session_start();
require_once 'includes/connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Необходимо войти в систему");
    exit;
}

// Получаем информацию о пользователе
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: auth/logout.php");
    exit;
}

// Обработка обновления профиля
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Обновление основной информации
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $errors = [];

        if (empty($username)) {
            $errors[] = "Введите имя пользователя";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Введите корректный email";
        }

        // Проверка уникальности email (если изменился)
        if ($email !== $user['email']) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $errors[] = "Email уже используется";
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$username, $email, $_SESSION['user_id']])) {
                $_SESSION['username'] = $username;
                $user['username'] = $username;
                $user['email'] = $email;
                $success_message = "Профиль успешно обновлен!";
            } else {
                $error_message = "Ошибка при обновлении профиля";
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }

    if (isset($_POST['change_password'])) {
        // Изменение пароля
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Неверный текущий пароль";
        }

        if (strlen($new_password) < 6) {
            $errors[] = "Новый пароль должен содержать минимум 6 символов";
        }

        if ($new_password !== $confirm_password) {
            $errors[] = "Пароли не совпадают";
        }

        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                $success_message = "Пароль успешно изменен!";
            } else {
                $error_message = "Ошибка при изменении пароля";
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
}

// Получаем статистику пользователя
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$orders_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $pdo->prepare("SELECT SUM(total_price) as total FROM orders WHERE user_id = ? AND status != 'cancelled'");
$stmt->execute([$_SESSION['user_id']]);
$total_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - Яшин стаффчик</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .profile-header h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-box .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-box .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .profile-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .profile-section h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #333;
            font-size: 1.5rem;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main>
        <div class="profile-container">
            <div class="profile-header">
                <h1>Добро пожаловать, <?= htmlspecialchars($user['username']) ?>!</h1>
                <p>Email: <?= htmlspecialchars($user['email']) ?></p>
                <?php if ($user['is_admin']): ?>
                    <p style="margin-top: 10px;">
                        <span style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px;">
                            🛡️ Администратор
                        </span>
                    </p>
                <?php endif; ?>
            </div>

            <div class="profile-stats">
                <div class="stat-box">
                    <div class="stat-value"><?= $orders_count ?></div>
                    <div class="stat-label">Заказов сделано</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= number_format($total_spent, 0, ',', ' ') ?> ₽</div>
                    <div class="stat-label">Потрачено</div>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <div class="profile-content">
                <!-- Основная информация -->
                <div class="profile-section">
                    <h2>Основная информация</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Имя пользователя</label>
                            <input type="text" id="username" name="username"
                                   value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email"
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <button type="submit" name="update_profile" class="btn-submit">
                            Сохранить изменения
                        </button>
                    </form>
                </div>

                <!-- Изменение пароля -->
                <div class="profile-section">
                    <h2>Изменить пароль</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="current_password">Текущий пароль</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Новый пароль</label>
                            <input type="password" id="new_password" name="new_password"
                                   minlength="6" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Подтвердите пароль</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   minlength="6" required>
                        </div>

                        <button type="submit" name="change_password" class="btn-submit">
                            Изменить пароль
                        </button>
                    </form>
                </div>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <a href="orders.php" class="btn btn--primary" style="display: inline-block; padding: 12px 30px; text-decoration: none;">
                    Посмотреть мои заказы
                </a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php' ?>
</body>
</html>
