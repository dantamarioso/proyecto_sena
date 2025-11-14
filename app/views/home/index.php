<?php
// Verificar que existe un usuario logueado
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/?url=auth/login");
    exit;
}

// Cargar el modelo para obtener usuarios
require_once __DIR__ . "/../../models/User.php";
$userModel = new User();
$currentId = $_SESSION['user']['id'] ?? null;

if ($currentId) {
    $usuarios = $userModel->allExceptId($currentId);
} else {
    $usuarios = $userModel->all();
}
?>

<div class="row justify-content-center">
    <div class="col-md-10">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Panel de Usuarios</h3>
            <a href="<?= BASE_URL ?>/?url=usuarios/crear" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg"></i> Nuevo usuario
            </a>
        </div>

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
                            <th>Estado</th>
                            <th>Creado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['nombre']) ?></td>
                                <td><?= htmlspecialchars($u['correo']) ?></td>
                                <td><?= htmlspecialchars($u['nombre_usuario']) ?></td>

                                <!-- Celular -->
                                <td>
                                    <?= $u['celular'] 
                                        ? htmlspecialchars($u['celular']) 
                                        : '<span class="text-muted">N/A</span>' ?>
                                </td>

                                <!-- Cargo -->
                                <td>
                                    <?= $u['cargo'] 
                                        ? htmlspecialchars($u['cargo']) 
                                        : '<span class="text-muted">Sin cargo</span>' ?>
                                </td>

                                <!-- Foto -->
                                <td>
                                    <?php if (!empty($u['foto'])): ?>
                                        <img src="<?= BASE_URL . '/' . htmlspecialchars($u['foto']) ?>"
                                             width="40" height="40"
                                             class="rounded-circle"
                                             style="object-fit:cover;">
                                    <?php else: ?>
                                        <span class="text-muted">Sin foto</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Estado -->
                                <td>
                                    <?php if ($u['estado'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Bloqueado</span>
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>

                                <td class="text-end">

                                    <!-- Editar -->
                                    <a href="<?= BASE_URL ?>/?url=usuarios/editar&id=<?= $u['id'] ?>"
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <!-- Bloquear / Desbloquear -->
                                    <?php if ($u['estado'] == 1): ?>
                                        <form class="d-inline" method="post"
                                              action="<?= BASE_URL ?>/?url=usuarios/bloquear">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button class="btn btn-sm btn-warning" type="submit"
                                                    onclick="return confirm('¿Bloquear este usuario?');">
                                                <i class="bi bi-ban"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form class="d-inline" method="post"
                                              action="<?= BASE_URL ?>/?url=usuarios/desbloquear">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button class="btn btn-sm btn-success" type="submit"
                                                    onclick="return confirm('¿Desbloquear este usuario?');">
                                                <i class="bi bi-unlock"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Eliminar -->
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
                                <td colspan="10" class="text-center text-muted">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
