<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/?url=auth/login");
    exit;
}
?>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>

<style>
    tbody tr:only-child td {
        display: table-cell !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-12">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
            <h3 class="mb-0">Panel de Usuarios</h3>
            <a href="<?= BASE_URL ?>/?url=usuarios/crear" class="btn btn-success btn-sm w-100 w-sm-auto">
                <i class="bi bi-plus-lg"></i> Nuevo usuario
            </a>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" id="busqueda" class="form-control" placeholder="Nombre, correo o usuario">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label">Estado</label>
                        <select id="filtro-estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Bloqueados</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label">Rol</label>
                        <select id="filtro-rol" class="form-select">
                            <option value="">Todos</option>
                            <option value="admin">Admin</option>
                            <option value="usuario">Usuario</option>
                            <option value="invitado">Invitado</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button class="btn btn-outline-secondary w-100" id="btn-limpiar">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Responsiva -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th class="d-none d-md-table-cell">Foto</th>
                        <th>Nombre</th>
                        <th class="d-none d-md-table-cell">Correo</th>
                        <th class="d-none d-lg-table-cell">Usuario</th>
                        <th class="d-none d-xl-table-cell">Celular</th>
                        <th class="d-none d-xl-table-cell">Cargo</th>
                        <th class="d-none d-lg-table-cell">Rol</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </thead>
                    <tbody id="usuarios-body">
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td class="d-none d-md-table-cell">
                                <?php if ($u['foto']): ?>
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($u['foto']) ?>"
                                         width="40" height="40" class="rounded-circle" style="object-fit:cover;">
                                <?php else: ?>
                                    <img src="<?= BASE_URL ?>/img/default_user.png"
                                         width="40" height="40" class="rounded-circle" style="object-fit:cover;" alt="Usuario sin foto">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($u['correo']) ?></td>
                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars($u['nombre_usuario']) ?></td>
                            <td class="d-none d-xl-table-cell"><?= $u['celular'] ? htmlspecialchars($u['celular']) : '<span class="text-muted">N/A</span>' ?></td>
                            <td class="d-none d-xl-table-cell"><?= $u['cargo'] ? htmlspecialchars($u['cargo']) : '<span class="text-muted">Sin cargo</span>' ?></td>
                            <td class="d-none d-lg-table-cell"><span class="badge bg-info"><?= htmlspecialchars($u['rol'] ?? 'usuario') ?></span></td>
                            <td>
                                <?php if ($u['estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Bloqueado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/?url=usuarios/editar&id=<?= $u['id'] ?>"
                                       class="btn btn-primary btn-sm" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <?php if ($u['estado'] == 1): ?>
                                        <form class="d-inline" method="post"
                                              action="<?= BASE_URL ?>/?url=usuarios/bloquear">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button class="btn btn-warning btn-sm" type="submit" title="Bloquear">
                                                <i class="bi bi-ban"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form class="d-inline" method="post"
                                              action="<?= BASE_URL ?>/?url=usuarios/desbloquear">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button class="btn btn-success btn-sm" type="submit" title="Desbloquear">
                                                <i class="bi bi-unlock"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $u['id'] ?>" data-nombre="<?= htmlspecialchars($u['nombre']) ?>" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="card-footer bg-light">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                    <small id="usuarios-info" class="text-muted"></small>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" id="btn-prev">&laquo;</button>
                        <span id="pagina-actual" class="mx-2">1</span>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-next">&raquo;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
