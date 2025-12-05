<?php
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/auth/login');
    exit;
}

require_once __DIR__ . '/../../models/User.php';
$userModel = new User();
$currentId = $_SESSION['user']['id'] ?? null;
$rolActual = $_SESSION['user']['rol'] ?? 'usuario';

// Carga inicial solo por compatibilidad (AJAX se encargará después)
if ($currentId) {
    $usuarios = $userModel->allExceptId($currentId);
} else {
    $usuarios = $userModel->all();
}
?>

<div class="row justify-content-center">
    <div class="col-md-11">

        <!-- HEADER PANEL -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Panel de Usuarios</h3>

            <?php if ($rolActual === 'admin') : ?>
                <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo usuario
                </a>
            <?php endif; ?>
        </div>

        <!-- FILTROS / BÚSQUEDA -->
        <div class="card mb-3">
            <div class="card-body row g-2 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" id="busqueda" class="form-control"
                           placeholder="Nombre, correo o usuario">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select id="filtro-estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Bloqueados</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Rol</label>
                    <select id="filtro-rol" class="form-select">
                        <option value="">Todos</option>
                        <option value="admin">Admin</option>
                        <option value="usuario">Usuario</option>
                        <option value="invitado">Invitado</option>
                    </select>
                </div>

                <div class="col-md-2 text-end">
                    <button class="btn btn-outline-secondary w-100" id="btn-limpiar">
                        Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- TABLA -->
        <div class="card">
            <div class="card-body table-responsive">

                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Foto</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Usuario</th>
                            <th>Celular</th>
                            <th>Cargo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody id="usuarios-body">
                        <!-- Contenido generado por AJAX (usuarios.js) -->
                        <?php if (empty($usuarios)) : ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- PAGINACIÓN -->
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small id="usuarios-info" class="text-muted">Total: 0 usuario(s)</small>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-1" id="btn-prev">
                            &laquo;
                        </button>
                        <span id="pagina-actual" class="me-1">1 / 1</span>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-next">
                            &raquo;
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- CONTENEDOR DE TOASTS -->
        <div class="toast-container" id="toast-container"></div>

    </div>
</div>
