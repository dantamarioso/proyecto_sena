<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Sistema Inventario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS global -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">

    <!-- CSS responsive -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/usuarios_responsive.css">

    <!-- CSS por vista -->
    <?php if (!empty($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $css): ?>
            <link rel="stylesheet" href="<?= BASE_URL ?>/css/<?= htmlspecialchars($css) ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <?php
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>

    <?php
    $isLogin     = isset($isLoginPage) && $isLoginPage === true;
    $isRegister  = isset($isRegisterPage) && $isRegisterPage === true;

    if (!$isLogin && !$isRegister):
    ?>
        <!-- NAVBAR -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <?php if (isset($_SESSION['user']) && ($_SESSION['user']['rol'] ?? 'usuario') === 'admin'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-menu-button-wide"></i> Menú
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/?url=usuarios/gestionDeUsuarios">
                                            <i class="bi bi-people"></i> Gestión de Usuarios
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <a class="navbar-brand mx-auto mx-lg-0" href="<?= BASE_URL ?>/?url=home/index">
                    <i class="bi bi-house"></i>
                </a>

                <div class="d-flex">
                    <?php if (isset($_SESSION['user'])): ?>
                        <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/?url=auth/logout">
                            <i class="bi bi-box-arrow-right"></i> Salir
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Enlaces directos opcionales (se eliminó el enlace flotante 'Usuarios') -->

    <!-- Contenido principal -->
    <div class="container py-4">
