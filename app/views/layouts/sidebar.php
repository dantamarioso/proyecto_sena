<?php
$rol = $_SESSION['user']['rol'] ?? 'usuario';
$nombre = $_SESSION['user']['nombre'] ?? 'Usuario';

// Imagen por defecto si no existe foto
$avatar = $_SESSION['user']['foto'] ?? null;
$avatar = (!empty($avatar)) ? BASE_URL . '/' . $avatar : BASE_URL . '/img/default_user.png';
?>

<nav id="sidebar" class="sidebar">

    <!-- Header -->
    <div class="sidebar-header">
        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; width: 100%;">
            <a href="<?= BASE_URL ?>/perfil/ver" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center;">
                <img src="<?= $avatar ?>" class="sidebar-avatar" title="Ver perfil">
                <div class="sidebar-user-info">
                    <span class="sidebar-welcome">Bienvenido</span>
                    <span class="sidebar-title"><?= htmlspecialchars($nombre) ?></span>
                </div>
            </a>
            
            <?php if (isset($_SESSION['user'])) : ?>
                <!-- Icono de notificaciones a la derecha -->
                <div id="notificationsBtn" class="notification-bell-icon" title="Alertas">
                    <i class="bi bi-bell-fill"></i>
                    <span id="notificationBadge" class="notification-badge-icon" style="display: none;">0</span>
                </div>
            <?php endif; ?>
        </div>

    <ul class="sidebar-nav">

        <li>
            <a href="<?= BASE_URL ?>/home/index">
                <i class="bi bi-house"></i>
                <span>Inicio</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/perfil/ver">
                <i class="bi bi-person-circle"></i>
                <span>Mi perfil</span>
            </a>
        </li>

        <!-- Inventario -->
        <li>
            <a href="<?= BASE_URL ?>/materiales/index">
                <i class="bi bi-box2-heart"></i>
                <span>Gestión de Inventario</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/materialeshistorial/index">
                <i class="bi bi-clock-history"></i>
                <span>Historial de Inventario</span>
            </a>
        </li>

        <?php if ($rol === 'admin') : ?>
        <li>
            <a href="<?= BASE_URL ?>/usuarios/gestionDeUsuarios">
                <i class="bi bi-people"></i>
                <span>Gestión de Usuarios</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/audit/historial">
                <i class="bi bi-clock-history"></i>
                <span>Historial de Usuarios</span>
            </a>
        </li>
        <?php endif; ?>



        <li>
            <a href="<?= BASE_URL ?>/auth/logout" class="logout">
                <i class="bi bi-door-open"></i>
                <span>Cerrar sesión</span>
            </a>
        </li>

    </ul>

</nav>

<?php if (isset($_SESSION['user'])) : ?>
<!-- Modal de notificaciones - FUERA del sidebar -->
<div id="notificationsModal" class="notifications-modal" style="display: none;">
    <div class="notifications-modal-overlay"></div>
    <div class="notifications-modal-content">
        <div class="notifications-modal-header">
            <div>
                <h5><i class="bi bi-bell-fill me-2"></i>Alertas del sistema</h5>
                <p class="mb-0" id="notificationCount">0 alertas</p>
            </div>
            <button type="button" class="notifications-close-btn" id="closeNotificationsModal">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div id="notificationsList" class="notifications-modal-body">
            <div class="notification-empty">
                <i class="bi bi-check-circle"></i>
                <p class="mb-0">No hay notificaciones</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
