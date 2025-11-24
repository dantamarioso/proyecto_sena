<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Sistema Inventario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- BASE_URL Global -->
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
    
    <!-- Sidebar Toggle Button CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar-toggle.css">

    <!-- Header y Footer CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/layout.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

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

    <!-- CSS específico para login -->
    <?php if (isset($isLoginPage) && $isLoginPage === true): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/login.css">
    <?php endif; ?>

    <!-- CSS específico para register -->
    <?php if (isset($isRegisterPage) && $isRegisterPage === true): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/register.css">
    <?php endif; ?>

    <!-- CSS específico para recuperación de contraseña -->
    <?php if (isset($isRecoveryPage) && $isRecoveryPage === true): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/recovery.css">
    <?php endif; ?>

    <!-- CSS responsive -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/usuarios_responsive.css">

    <!-- CSS formularios de usuarios -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/usuarios_form.css">

    <!-- Toast/Notificación Emergente -->
    <style>
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 9999;
            animation: slideInRight 0.3s ease-out;
            font-weight: 500;
            font-size: 14px;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .notification-toast.show {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notification-toast.error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc3545;
        }

        .notification-toast.success {
            background-color: #dcfce7;
            color: #166534;
            border-left: 4px solid #198754;
        }

        .notification-toast.warning {
            background-color: #fef3c7;
            color: #92400e;
            border-left: 4px solid #ffc107;
        }

        .notification-toast i {
            font-size: 18px;
        }

        @media (max-width: 576px) {
            .notification-toast {
                min-width: calc(100% - 40px);
                right: 20px;
                left: 20px;
            }
        }
    </style>

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
