<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Sistema Inventario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- BASE_URL Global - Forzar HTTPS en ngrok -->
    <script>
        // Construir BASE_URL SIEMPRE usando el protocolo actual del navegador
        let currentProtocol = window.location.protocol; // https: o http:
        const currentHost = window.location.host; // incluyendo puerto si aplica
        
        // Para ngrok: asegurar que sea siempre https
        if (currentHost.includes('ngrok') && currentProtocol === 'http:') {
            console.warn('ADVERTENCIA: ngrok debería usar HTTPS. Redirigiendo...');
            currentProtocol = 'https:';
            window.location.replace('https://' + currentHost + window.location.pathname + window.location.search);
        }
        
        // Construir URL base
        window.BASE_URL = currentProtocol + '//' + currentHost + '/proyecto_sena/public';
    </script>

    <!-- Google Fonts - Work Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/sidebar.css">
    
    <!-- Sidebar Toggle Button CSS -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/sidebar-toggle.css">

    <!-- Header y Footer CSS -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/layout.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS Formularios -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/formularios.css">

    <!-- CSS Tablas -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/tablas.css">

    <!-- CSS Modales y Notificaciones -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/modales.css">

    <!-- CSS Utilidades y Componentes -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/utilidades.css">

    <!-- CSS global -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/style.css">

    <!-- CSS Gestión de Usuarios -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/usuarios_gestion.css">

    <!-- CSS Perfil de Usuario -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/perfil_mejorado.css">

    <!-- CSS Auditoría -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/audit_mejorado.css">

    <!-- CSS Autenticación -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/auth_mejorado.css">

    <!-- CSS específico para login -->
    <?php if (isset($isLoginPage) && $isLoginPage === true): ?>
        <link rel="stylesheet" href="/proyecto_sena/public/css/login.css">
    <?php endif; ?>

    <!-- CSS específico para register -->
    <?php if (isset($isRegisterPage) && $isRegisterPage === true): ?>
        <link rel="stylesheet" href="/proyecto_sena/public/css/register.css">
    <?php endif; ?>

    <!-- CSS específico para recuperación de contraseña -->
    <?php if (isset($isRecoveryPage) && $isRecoveryPage === true): ?>
        <link rel="stylesheet" href="/proyecto_sena/public/css/recovery.css">
    <?php endif; ?>

    <!-- CSS responsive -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/usuarios_responsive.css">

    <!-- CSS formularios de usuarios -->
    <link rel="stylesheet" href="/proyecto_sena/public/css/usuarios_form.css">

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
            border-left: 4px solid #39A900;
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
