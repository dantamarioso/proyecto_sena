<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="mb-3">Editar usuario</h3>

                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre" class="form-control"
                               value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                    </div>

                    <!-- Correo -->
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control"
                               value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                    </div>

                    <!-- Usuario -->
                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario</label>
                        <input type="text" name="nombre_usuario" class="form-control"
                               value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" required>
                    </div>

                    <!-- Celular -->
                    <div class="mb-3">
                        <label class="form-label">Celular</label>
                        <input type="text" name="celular" class="form-control"
                               value="<?= htmlspecialchars($usuario['celular'] ?? '') ?>"
                               placeholder="Ej: +57 123 456 7890">
                    </div>

                    <!-- Cargo -->
                    <div class="mb-3">
                        <label class="form-label">Cargo</label>
                        <input type="text" name="cargo" class="form-control"
                               value="<?= htmlspecialchars($usuario['cargo'] ?? '') ?>"
                               placeholder="Ej: Gerente">
                    </div>

                    <!-- Rol -->
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select">
                            <option value="admin"    <?= ($usuario['rol'] ?? '') === 'admin'    ? 'selected' : '' ?>>Admin</option>
                            <option value="usuario"  <?= ($usuario['rol'] ?? '') === 'usuario'  ? 'selected' : '' ?>>Usuario</option>
                            <option value="invitado" <?= ($usuario['rol'] ?? '') === 'invitado' ? 'selected' : '' ?>>Invitado</option>
                        </select>
                    </div>

                    <!-- Foto -->
                    <div class="mb-3">
                        <label class="form-label">Foto de perfil</label>
                        <input type="file" name="foto" id="foto_editar" class="form-control" accept="image/*">
                        <small class="text-muted">Formatos permitidos: JPG, PNG — Máximo 2MB.</small>

                        <!-- PREVIEW ACTUAL O NUEVA -->
                        <div class="mt-3" id="previewContainerEditar" style="<?= !empty($usuario['foto']) ? '' : 'display:none;' ?>">
                            <label class="form-label">Foto actual/nueva</label>
                            <img id="preview_editar"
                                 src="<?= !empty($usuario['foto']) ? BASE_URL . '/' . htmlspecialchars($usuario['foto']) : BASE_URL . '/img/default_user.png' ?>"
                                 width="80" height="80"
                                 style="object-fit:cover;border-radius:50%;border:3px solid #3b82f6;display:block;">
                        </div>
                    </div>

                    <!-- Nueva contraseña -->
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña (opcional)</label>
                        <div class="input-group">
                            <input type="password" id="password_edit" name="password" class="form-control"
                                   placeholder="Dejar vacío para mantener la actual">
                            <span class="input-group-text" id="togglePasswordEdit" style="cursor:pointer;">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                        <small class="text-muted">Déjalo vacío para no cambiarla.</small>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="1" <?= $usuario['estado'] == 1 ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= $usuario['estado'] == 0 ? 'selected' : '' ?>>Bloqueado</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="<?= BASE_URL ?>/?url=usuarios/gestionDeUsuarios" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-check-lg"></i> Guardar cambios
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
