<div class="recovery-page">
    <div class="recovery-container">
        <div class="recovery-card">

            <h3 class="text-center">Recuperar Contraseña</h3>
            <h4 class="text-center">Ingresa tu correo para recibir un código</h4>

            <?php if (!empty($_SESSION['flash_error'])) :
                ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['flash_error']);
                    unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/auth/sendCode">

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-envelope me-2"></i>Correo Registrado</label>
                    <input type="email" name="correo" class="form-control" placeholder="tu@email.com" required autofocus>
                </div>

                <button type="submit" class="btn btn-recovery">
                    <i class="bi bi-arrow-right me-2"></i>Enviar Código
                </button>
            </form>

            <div class="divider"></div>

            <div class="text-center">
                <p class="text-muted mb-3">¿Recuerdas tu contraseña?</p>
                <a href="<?= BASE_URL ?>/auth/login" class="link-recovery">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Volver a Iniciar Sesión
                </a>
            </div>

        </div>
    </div>
</div>
