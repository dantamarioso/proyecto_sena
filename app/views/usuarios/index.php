<div class="row">
    <div class="col-12">
        <h3 class="mb-4">Gestión de usuarios</h3>

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Creado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['id']) ?></td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['correo']) ?></td>
                            <td><?= htmlspecialchars($u['nombre_usuario']) ?></td>
                            <td>
                                <?php if ((int)$u['estado'] === 1): ?>
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
                                <?php if ((int)$u['estado'] === 1): ?>
                                    <form method="post" action="<?= BASE_URL ?>/?url=usuarios/bloquear"
                                          class="d-inline">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-warning" type="submit"
                                                onclick="return confirm('¿Bloquear este usuario?');">
                                            <i class="bi bi-ban"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="<?= BASE_URL ?>/?url=usuarios/desbloquear"
                                          class="d-inline">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-success" type="submit"
                                                onclick="return confirm('¿Desbloquear este usuario?');">
                                            <i class="bi bi-unlock"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Eliminar -->
                                <form method="post" action="<?= BASE_URL ?>/?url=usuarios/eliminar"
                                      class="d-inline">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-danger" type="submit"
                                            onclick="return confirm('¿Eliminar definitivamente este usuario?');">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No hay usuarios registrados todavía.
                            </td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
