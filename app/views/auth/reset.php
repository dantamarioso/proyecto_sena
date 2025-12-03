<div class="reset-page">
    <div class="reset-container">
        <div class="reset-card">

            <h3 class="text-center">Nueva Contraseña</h3>
            <h4 class="text-center">Establece una contraseña segura para tu cuenta</h4>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/auth/resetPasswordPost">

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-lock me-2"></i>Nueva Contraseña</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" 
                               placeholder="••••••••" required>
                        <span class="input-group-text" id="togglePassword">
                            <i class="bi bi-eye-fill"></i>
                        </span>
                    </div>
                    <small class="text-muted d-block mt-2">Mínimo 8 caracteres, mayúscula y carácter especial</small>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-lock-check me-2"></i>Repetir Contraseña</label>
                    <div class="input-group">
                        <input type="password" name="password2" id="password2" class="form-control" 
                               placeholder="••••••••" required>
                        <span class="input-group-text" id="togglePassword2">
                            <i class="bi bi-eye-fill"></i>
                        </span>
                    </div>
                </div>

                <!-- Checklist de requisitos -->
                <div id="checklist" class="password-checklist mb-4" style="background: rgba(255,255,255,0.1); padding: 12px 16px; border-radius: 10px;">
                    <p class="mb-2 text-muted"><strong><i class="bi bi-info-circle me-2"></i>Requisitos:</strong></p>
                    <ul class="mb-0" style="padding-left: 28px;">
                        <li id="chk-length" class="text-muted" style="margin-bottom: 4px;">
                            <i class="bi bi-x-circle me-2" style="color: #b30c1c;"></i>Mínimo 8 caracteres
                        </li>
                        <li id="chk-uppercase" class="text-muted" style="margin-bottom: 4px;">
                            <i class="bi bi-x-circle me-2" style="color: #b30c1c;"></i>Al menos una letra mayúscula
                        </li>
                        <li id="chk-special" class="text-muted">
                            <i class="bi bi-x-circle me-2" style="color: #b30c1c;"></i>Al menos un carácter especial (!@#$%&*)
                        </li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-reset">
                    <i class="bi bi-check-lg me-2"></i>Guardar Nueva Contraseña
                </button>

            </form>

            <div class="divider"></div>

            <div class="text-center">
                <a href="<?= BASE_URL ?>/auth/login" class="link-recovery">
                    <i class="bi bi-arrow-left me-2"></i>Volver a Iniciar Sesión
                </a>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const password = document.getElementById("password");
    const password2 = document.getElementById("password2");
    const togglePassword = document.getElementById("togglePassword");
    const togglePassword2 = document.getElementById("togglePassword2");

    // Toggle de visibilidad
    togglePassword.addEventListener("click", () => {
        const type = password.type === "password" ? "text" : "password";
        password.type = type;
        togglePassword.innerHTML = type === "text"
            ? '<i class="bi bi-eye-slash-fill"></i>'
            : '<i class="bi bi-eye-fill"></i>';
    });

    togglePassword2.addEventListener("click", () => {
        const type = password2.type === "password" ? "text" : "password";
        password2.type = type;
        togglePassword2.innerHTML = type === "text"
            ? '<i class="bi bi-eye-slash-fill"></i>'
            : '<i class="bi bi-eye-fill"></i>';
    });

    // Validación de requisitos
    password.addEventListener("input", () => {
        const hasLength = password.value.length >= 8;
        const hasUpper = /[A-Z]/.test(password.value);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-]/.test(password.value);

        updateChecklist("chk-length", hasLength);
        updateChecklist("chk-uppercase", hasUpper);
        updateChecklist("chk-special", hasSpecial);
    });

    function updateChecklist(id, isValid) {
        const elem = document.getElementById(id);
        if (isValid) {
            elem.classList.remove("text-muted");
            elem.classList.add("text-success");
            elem.querySelector("i").className = "bi bi-check-circle me-2";
            elem.querySelector("i").style.color = "#39A900";
        } else {
            elem.classList.add("text-muted");
            elem.classList.remove("text-success");
            elem.querySelector("i").className = "bi bi-x-circle me-2";
            elem.querySelector("i").style.color = "#b30c1c";
        }
    }
});
</script>
