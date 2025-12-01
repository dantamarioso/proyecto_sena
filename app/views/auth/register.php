<div class="register-page">
    <div class="register-container">

        <div class="register-wrapper">
            <div class="card register-card">

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

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre_completo" class="form-control" required>
                    </div>


                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="correo" class="form-control" required>
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" required>
                            <span class="input-group-text" id="togglePassword">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Repetir contraseña -->
                    <div class="mb-3">
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

                    <!-- Términos -->
                    <div class="form-check my-3">
                        <input class="form-check-input" type="checkbox" id="terminos" name="terminos" required>
                        <label class="form-check-label" for="terminos">
                            Acepto los
                            <a href="<?= BASE_URL ?>/auth/terminos" target="_blank">
                                términos y condiciones
                            </a>
                        </label>
                    </div>

                    <div class="d-grid mb-2">
                        <button class="btn-register" type="submit">Registrarse</button>
                    </div>

                    <div class="text-center login-link">
                        ¿Ya tienes cuenta?
                        <a href="<?= BASE_URL ?>/auth/login">Inicia sesión</a>
                    </div>

                </form>

            </div><!-- card -->
        </div><!-- wrapper -->

    </div><!-- container -->
</div><!-- page -->