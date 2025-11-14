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
    // Si la vista define $isLoginPage o $isRegisterPage → ocultamos la navbar
    $isLogin  = isset($isLoginPage) && $isLoginPage === true;
    $isRegister = isset($isRegisterPage) && $isRegisterPage === true;

    if (!$isLogin && !$isRegister):
    ?>
        <!-- NAVBAR -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">

                <a class="navbar-brand" href="<?= BASE_URL ?>/?url=home/index">
                    Inventario
                </a>

                <div class="d-flex">
                    <?php if (isset($_SESSION['user'])): ?>
                        <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/?url=auth/logout">
                            Salir
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </nav>
    <?php endif; ?>

    <?php if (isset($_SESSION['user'])): ?>
        <a class="btn btn-outline-light btn-sm me-2" href="<?= BASE_URL ?>/?url=usuarios/index">
            Usuarios
        </a>
        <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/?url=auth/logout">Salir</a>
    <?php endif; ?>
    <?php if (!empty($pageStyles)) {
        foreach ($pageStyles as $css) {
            echo '<link rel="stylesheet" href="' . BASE_URL . '/public/css/' . $css . '.css">';
        }
    } ?>

    <?php if (!empty($pageScripts)) {
        foreach ($pageScripts as $js) {
            echo '<script src="' . BASE_URL . '/public/js/' . $js . '.js"></script>';
        }
    } ?>



    <!-- Contenido principal -->
    <div class="container py-4">