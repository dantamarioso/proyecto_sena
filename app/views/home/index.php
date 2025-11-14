<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/?url=auth/login");
    exit;
}

require_once __DIR__ . "/../../models/User.php";
$userModel = new User();
$currentId = $_SESSION['user']['id'] ?? null;

// Carga inicial (page 1, sin filtros)
if ($currentId) {
    $usuarios = $userModel->allExceptId($currentId);
} else {
    $usuarios = $userModel->all();
}
?>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>

<div class="row justify-content-center">
    <div class="col-md-11">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Panel de Usuarios</h3>
            <a href="<?= BASE_URL ?>/?url=usuarios/crear" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg"></i> Nuevo usuario
            </a>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="card mb-3">
            <div class="card-body row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" id="busqueda" class="form-control" placeholder="Nombre, correo o usuario">
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

        <!-- Tabla -->
        <div class="card">
            <div class="card-body table-responsive">

                <table class="table table-striped align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Usuario</th>
                        <th>Celular</th>
                        <th>Cargo</th>
                        <th>Foto</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Creado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="usuarios-body">
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['correo']) ?></td>
                            <td><?= htmlspecialchars($u['nombre_usuario']) ?></td>
                            <td><?= $u['celular'] ? htmlspecialchars($u['celular']) : '<span class="text-muted">N/A</span>' ?></td>
                            <td><?= $u['cargo'] ? htmlspecialchars($u['cargo']) : '<span class="text-muted">Sin cargo</span>' ?></td>
                            <td>
                                <?php if ($u['foto']): ?>
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($u['foto']) ?>"
                                         width="40" height="40" class="rounded-circle" style="object-fit:cover;">
                                <?php else: ?>
                                    <span class="text-muted">Sin foto</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['rol'] ?? 'usuario') ?></td>
                            <td>
                                <?php if ($u['estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Bloqueado</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/?url=usuarios/editar&id=<?= $u['id'] ?>"
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <?php if ($u['estado'] == 1): ?>
                                    <form class="d-inline" method="post"
                                          action="<?= BASE_URL ?>/?url=usuarios/bloquear">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-warning" type="submit">
                                            <i class="bi bi-ban"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form class="d-inline" method="post"
                                          action="<?= BASE_URL ?>/?url=usuarios/desbloquear">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-success" type="submit">
                                            <i class="bi bi-unlock"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form class="d-inline" method="post"
                                      action="<?= BASE_URL ?>/?url=usuarios/eliminar">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-danger" type="submit"
                                            onclick="return confirm('¿Eliminar usuario definitivamente?');">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small id="usuarios-info" class="text-muted"></small>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-1" id="btn-prev">&laquo;</button>
                        <span id="pagina-actual" class="me-1">1</span>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-next">&raquo;</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
