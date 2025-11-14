document.addEventListener("DOMContentLoaded", () => {
    const password = document.getElementById("password");
    const password2 = document.getElementById("password2");

    if (password) {
        password.addEventListener("input", validatePassword);
        password2.addEventListener("input", validatePassword);
    }

    // Toggle password visibility
    const togglePassword = document.getElementById("togglePassword");
    const togglePassword2 = document.getElementById("togglePassword2");

    if (togglePassword && password) {
        togglePassword.addEventListener("click", () => {
            const type = password.type === "password" ? "text" : "password";
            password.type = type;
            togglePassword.innerHTML = type === "text"
                ? '<i class="bi bi-eye-slash-fill"></i>'
                : '<i class="bi bi-eye-fill"></i>';
        });
    }

    if (togglePassword2 && password2) {
        togglePassword2.addEventListener("click", () => {
            const type = password2.type === "password" ? "text" : "password";
            password2.type = type;
            togglePassword2.innerHTML = type === "text"
                ? '<i class="bi bi-eye-slash-fill"></i>'
                : '<i class="bi bi-eye-fill"></i>';
        });
    }

    function validatePassword() {
        const pass = password.value;
        const pass2 = password2.value;

        const chkLength  = document.getElementById("chk-length");
        const chkUpper   = document.getElementById("chk-uppercase");
        const chkSpecial = document.getElementById("chk-special");

        const hasLength  = pass.length >= 8;
        const hasUpper   = /[A-Z]/.test(pass);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-]/.test(pass);
        const matches    = pass !== "" && pass === pass2;

        updateStatus(chkLength, hasLength);
        updateStatus(chkUpper, hasUpper);
        updateStatus(chkSpecial, hasSpecial);

        const matchMessage = document.getElementById("match-message");
        if (!matchMessage) return;

        if (matches) {
            matchMessage.textContent = "✔ Las contraseñas coinciden";
            matchMessage.classList.remove("text-danger");
            matchMessage.classList.add("text-success");
        } else if (pass2 !== "") {
            matchMessage.textContent = "✖ Las contraseñas no coinciden";
            matchMessage.classList.remove("text-success");
            matchMessage.classList.add("text-danger");
        } else {
            matchMessage.textContent = "";
        }
    }

    function updateStatus(element, condition) {
        if (!element) return;

        if (condition) {
            element.classList.remove("invalid");
            element.classList.add("valid");
            if (!element.textContent.includes("✔")) {
                element.textContent = "✔ " + element.textContent.replace("✖ ", "");
            }
        } else {
            element.classList.remove("valid");
            element.classList.add("invalid");
            if (!element.textContent.includes("✖")) {
                element.textContent = "✖ " + element.textContent.replace("✔ ", "");
            }
        }
    }
});
