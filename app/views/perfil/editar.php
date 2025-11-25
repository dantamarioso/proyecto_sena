<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/?url=auth/login");
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">

                <h3 class="mb-3">Editar perfil</h3>

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

                    <!-- Información básica -->
                    <fieldset>
                        <legend class="mb-3" style="font-size: 1.1rem; font-weight: 600;">Información personal</legend>

                        <div class="mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" class="form-control"
                                   value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre de usuario</label>
                            <input type="text" name="nombre_usuario" class="form-control"
                                   value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Celular</label>
                            <input type="text" name="celular" class="form-control"
                                   value="<?= htmlspecialchars($usuario['celular'] ?? '') ?>"
                                   placeholder="Ej: +57 123 456 7890">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo" class="form-control"
                                   value="<?= htmlspecialchars($usuario['cargo'] ?? '') ?>"
                                   placeholder="Ej: Gerente">
                        </div>
                    </fieldset>

                    <hr class="my-4">

                    <!-- Foto de perfil -->
                    <fieldset>
                        <legend class="mb-3" style="font-size: 1.1rem; font-weight: 600;">Foto de perfil</legend>

                        <div class="mb-3">
                            <label class="form-label">Cambiar foto</label>
                            <input type="file" name="foto" id="foto_editar" class="form-control" accept="image/*">
                            <small class="text-muted">Formatos permitidos: JPG, PNG — Máximo 2MB.</small>

                            <div class="image-preview-container" id="previewContainerEditar" style="<?= !empty($usuario['foto']) ? '' : 'display:none;' ?>">
                                <img id="preview_editar"
                                     src="<?= !empty($usuario['foto']) ? BASE_URL . '/' . htmlspecialchars($usuario['foto']) : BASE_URL . '/img/default_user.png' ?>"
                                     class="image-preview"
                                     alt="Vista previa de foto">
                            </div>
                        </div>
                    </fieldset>

                    <hr class="my-4">

                    <!-- Seguridad (contraseña) -->
                    <fieldset>
                        <legend class="mb-3" style="font-size: 1.1rem; font-weight: 600;">Seguridad</legend>

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
                    </fieldset>

                    <!-- Campos solo para admin -->
                    <?php if ($currentRol === 'admin'): ?>
                        <hr class="my-4">

                        <fieldset>
                            <legend class="mb-3" style="font-size: 1.1rem; font-weight: 600;">
                                <i class="bi bi-shield-lock"></i> Configuración de administrador
                            </legend>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Rol</label>
                                    <select name="rol" class="form-select">
                                        <option value="admin" <?= ($usuario['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="usuario" <?= ($usuario['rol'] ?? '') === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                                        <option value="invitado" <?= ($usuario['rol'] ?? '') === 'invitado' ? 'selected' : '' ?>>Invitado</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select">
                                        <option value="1" <?= $usuario['estado'] == 1 ? 'selected' : '' ?>>Activo</option>
                                        <option value="0" <?= $usuario['estado'] == 0 ? 'selected' : '' ?>>Bloqueado</option>
                                    </select>
                                </div>
                            </div>

                            <?php if (!$isOwnProfile): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Estás editando el perfil de otro usuario. Los cambios serán registrados en el historial de auditoría.
                                </div>
                            <?php endif; ?>
                        </fieldset>
                    <?php endif; ?>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between gap-2 mt-4">
                        <a href="<?= BASE_URL ?>/?url=perfil/ver" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-arrow-left"></i> Cancelar
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