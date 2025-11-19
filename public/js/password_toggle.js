// Script para toggle de contraseña en crear/editar usuarios
document.addEventListener("DOMContentLoaded", function() {
    console.log("Script de contraseña cargado");

    // Función para configurar el toggle
    function setupToggle(toggleId, inputId) {
        const toggleBtn = document.getElementById(toggleId);
        const inputField = document.getElementById(inputId);

        if (!toggleBtn || !inputField) {
            console.warn(`No se encontraron elementos: toggleId=${toggleId}, inputId=${inputId}`);
            return;
        }

        console.log(`Configurando toggle para: ${toggleId} -> ${inputId}`);

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

            console.log(`Toggle ${toggleId}: ${currentType} -> ${newType}`);
        });

        // Cambiar cursor
        toggleBtn.style.cursor = "pointer";
    }

    // Configurar todos los toggles
    setupToggle("togglePasswordCrear", "password_crear");
    setupToggle("togglePassword2Crear", "password2_crear");
    setupToggle("togglePasswordEdit", "password_edit");

    console.log("Todos los toggles configurados");
});
