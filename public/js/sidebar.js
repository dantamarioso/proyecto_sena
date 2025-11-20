const sidebar = document.getElementById("sidebar");

// Si no existe sidebar (login/register), no hacer nada
if (!sidebar) {
    console.log("Sidebar no encontrado - página de login/register");
    // Asegurarse de que no hay botón toggle
    const toggleBtn = document.getElementById("sidebar-toggle-btn");
    if (toggleBtn) {
        toggleBtn.remove();
    }
} else {
    // Submenús (si hay)
    document.querySelectorAll(".submenu-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            btn.parentElement.classList.toggle("open");
        });
    });

    // Toggle sidebar en móvil (768px)
    const mediaQuery = window.matchMedia("(max-width: 768px)");

    function handleResponsive(e) {
        if (e.matches) {
            // En móvil - agregar botón para abrir/cerrar
            if (!document.getElementById("sidebar-toggle-btn")) {
                const toggleBtn = document.createElement("button");
                toggleBtn.id = "sidebar-toggle-btn";
                toggleBtn.className = "btn btn-sm btn-dark";
                toggleBtn.innerHTML = '<i class="bi bi-list"></i>';
                toggleBtn.style.cssText = "position: fixed; top: 20px; left: 20px; z-index: 1040; display: none;";
                document.body.appendChild(toggleBtn);
                
                // Mostrar botón solo en móvil
                if (window.innerWidth <= 768) {
                    toggleBtn.style.display = "block";
                }

                toggleBtn.addEventListener("click", () => {
                    sidebar.classList.toggle("mobile-open");
                });
            }

            // Cerrar sidebar al hacer click en un enlace
            document.querySelectorAll(".sidebar-nav a, .sidebar-nav button").forEach(link => {
                link.addEventListener("click", () => {
                    sidebar.classList.remove("mobile-open");
                });
            });
        } else {
            // En desktop - remover toggle
            const toggleBtn = document.getElementById("sidebar-toggle-btn");
            if (toggleBtn) {
                toggleBtn.remove();
            }
            sidebar.classList.remove("mobile-open");
        }
    }

    mediaQuery.addEventListener("change", handleResponsive);
    handleResponsive(mediaQuery);
}