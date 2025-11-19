<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
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
                        <input type="text" name="celular" class="form-control">
                    </div>

                    <!-- Cargo -->
                    <div class="mb-3">
                        <label class="form-label">Cargo (opcional)</label>
                        <input type="text" name="cargo" class="form-control">
                    </div>

                    <!-- Foto -->
                    <div class="mb-3">
                        <label class="form-label">Foto de perfil (opcional)</label>
                        <input type="file" name="foto" id="foto_crear" class="form-control" accept="image/*">
                        <small class="text-muted">Formatos permitidos: JPG, PNG — Máximo 2MB.</small>

                        <!-- PREVIEW -->
                        <div class="mt-2 d-none" id="previewContainerCrear">
                            <img id="preview_crear" src="" 
                                 width="70" height="70"
                                 style="object-fit:cover;border-radius:50%;border:2px solid #ddd;">
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password_crear" class="form-control" required>
                            <span class="input-group-text pointer" id="togglePasswordCrear">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Repetir contraseña -->
                    <div class="mb-3">
                        <label class="form-label">Repetir contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password2" id="password2_crear" class="form-control" required>
                            <span class="input-group-text pointer" id="togglePassword2Crear">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                        <small id="matchMessageCrear" class="text-danger"></small>
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

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/?url=usuarios/gestionDeUsuarios" class="btn btn-secondary">
                            Volver
                        </a>
                        <button type="submit" class="btn btn-success">
                            Crear usuario
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
