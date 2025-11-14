<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="text-center mb-4">Registro de usuario</h3>

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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="correo" class="form-control" required>
                    </div>

                    <!-- Contraseña + ojo -->
                    <div class="mb-3 position-relative">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" required>
                            <span class="input-group-text" id="togglePassword">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3 position-relative">
                        <label class="form-label">Repetir contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password2" id="password2" class="form-control" required>
                            <span class="input-group-text" id="togglePassword2">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                        <small id="match-message" class="text-danger"></small>
                    </div>

                    <!-- Checklist -->
                    <div id="checklist" class="password-checklist">
                        <p class="mb-1">La contraseña debe incluir:</p>
                        <ul>
                            <li id="chk-length" class="invalid">✖ Mínimo 8 caracteres</li>
                            <li id="chk-uppercase" class="invalid">✖ Al menos una letra mayúscula</li>
                            <li id="chk-special" class="invalid">✖ Al menos un carácter especial (!@#$%&*)</li>
                        </ul>
                    </div>

                    <!-- Términos y condiciones -->
                    <div class="form-check my-3">
                        <input class="form-check-input" type="checkbox" id="terminos" name="terminos" required>

                        <label class="form-check-label" for="terminos">
                            Acepto los
                            <a href="<?= BASE_URL ?>/?url=auth/terminos" target="_blank">
                                términos y condiciones
                            </a>
                        </label>

                    </div>

                    <div class="d-grid mb-2">
                        <button class="btn btn-success" type="submit">Registrarse</button>
                    </div>
                    <div class="text-center">
                        ¿Ya tienes cuenta?
                        <a href="<?= BASE_URL ?>/?url=auth/login">Inicia sesión</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>