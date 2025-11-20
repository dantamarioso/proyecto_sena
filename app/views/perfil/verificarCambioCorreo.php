<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/?url=auth/login");
    exit;
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">Verificar Cambio de Correo</h3>
                </div>
                <div class="card-body p-5">
                    <?php if (isset($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> <?= $_SESSION['flash_success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> <?= $_SESSION['flash_error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_error']); ?>
                    <?php endif; ?>

                    <div class="text-center mb-4">
                        <p class="text-muted">
                            Hemos enviado un código de verificación a:<br>
                            <strong><?= htmlspecialchars($newEmail) ?></strong>
                        </p>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/?url=perfil/verificarCambioCorreo">
                        <div class="mb-4">
                            <label for="codigo" class="form-label">Código de Verificación</label>
                            <input 
                                type="text" 
                                id="codigo" 
                                name="codigo" 
                                class="form-control form-control-lg text-center" 
                                placeholder="000000"
                                maxlength="6"
                                inputmode="numeric"
                                required
                                autofocus
                            >
                            <small class="form-text text-muted">
                                Ingresa el código de 6 dígitos enviado a tu correo
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="bi bi-check-lg"></i> Verificar Código
                        </button>
                    </form>

                    <?php if ($remainingCooldown > 0): ?>
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-hourglass-split"></i>
                            Puedes reenviar el código en <strong id="cooldown"><?= $remainingCooldown ?></strong> segundos
                        </div>
                        <button 
                            id="btn-reenviar" 
                            type="button" 
                            class="btn btn-outline-secondary w-100" 
                            disabled
                        >
                            Reenviar Código
                        </button>
                        <script>
                            let countdown = <?= $remainingCooldown ?>;
                            const countdownEl = document.getElementById('cooldown');
                            const btnReenviar = document.getElementById('btn-reenviar');
                            
                            const interval = setInterval(() => {
                                countdown--;
                                countdownEl.textContent = countdown;
                                
                                if (countdown <= 0) {
                                    clearInterval(interval);
                                    btnReenviar.disabled = false;
                                }
                            }, 1000);
                        </script>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/?url=perfil/verificarCambioCorreo&reenviar=1" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-repeat"></i> Reenviar Código
                        </a>
                    <?php endif; ?>

                    <div class="mt-4 text-center">
                        <a href="<?= BASE_URL ?>/?url=perfil/editar" class="text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Volver a Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
