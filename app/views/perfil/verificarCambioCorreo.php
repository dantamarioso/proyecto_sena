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

                    <div id="containerBotones">
                        <button 
                            id="btn-reenviar" 
                            type="button" 
                            class="btn btn-outline-secondary w-100"
                        >
                            <i class="bi bi-arrow-repeat"></i> Reenviar Código
                        </button>
                        <p class="text-muted text-center mt-2" id="contadorText"></p>
                    </div>

                    <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        const btnReenviar = document.getElementById("btn-reenviar");
                        const contadorText = document.getElementById("contadorText");
                        const storageKey = 'cambioCorreo_cooldown_end';

                        // Obtener cooldown guardado o el valor por defecto
                        let cooldownEnd = localStorage.getItem(storageKey);
                        let cooldown = 60; // Duración estándar del cooldown (60 segundos)

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

                        iniciarContador();

                        btnReenviar.addEventListener("click", async (e) => {
                            e.preventDefault();
                            
                            btnReenviar.disabled = true;
                            const originalText = btnReenviar.innerHTML;
                            btnReenviar.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Enviando...';

                            try {
                                const response = await fetch("<?= BASE_URL ?>/?url=perfil/verificarCambioCorreo&reenviar=1", {
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
                                    
                                    // Insertar antes del formulario
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
                    });
                    </script>

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
