document.addEventListener("DOMContentLoaded", () => {

    const btnReenviar = document.getElementById("btnReenviar");
    const contadorText = document.getElementById("contadorText");

    let cooldown = 90; // segundos

    function iniciarContador() {
        btnReenviar.disabled = true;

        const timer = setInterval(() => {
            contadorText.textContent = `Puedes reenviar en ${cooldown} segundos...`;
            cooldown--;

            if (cooldown < 0) {
                clearInterval(timer);
                btnReenviar.disabled = false;
                contadorText.textContent = "";
            }
        }, 1000);
    }

    if (btnReenviar) {
        iniciarContador();

        btnReenviar.addEventListener("click", () => {
            window.location.href = BASE_URL + "/?url=auth/resendCode";
        });
    }

});
