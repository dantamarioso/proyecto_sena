<?php 
$rol = $_SESSION['user']['rol'] ?? 'usuario';
$nombre = $_SESSION['user']['nombre'] ?? 'Usuario';

// Imagen por defecto si no existe avatar
$avatar = $_SESSION['user']['avatar'] ?? null;
$avatar = (!empty($avatar)) ? $avatar : BASE_URL . "/img/default_user.png";
?>

<nav id="sidebar" class="sidebar">

    <!-- Header -->
    <div class="sidebar-header">
        <img src="<?= $avatar ?>" class="sidebar-avatar">

        <div class="sidebar-user-info">
            <span class="sidebar-welcome">Bienvenido</span>
            <span class="sidebar-title"><?= htmlspecialchars($nombre) ?></span>
        </div>
    </div>

    <div class="sidebar-search">
        <input type="text" placeholder="Buscar..." class="search-input">
    </div>

    <ul class="sidebar-nav">

        <li>
            <a href="<?= BASE_URL ?>/?url=home/index">
                <i class="bi bi-house"></i>
                <span>Inicio</span>
            </a>
        </li>


        <?php if ($rol === 'admin'): ?>
        <li>
            <a href="<?= BASE_URL ?>/?url=usuarios/index">
                <i class="bi bi-people"></i>
                <span>Gestión de Usuarios</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/?url=audit/historial">
                <i class="bi bi-clock-history"></i>
                <span>Historial de Cambios</span>
            </a>
        </li>
        <?php endif; ?>



        <li>
            <a href="<?= BASE_URL ?>/?url=auth/logout" class="logout">
                <i class="bi bi-door-open"></i>
                <span>Cerrar sesión</span>
            </a>
        </li>

    </ul>

</nav>
