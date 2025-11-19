<div class="verify-page">
    <div class="verify-container">
        <div class="verify-card">

            <h3 class="text-center">Verificar Email</h3>
            <h4 class="text-center">Completa tu registro confirmando tu correo</h4>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <div class="text-muted mb-4">
                <i class="bi bi-info-circle me-2"></i>
                Enviamos un código a: <strong><?= htmlspecialchars($_SESSION['register_correo']) ?></strong>
            </div>

            <form method="post" action="<?= BASE_URL ?>/?url=auth/verifyEmailPost">

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-key me-2"></i>Código de Verificación</label>
                    <input type="text" name="code" class="form-control" maxlength="6" placeholder="000000" 
                           inputmode="numeric" pattern="[0-9]{6}" required autofocus>
                    <small class="text-muted d-block mt-2">Código de 6 dígitos • Válido por 10 minutos</small>
                </div>

                <button type="submit" class="btn btn-verify">
                    <i class="bi bi-check-lg me-2"></i>Verificar Email
                </button>

            </form>

            <div class="text-center mb-3">
                <button id="btnReenviar" class="btn btn-outline-recovery" type="button">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reenviar Código
                </button>
                <p class="text-muted" id="contadorText"></p>
            </div>

            <div class="divider"></div>

            <div class="text-center">
                <a href="<?= BASE_URL ?>/?url=auth/register" class="link-recovery">
                    <i class="bi bi-arrow-left me-2"></i>Volver al Registro
                </a>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const btnReenviar = document.getElementById("btnReenviar");
    const contadorText = document.getElementById("contadorText");

    let cooldown = <?= (int)($remainingCooldown ?? 90) ?>; // segundos

    function iniciarContador() {
        if (cooldown > 0) {
            btnReenviar.disabled = true;
        }

        const timer = setInterval(() => {
            if (cooldown > 0) {
                contadorText.textContent = `⏱️ Puedes reenviar en ${cooldown} segundos...`;
                cooldown--;
            }

            if (cooldown < 0) {
                clearInterval(timer);
                btnReenviar.disabled = false;
                contadorText.textContent = "";
            }
        }, 1000);
    }

    if (btnReenviar) {
        iniciarContador();

        btnReenviar.addEventListener("click", () => {
            window.location.href = "<?= BASE_URL ?>/?url=auth/resendVerificationEmail";
        });
    }

});
</script>
