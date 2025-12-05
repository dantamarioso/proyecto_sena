<?php
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/auth/login');
    exit;
}
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0"><i class="bi bi-shield-check"></i> Verificar Cambio de Correo</h4>
                </div>
                <div class="card-body p-4" style="color: #333;">
                    <!-- Mensajes de éxito/error -->
                    <?php if (isset($_SESSION['flash_success'])) : ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['flash_error'])) : ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_error']); ?>
                    <?php endif; ?>

                    <!-- Información -->
                    <div class="text-center mb-4 p-3 bg-light rounded" style="border: 1px solid #dee2e6;">
                        <p class="mb-1" style="color: #333;"><strong>Nuevo correo:</strong></p>
                        <p class="fw-bold" style="color: #000; font-size: 1.1rem;"><?= htmlspecialchars($newEmail ?? 'No disponible') ?></p>
                        <small class="d-block mt-2" style="color: #666;">Hemos enviado un código de verificación a este correo</small>
                    </div>

                    <!-- Formulario -->
                    <form method="POST" action="<?= BASE_URL ?>/perfil/verificarCambioCorreo">
                        <div class="mb-3">
                            <label for="codigo" class="form-label fw-bold" style="color: #000;">Código de Verificación</label>
                            <input 
                                type="text" 
                                id="codigo" 
                                name="codigo" 
                                class="form-control form-control-lg text-center font-monospace" 
                                placeholder="000000"
                                maxlength="6"
                                inputmode="numeric"
                                required
                                autofocus
                                style="color: #000; font-weight: bold;"
                            >
                            <small class="d-block mt-2" style="color: #666;">Ingresa el código de 6 dígitos</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-2">
                            <i class="bi bi-check-lg"></i> Verificar Código
                        </button>
                    </form>

                    <hr class="my-3">

                    <!-- Reenviar código -->
                    <div class="text-center">
                        <button 
                            id="btn-reenviar" 
                            type="button" 
                            class="btn btn-outline-secondary w-100 mb-2"
                            style="color: #000; border-color: #999;"
                        >
                            <i class="bi bi-arrow-repeat"></i> Reenviar Código
                        </button>
                        <p class="small mb-0" id="contadorText" style="color: #666;"></p>
                    </div>

                    <hr class="my-3">

                    <!-- Volver -->
                    <div class="text-center">
                        <a href="<?= BASE_URL ?>/perfil/editar" class="btn btn-link" style="color: #007bff;">
                            <i class="bi bi-arrow-left"></i> Volver a Editar Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const btnReenviar = document.getElementById("btn-reenviar");
    const contadorText = document.getElementById("contadorText");
    const storageKey = 'cambioCorreo_cooldown_end';

    // Obtener cooldown guardado
    let cooldownEnd = localStorage.getItem(storageKey);
    let cooldown = 60;

    if (cooldownEnd) {
        const remainingSeconds = Math.ceil((parseInt(cooldownEnd) - Date.now()) / 1000);
        if (remainingSeconds > 0) {
            cooldown = remainingSeconds;
        } else {
            localStorage.removeItem(storageKey);
        }
    }

    function iniciarContador() {
        if (cooldown > 0) {
            btnReenviar.disabled = true;
            const endTime = Date.now() + (cooldown * 1000);
            localStorage.setItem(storageKey, endTime.toString());
        }

        const timer = setInterval(() => {
            if (cooldown > 0) {
                contadorText.textContent = `⏱️ Espera ${cooldown} segundos...`;
                cooldown--;
            }

            if (cooldown < 0) {
                clearInterval(timer);
                btnReenviar.disabled = false;
                contadorText.textContent = "";
                localStorage.removeItem(storageKey);
            }
        }, 1000);
    }

    iniciarContador();

    btnReenviar.addEventListener("click", async (e) => {
        e.preventDefault();
        
        btnReenviar.disabled = true;
        const originalText = btnReenviar.innerHTML;
        btnReenviar.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Enviando...';

        try {
            const response = await fetch("<?= BASE_URL ?>/perfil/verificarCambioCorreo?reenviar=1", {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            const contentType = response.headers.get('content-type');
            
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Respuesta inválida del servidor');
            }

            const data = await response.json();

            if (data.success) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                alertDiv.role = 'alert';
                alertDiv.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message + 
                                   '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                
                const form = document.querySelector('form');
                form.parentElement.insertBefore(alertDiv, form);
                
                setTimeout(() => alertDiv.remove(), 4000);
                
                cooldown = 60;
                iniciarContador();
            } else {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                alertDiv.role = 'alert';
                alertDiv.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>' + (data.message || 'Error desconocido') +
                                   '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                
                const form = document.querySelector('form');
                form.parentElement.insertBefore(alertDiv, form);
                setTimeout(() => alertDiv.remove(), 4000);
                
                btnReenviar.disabled = false;
                btnReenviar.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
            alertDiv.role = 'alert';
            alertDiv.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>Error al reenviar: ' + error.message +
                               '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            
            const form = document.querySelector('form');
            form.parentElement.insertBefore(alertDiv, form);
            setTimeout(() => alertDiv.remove(), 5000);
            
            btnReenviar.disabled = false;
            btnReenviar.innerHTML = originalText;
        }
    });
});
</script>