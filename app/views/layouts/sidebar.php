<?php 
$rol = $_SESSION['user']['rol'] ?? 'usuario';
$nombre = $_SESSION['user']['nombre'] ?? 'Usuario';

// Imagen por defecto si no existe avatar
$avatar = $_SESSION['user']['avatar'] ?? null;
$avatar = (!empty($avatar)) ? $avatar : BASE_URL . "/assets/default_user.png";
?>

<nav id="sidebar" class="sidebar expanded">

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

        <li class="submenu">
            <button class="submenu-btn">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
                <i class="bi bi-chevron-down arrow"></i>
            </button>

            <ul class="submenu-items">
                <li><a href="#">Panel General</a></li>
                <li><a href="#">Reportes</a></li>
            </ul>
        </li>

        <?php if ($rol === 'admin'): ?>
        <li>
            <a href="<?= BASE_URL ?>/?url=usuarios/index">
                <i class="bi bi-people"></i>
                <span>Gestión de Usuarios</span>
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="<?= BASE_URL ?>/?url=inventario/index">
                <i class="bi bi-box-seam"></i>
                <span>Inventario</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/?url=auth/logout" class="logout">
                <i class="bi bi-door-open"></i>
                <span>Cerrar sesión</span>
            </a>
        </li>

    </ul>

    <button id="toggleSidebar" class="toggle-btn">
        <i class="bi bi-chevron-double-left"></i>
    </button>

</nav>
