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
    const storageKey = 'verifyEmail_cooldown_end';

    // Obtener cooldown guardado o el valor por defecto
    let cooldownEnd = localStorage.getItem(storageKey);
    let cooldown = 60; // Duración estándar del cooldown

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
            // Guardar timestamp de cuando termina el cooldown
            const endTime = Date.now() + (cooldown * 1000);
            localStorage.setItem(storageKey, endTime.toString());
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
                localStorage.removeItem(storageKey);
            }
        }, 1000);
    }

    if (btnReenviar) {
        iniciarContador();

        btnReenviar.addEventListener("click", async (e) => {
            e.preventDefault();
            
            btnReenviar.disabled = true;
            const originalText = btnReenviar.innerHTML;
            btnReenviar.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Enviando...';

            try {
                const response = await fetch("<?= BASE_URL ?>/?url=auth/resendVerificationEmail", {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const contentType = response.headers.get('content-type');
                
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Respuesta no es JSON válido: ' + contentType);
                }

                const data = await response.json();

                if (data.success) {
                    // Mostrar mensaje de éxito
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.role = 'alert';
                    alertDiv.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
                    
                    // Insertar antes del contenedor del formulario
                    const form = document.querySelector('form');
                    form.parentElement.insertBefore(alertDiv, form);
                    
                    // Remover después de 4 segundos
                    setTimeout(() => alertDiv.remove(), 4000);
                    
                    // Reiniciar contador a 60 segundos
                    cooldown = 60;
                    iniciarContador();
                } else {
                    // Mostrar error
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.role = 'alert';
                    alertDiv.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>' + (data.message || 'Error desconocido');
                    
                    const form = document.querySelector('form');
                    form.parentElement.insertBefore(alertDiv, form);
                    setTimeout(() => alertDiv.remove(), 4000);
                    
                    btnReenviar.disabled = false;
                    btnReenviar.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error en AJAX:', error);
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.role = 'alert';
                alertDiv.innerHTML = '<i class="bi bi-exclamation-circle me-2"></i>Error al reenviar el código: ' + error.message;
                
                const form = document.querySelector('form');
                form.parentElement.insertBefore(alertDiv, form);
                setTimeout(() => alertDiv.remove(), 5000);
                
                btnReenviar.disabled = false;
                btnReenviar.innerHTML = originalText;
            }
        });
    }

});
</script>
