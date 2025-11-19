const sidebar = document.getElementById("sidebar");
const toggle = document.getElementById("toggleSidebar");
const main = document.querySelector(".main-content");

// Toggle sidebar
toggle.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    main.classList.toggle("collapsed");

    toggle.innerHTML = sidebar.classList.contains("collapsed")
        ? '<i class="bi bi-chevron-double-right"></i>'
        : '<i class="bi bi-chevron-double-left"></i>';
});

// Submenús
document.querySelectorAll(".submenu-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        btn.parentElement.classList.toggle("open");
    });
});
