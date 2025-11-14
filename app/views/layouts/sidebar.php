<?php $rol = $_SESSION['user']['rol'] ?? 'usuario'; ?>

<ul class="nav flex-column">

    <li class="nav-item">
        <a href="<?= BASE_URL ?>/?url=home/index" class="nav-link">
            <i class="bi bi-house"></i> Inicio
        </a>
    </li>

    <?php if ($rol === 'admin'): ?>
    <li class="nav-item">
        <a href="<?= BASE_URL ?>/?url=usuarios/index" class="nav-link">
            <i class="bi bi-people"></i> Gestión de usuarios
        </a>
    </li>
    <?php endif; ?>

    <?php if ($rol === 'admin' || $rol === 'usuario'): ?>
    <li class="nav-item">
        <a href="<?= BASE_URL ?>/?url=inventario/index" class="nav-link">
            <i class="bi bi-box-seam"></i> Inventario
        </a>
    </li>
    <?php endif; ?>

    <!-- TODOS -->
    <li class="nav-item">
        <a href="<?= BASE_URL ?>/?url=auth/logout" class="nav-link text-danger">
            <i class="bi bi-door-open"></i> Cerrar sesión
        </a>
    </li>

</ul>
