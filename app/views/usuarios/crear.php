<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">

                <h3 class="mb-3">Crear nuevo usuario</h3>

                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" id="crear-usuario-form">

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre_completo" class="form-control" required>
                    </div>

                    <!-- Correo -->
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" required>
                    </div>

                    <!-- Usuario -->
                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario</label>
                        <input type="text" name="nombre_usuario" class="form-control" required>
                    </div>

                    <!-- Celular -->
                    <div class="mb-3">
                        <label class="form-label">Celular (opcional)</label>
                        <input type="text" name="celular" class="form-control" placeholder="Ej: +57 123 456 7890">
                    </div>

                    <!-- Cargo -->
                    <div class="mb-3">
                        <label class="form-label">Cargo (opcional)</label>
                        <input type="text" name="cargo" class="form-control" placeholder="Ej: Gerente">
                    </div>

                    <!-- Foto -->
                    <div class="mb-3">
                        <label class="form-label">Foto de perfil (opcional)</label>
                        <input type="file" name="foto" id="foto_crear" class="form-control" accept="image/*">
                        <small class="text-muted">Formatos permitidos: JPG, PNG — Máximo 2MB.</small>

                        <!-- PREVIEW -->
                        <div class="mt-3 d-none" id="previewContainerCrear">
                            <label class="form-label">Vista previa</label>
                            <img id="preview_crear" src="" 
                                 width="80" height="80"
                                 style="object-fit:cover;border-radius:50%;border:3px solid #3b82f6;display:block;">
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password_crear" class="form-control" required>
                            <span class="input-group-text" id="togglePasswordCrear" style="cursor:pointer;">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Repetir contraseña -->
                    <div class="mb-3">
                        <label class="form-label">Repetir contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password2" id="password2_crear" class="form-control" required>
                            <span class="input-group-text" id="togglePassword2Crear" style="cursor:pointer;">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                        <small id="matchMessageCrear" class="text-danger d-none">Las contraseñas no coinciden</small>
                    </div>

                    <!-- Rol -->
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select">
                            <option value="usuario" selected>Usuario</option>
                            <option value="admin">Admin</option>
                            <option value="invitado">Invitado</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="1" selected>Activo</option>
                            <option value="0">Bloqueado</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="<?= BASE_URL ?>/?url=usuarios/gestionDeUsuarios" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="bi bi-check-lg"></i> Crear usuario
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
