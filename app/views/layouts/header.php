<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Sistema Inventario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">

    <!-- Header y Footer CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/layout.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS Mejoras (responsive & diseño) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/mejoras.css">

    <!-- CSS Formularios -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/formularios.css">

    <!-- CSS Tablas -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/tablas.css">

    <!-- CSS Modales y Notificaciones -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/modales.css">

    <!-- CSS Utilidades y Componentes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/utilidades.css">

    <!-- CSS global -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">

    <!-- CSS Gestión de Usuarios -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/usuarios_gestion.css">

    <!-- CSS Perfil de Usuario -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/perfil_mejorado.css">

    <!-- CSS Auditoría -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/audit_mejorado.css">

    <!-- CSS Autenticación -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth_mejorado.css">

    <!-- CSS responsive -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/usuarios_responsive.css">

    <!-- CSS formularios de usuarios -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/usuarios_form.css">

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

$isLogin     = isset($isLoginPage) && $isLoginPage === true;
$isRegister  = isset($isRegisterPage) && $isRegisterPage === true;
?>

<?php if (!$isLogin && !$isRegister): ?>

    <!-- SIDEBAR -->
    <?php include __DIR__ . "/sidebar.php"; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-wrapper">
        <div class="main-content">
<?php else: ?>
    <!-- LOGIN O REGISTER -->
    <div class="container py-4">
<?php endif; ?>
