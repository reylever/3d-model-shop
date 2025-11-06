<link rel="stylesheet" href="assets/css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/logo.png" alt="Яшин стаффчик" height="40" class="me-2">
                <span>Яшин Стаффчик</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Главная</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-semibold" href="#" id="catalogDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Каталог
                        </a>
                        <ul class="dropdown-menu shadow-lg border-0 rounded-3 p-2 animate__animated animate__fadeIn" aria-labelledby="catalogDropdown" style="min-width: 220px;">
                            <li><h6 class="dropdown-header text-secondary fs-6 fw-bold border-bottom pb-2 mb-2">Категории 3D моделей</h6></li>
                            <li><a class="dropdown-item fs-6 py-2 rounded" href="#"><i class="bi bi-person-fill me-2 text-primary"></i>Персонажи</a></li>
                            <li><a class="dropdown-item fs-6 py-2 rounded" href="#"><i class="bi bi-bullseye me-2 text-danger"></i>Оружие</a></li>
                            <li><a class="dropdown-item fs-6 py-2 rounded" href="#"><i class="bi bi-house-door me-2 text-success"></i>Мебель</a></li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li><a class="dropdown-item text-primary fw-bold py-2" href="#"><i class="bi bi-grid me-2"></i>Все категории</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Корзина</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Вход</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>