document.addEventListener("DOMContentLoaded", () => {

    const passwordField = document.getElementById("login-password");
    const togglePassword = document.getElementById("toggleLoginPassword");

    if (togglePassword && passwordField) {
        togglePassword.addEventListener("click", () => {
            const type = passwordField.type === "password" ? "text" : "password";
            passwordField.type = type;

            togglePassword.innerHTML = type === "text"
                ? '<i class="bi bi-eye-slash-fill"></i>'
                : '<i class="bi bi-eye-fill"></i>';
        });
    }

});
