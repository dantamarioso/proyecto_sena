document.addEventListener("DOMContentLoaded", () => {
    /* ======================================================
       ====  TOGGLE PASSWORD - Manejado por password_toggle.js
       ====  (Se comentó aquí para evitar conflictos)
    ====================================================== */
    // Ver password_toggle.js para la funcionalidad de toggle

    // ====== PREVIEW IMAGEN Y TAMAÑO MÁXIMO (2MB) ======
    const MAX_SIZE = 2 * 1024 * 1024; // 2MB

    function handleImagePreview(inputId, previewId, containerId = null) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const container = containerId ? document.getElementById(containerId) : null;

        if (!input || !preview) return;

        input.addEventListener("change", () => {
            const file = input.files[0];
            if (!file) {
                preview.src = "";
                if (container) {
                    if (containerId === "previewContainerEditar") {
                        // En editar, mostrar la foto anterior o default
                        container.style.display = "block";
                    } else {
                        container.classList.add("d-none");
                    }
                }
                return;
            }

            // Validar tipo de archivo
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert("Por favor selecciona una imagen válida (JPG, PNG, GIF, WebP)");
                input.value = "";
                return;
            }

            if (file.size > MAX_SIZE) {
                alert("La imagen supera el tamaño máximo de 2MB. Tamaño actual: " + (file.size / 1024 / 1024).toFixed(2) + "MB");
                input.value = "";
                preview.src = "";
                if (container) container.classList.add("d-none");
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                if (container) {
                    if (container.classList) {
                        container.classList.remove("d-none");
                    } else {
                        container.style.display = "block";
                    }
                }
            };
            reader.onerror = () => {
                alert("Error al leer la imagen. Intenta de nuevo.");
                input.value = "";
            };
            reader.readAsDataURL(file);
        });
    }

    handleImagePreview("foto_crear", "preview_crear", "previewContainerCrear");
    handleImagePreview("foto_editar", "preview_editar", "previewContainerEditar");

    // ====== BÚSQUEDA / FILTROS / PAGINACIÓN (AJAX) ======
    const tbody = document.getElementById("usuarios-body");
    const inputBusqueda = document.getElementById("busqueda");
    const filtroEstado = document.getElementById("filtro-estado");
    const filtroRol    = document.getElementById("filtro-rol");
    const btnLimpiar   = document.getElementById("btn-limpiar");
    const btnPrev      = document.getElementById("btn-prev");
    const btnNext      = document.getElementById("btn-next");
    const info         = document.getElementById("usuarios-info");
    const spanPagina   = document.getElementById("pagina-actual");

    let currentPage = 1;
    let totalPages  = 1;
    let isLoading   = false;
    let debounceTimer = null;

    function fetchUsuarios(page = 1) {
        if (!tbody) return;
        if (isLoading) return;
        isLoading = true;

        const q      = inputBusqueda ? inputBusqueda.value.trim() : "";
        const estado = filtroEstado ? filtroEstado.value : "";
        const rol    = filtroRol    ? filtroRol.value    : "";

        const url = `${BASE_URL}/?url=usuarios/buscar&q=${encodeURIComponent(q)}&estado=${encodeURIComponent(estado)}&rol=${encodeURIComponent(rol)}&page=${page}`;

        fetch(url)
            .then(r => r.json())
            .then(json => {
                const rows = json.data || [];
                currentPage = json.page || 1;
                totalPages  = json.totalPages || 1;

                tbody.innerHTML = "";

                if (rows.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center text-muted">No hay usuarios que coincidan.</td>
                        </tr>
                    `;
                } else {
                    rows.forEach(u => {
                        let fotoUrl = "";
                        if (u.foto) {
                            // La foto ya tiene la ruta completa: uploads/fotos/...
                            // Solo agregar BASE_URL al inicio
                            fotoUrl = `${BASE_URL}/${u.foto}`;
                        } else {
                            fotoUrl = `${BASE_URL}/img/default_user.png`;
                        }

                        const fotoHtml = u.foto
                            ? `<img src="${fotoUrl}" width="40" height="40" class="rounded-circle" style="object-fit:cover;" onerror="this.src='${BASE_URL}/img/default_user.png'">`
                            : `<img src="${BASE_URL}/img/default_user.png" width="40" height="40" class="rounded-circle" style="object-fit:cover;" alt="Usuario sin foto">`;

                        const estadoHtml = u.estado == 1
                            ? `<span class="badge bg-success">Activo</span>`
                            : `<span class="badge bg-danger">Bloqueado</span>`;

                        const cel = u.celular ? u.celular : '<span class="text-muted">N/A</span>';
                        const car = u.cargo   ? u.cargo   : '<span class="text-muted">Sin cargo</span>';
                        const rol = u.rol     ? u.rol     : 'usuario';

                        const rowHtml = `
                            <tr>
                                <td>${u.id}</td>
                                <td class="d-none d-md-table-cell">${fotoHtml}</td>
                                <td>${escapeHtml(u.nombre)}</td>
                                <td class="d-none d-md-table-cell">${escapeHtml(u.correo)}</td>
                                <td class="d-none d-lg-table-cell">${escapeHtml(u.nombre_usuario)}</td>
                                <td class="d-none d-xl-table-cell">${cel}</td>
                                <td class="d-none d-xl-table-cell">${car}</td>
                                <td class="d-none d-lg-table-cell"><span class="badge bg-info">${escapeHtml(rol)}</span></td>
                                <td>${estadoHtml}</td>
                                <td class="text-center">
                                    <a href="${BASE_URL}/?url=usuarios/editar&id=${u.id}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    ${
                                        u.estado == 1
                                        ? `<form class="d-inline" method="post" action="${BASE_URL}/?url=usuarios/bloquear">
                                                <input type="hidden" name="id" value="${u.id}">
                                                <button class="btn btn-sm btn-warning" type="submit">
                                                    <i class="bi bi-ban"></i>
                                                </button>
                                           </form>`
                                        : `<form class="d-inline" method="post" action="${BASE_URL}/?url=usuarios/desbloquear">
                                                <input type="hidden" name="id" value="${u.id}">
                                                <button class="btn btn-sm btn-success" type="submit">
                                                    <i class="bi bi-unlock"></i>
                                                </button>
                                           </form>`
                                    }

                                    <form class="d-inline" method="post" action="${BASE_URL}/?url=usuarios/eliminar">
                                        <input type="hidden" name="id" value="${u.id}">
                                        <button class="btn btn-sm btn-danger" type="submit"
                                                onclick="return confirm('¿Eliminar usuario definitivamente?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML("beforeend", rowHtml);
                    });
                }

                if (spanPagina) {
                    spanPagina.textContent = `${currentPage} / ${totalPages}`;
                }

                if (info) {
                    info.textContent = `Total: ${json.total ?? 0} usuario(s)`;
                }

            })
            .catch(err => {
                console.error(err);
            })
            .finally(() => {
                isLoading = false;
            });
    }

    function escapeHtml(str) {
        if (str == null) return "";
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    if (inputBusqueda) {
        inputBusqueda.addEventListener("keyup", () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchUsuarios(1);
            }, 300);
        });
    }

    if (filtroEstado) {
        filtroEstado.addEventListener("change", () => fetchUsuarios(1));
    }
    if (filtroRol) {
        filtroRol.addEventListener("change", () => fetchUsuarios(1));
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener("click", () => {
            if (inputBusqueda) inputBusqueda.value = "";
            if (filtroEstado) filtroEstado.value = "";
            if (filtroRol) filtroRol.value = "";
            fetchUsuarios(1);
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener("click", () => {
            if (currentPage > 1) {
                fetchUsuarios(currentPage - 1);
            }
        });
    }

    if (btnNext) {
        btnNext.addEventListener("click", () => {
            if (currentPage < totalPages) {
                fetchUsuarios(currentPage + 1);
            }
        });
    }

    // Cargar usuarios automáticamente al abrir la página
    fetchUsuarios(1);
});
/* ======================================================
   TOASTS
====================================================== */
function showToast(msg, type = "success") {
    const cont = document.getElementById("toast-container");
    if (!cont) return;

    const toast = document.createElement("div");
    toast.className = "toast-custom";

    toast.style.borderLeft = type === "success"
        ? "4px solid #1cc88a"
        : "4px solid #e74a3b";

    toast.innerHTML = msg;

    cont.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 400);
    }, 2500);
}
