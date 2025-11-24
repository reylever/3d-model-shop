<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Яшин стаффчик - 3д модели - Вход</title>
    
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <?php include 'includes/header.php' ?>


    <main class="login-page">
        <section class="login-container">
            <h1 class="login-title">Вход в аккаунт</h1>
            <form class="login-form" method="POST" action="auth/login_handler.php" autocomplete="off">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Введите email">
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required placeholder="Введите пароль">
                </div>

                <button type="submit" class="btn btn--primary btn--block">Войти</button>

                <p class="login-register">
                    Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
                </p>
            </form>
        </section>
    </main>

    <?php
    if (isset($_GET['error'])) {
        echo '<script>alert("Ошибка: ' . htmlspecialchars($_GET['error']) . '");</script>';
    }
    ?>
</body>
</html>