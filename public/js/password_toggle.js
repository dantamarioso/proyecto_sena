// Script para toggle de contraseña en crear/editar usuarios
document.addEventListener("DOMContentLoaded", function() {
    // Función para configurar el toggle
    function setupToggle(toggleId, inputId) {
        const toggleBtn = document.getElementById(toggleId);
        const inputField = document.getElementById(inputId);

        if (!toggleBtn || !inputField) {
            return;
        }

        toggleBtn.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();

            const currentType = inputField.type;
            const newType = currentType === "password" ? "text" : "password";

            inputField.type = newType;

            const icon = toggleBtn.querySelector("i");
            if (icon) {
                icon.className = newType === "password" 
                    ? "bi bi-eye-fill" 
                    : "bi bi-eye-slash-fill";
            }


        });

        // Cambiar cursor
        toggleBtn.style.cursor = "pointer";
    }

    // Configurar todos los toggles
    setupToggle("togglePasswordCrear", "password_crear");
    setupToggle("togglePassword2Crear", "password2_crear");
    setupToggle("togglePasswordEdit", "password_edit");
});
