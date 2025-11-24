<?php
session_start();
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Ошибка 404 — Страница не найдена</title>
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

  <?php include 'includes/header.php' ?>

    <main>
    <section class="hero">
      <div class="container">
        <div class="error-404">
          <h1>404</h1>
          <p>Похоже, такой страницы не существует</p>
          <a href="./index.php" class="btn btn--primary">
            Вернуться на главную
          </a>
        </div>
      </div>
    </section>
  </main>

</body>
</html>