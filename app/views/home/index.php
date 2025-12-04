<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/auth/login");
    exit;
}

// Determinar mensaje de bienvenida según el rol
$rol = $usuario['rol'] ?? 'usuario';
$nombre = $usuario['nombre'] ?? 'Usuario';

// Descripciones según el rol
$descripciones = [
    'admin' => 'Tienes acceso completo al sistema para gestionar usuarios, materiales, auditoría y todas las configuraciones. Puedes crear, editar, eliminar y visualizar toda la información del inventario.',
    'dinamizador' => 'Como dinamizador, puedes gestionar materiales, consultar el inventario y coordinar actividades dentro de tu línea y nodo asignado. Tienes permisos para crear y editar información relevante a tu área.',
    'usuario' => 'Bienvenido al sistema de inventario SENA. Puedes consultar materiales, ver el estado del inventario y acceder a la información de tu línea y nodo asignado.',
    'invitado' => 'Como invitado, tienes acceso de solo lectura al sistema. Puedes consultar información sobre materiales e inventario, pero no realizar modificaciones.'
];

$descripcion = $descripciones[$rol] ?? $descripciones['usuario'];

// Traducir nombres de roles
$rolesNombres = [
    'admin' => 'Administrador',
    'dinamizador' => 'Dinamizador',
    'usuario' => 'Usuario',
    'invitado' => 'Invitado'
];

$rolNombre = $rolesNombres[$rol] ?? 'Usuario';
?>

<div class="container-bienvenida">
    <div class="bienvenida-header">
        <h1>¡Bienvenido, <?php echo htmlspecialchars($nombre); ?>! 👋</h1>
        <div class="info-usuario">
            <div class="info-item">
                <span class="info-label">Rol:</span>
                <span class="info-value rol-<?php echo htmlspecialchars($rol); ?>">
                    <?php echo htmlspecialchars($rolNombre); ?>
                </span>
            </div>
            <?php if ($rol !== 'admin' && !empty($lineaNombre)): ?>
                <div class="info-item">
                    <span class="info-label">Línea:</span>
                    <span class="info-value"><?php echo htmlspecialchars($lineaNombre); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($rol !== 'admin' && !empty($nodoNombre)): ?>
                <div class="info-item">
                    <span class="info-label">Nodo:</span>
                    <span class="info-value"><?php echo htmlspecialchars($nodoNombre); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bienvenida-descripcion">
        <h2>Acerca del Sistema</h2>
        <p><?php echo htmlspecialchars($descripcion); ?></p>
    </div>

    <div class="accesos-rapidos">
        <h3>Accesos Rápidos</h3>
        <div class="accesos-grid">
            <?php if ($rol === 'admin' || $rol === 'dinamizador'): ?>
                <a href="<?php echo BASE_URL; ?>/materiales/index" class="acceso-card">
                    <span class="acceso-icon">📦</span>
                    <span class="acceso-titulo">Gestión de Materiales</span>
                </a>
            <?php endif; ?>
            
            <?php if ($rol === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>/usuarios/gestionDeUsuarios" class="acceso-card">
                    <span class="acceso-icon">👥</span>
                    <span class="acceso-titulo">Gestión de Usuarios</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/audit/historial" class="acceso-card">
                    <span class="acceso-icon">📋</span>
                    <span class="acceso-titulo">Auditoría</span>
                </a>
            <?php endif; ?>
            
            <a href="<?php echo BASE_URL; ?>/perfil/ver" class="acceso-card">
                <span class="acceso-icon">👤</span>
                <span class="acceso-titulo">Mi Perfil</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>/materiales/index" class="acceso-card">
                <span class="acceso-icon">🔍</span>
                <span class="acceso-titulo">Consultar Inventario</span>
            </a>
        </div>
    </div>
</div>
