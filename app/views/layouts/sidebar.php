<?php 
$rol = $_SESSION['user']['rol'] ?? 'usuario';
$nombre = $_SESSION['user']['nombre'] ?? 'Usuario';

// Imagen por defecto si no existe foto
$avatar = $_SESSION['user']['foto'] ?? null;
$avatar = (!empty($avatar)) ? BASE_URL . '/' . $avatar : BASE_URL . "/img/default_user.png";
?>

<nav id="sidebar" class="sidebar">

    <!-- Header -->
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>/?url=perfil/ver" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; width: 100%;">
            <img src="<?= $avatar ?>" class="sidebar-avatar" title="Ver perfil">

            <div class="sidebar-user-info">
                <span class="sidebar-welcome">Bienvenido</span>
                <span class="sidebar-title"><?= htmlspecialchars($nombre) ?></span>
            </div>
        </a>
    </div>



    <ul class="sidebar-nav">

        <li>
            <a href="<?= BASE_URL ?>/?url=home/index">
                <i class="bi bi-house"></i>
                <span>Inicio</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/?url=perfil/ver">
                <i class="bi bi-person-circle"></i>
                <span>Mi perfil</span>
            </a>
        </li>

        <!-- Inventario -->
        <li>
            <a href="<?= BASE_URL ?>/?url=materiales/index">
                <i class="bi bi-box2-heart"></i>
                <span>Gestión de Inventario</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/?url=materiales/historialInventario">
                <i class="bi bi-clock-history"></i>
                <span>Historial de Inventario</span>
            </a>
        </li>

        <?php if ($rol === 'admin'): ?>
        <li>
            <a href="<?= BASE_URL ?>/?url=usuarios/gestionDeUsuarios">
                <i class="bi bi-people"></i>
                <span>Gestión de Usuarios</span>
            </a>
        </li>

        <li>
            <a href="<?= BASE_URL ?>/?url=audit/historial">
                <i class="bi bi-clock-history"></i>
                <span>Historial de Usuarios</span>
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
