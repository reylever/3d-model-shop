<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Яшин стаффчик - 3д модели - Регистрация</title>
    
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <?php include 'includes/header.php' ?>

    <main class="register-page">
        <section class="register-container">
            <h1 class="register-title">Создать аккаунт</h1>
            <form class="register-form" method="POST" action="auth/register_handler.php" autocomplete="off">
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" required placeholder="Введите имя пользователя">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Введите email">
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required placeholder="Минимум 8 символов">
                </div>

                <div class="form-group">
                    <label for="password_confirm">Подтвердите пароль</label>
                    <input type="password" id="password_confirm" name="password_confirm" required placeholder="Повторите пароль">
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">Я согласен с <a href="#">условиями использования</a></label>
                </div>

                <button type="submit" class="default_button">Зарегистрироваться</button>

                <p class="register-login">
                    Уже есть аккаунт? <a href="login.php">Войти</a>
                </p>
            </form>
        </section>
    </main>

    <?php include 'includes/footer.php' ?>

    <?php
    if (isset($_GET['error'])) {
        echo '<script>alert("Ошибка: ' . htmlspecialchars($_GET['error']) . '");</script>';
    }
    if (isset($_GET['success'])) {
        echo '<script>alert("Регистрация успешна! Теперь вы можете войти.");</script>';
    }
    ?>
</body>
</html>