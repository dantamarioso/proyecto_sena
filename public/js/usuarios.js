document.addEventListener("DOMContentLoaded", () => {

    const passField = document.getElementById("password_edit");
    const toggle = document.getElementById("togglePasswordEdit");

    if (passField && toggle) {
        toggle.addEventListener("click", () => {

            const type = passField.type === "password" ? "text" : "password";
            passField.type = type;

            toggle.innerHTML = type === "text"
                ? '<i class="bi bi-eye-slash-fill"></i>'
                : '<i class="bi bi-eye-fill"></i>';
        });
    }

});
