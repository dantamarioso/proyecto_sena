function initializeSidebar() {
    const sidebar = document.getElementById("sidebar");

    if (!sidebar) return;

    // ========== CREAR BOTÓN TOGGLE EN MÓVIL ==========
    function createToggleButton() {
        // Remover botón anterior si existe
        const existingBtn = document.getElementById("sidebar-toggle-btn");
        if (existingBtn) existingBtn.remove();

        // Solo crear en pantallas menores a 768px
        if (window.innerWidth > 768) return;

        const toggleBtn = document.createElement("button");
        toggleBtn.id = "sidebar-toggle-btn";
        toggleBtn.className = "btn btn-sm btn-dark";
        toggleBtn.innerHTML = '<i class="bi bi-list"></i>';
        toggleBtn.style.cssText = `
            position: fixed; 
            top: 20px; 
            left: 20px; 
            z-index: 1040; 
            display: block;
            padding: 8px 12px !important;
            font-size: 18px !important;
            min-height: auto !important;
            min-width: auto !important;
            height: auto !important;
            width: auto !important;
            background-color: #212529 !important;
            border: 1px solid #343a40 !important;
            color: #fff !important;
        `;

        toggleBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            sidebar.classList.toggle("mobile-open");
        });

        document.body.appendChild(toggleBtn);
    }

    // ========== CERRAR SIDEBAR AL HACER CLIC EN ENLACE ==========
    function setupSidebarLinks() {
        document.querySelectorAll(".sidebar-nav a").forEach((link) => {
            link.addEventListener("click", () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove("mobile-open");
                }
            });
        });
    }

    // ========== CERRAR SIDEBAR AL HACER CLIC FUERA ==========
    function setupClickOutside() {
        document.addEventListener("click", (e) => {
            if (
                window.innerWidth <= 768 &&
                !sidebar.contains(e.target) &&
                !document.getElementById("sidebar-toggle-btn")?.contains(e.target)
            ) {
                sidebar.classList.remove("mobile-open");
            }
        });
    }

    // ========== MANEJAR REDIMENSIONAMIENTO ==========
    function handleResize() {
        const toggleBtn = document.getElementById("sidebar-toggle-btn");

        if (window.innerWidth <= 768) {
            // Estamos en móvil
            if (!toggleBtn) {
                createToggleButton();
            }
            sidebar.classList.remove("mobile-open");
        } else {
            // Estamos en desktop
            if (toggleBtn) {
                toggleBtn.remove();
            }
            sidebar.classList.remove("mobile-open");
        }
    }

    // ========== INICIALIZACIÓN ==========
    createToggleButton();
    setupSidebarLinks();
    setupClickOutside();

    // ========== EVENT LISTENERS ==========
    window.addEventListener("resize", handleResize);
}

// Ejecutar al cargar el DOM
document.addEventListener("DOMContentLoaded", initializeSidebar);

// Para aplicaciones que no hacen full reload (opcional pero recomendado)
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeSidebar);
} else {
    initializeSidebar();
}
