document.addEventListener("DOMContentLoaded", () => {

    /* ======================================================
       ====  NOTIFICACIÓN EMERGENTE GLOBAL
    ====================================================== */
    window.mostrarNotificacion = function(mensaje, tipo = 'error', duracion = 3000) {
        // Buscar elementos con verificación robusta
        let toast = document.getElementById('notificationToast');
        let toastMsg = document.getElementById('toastMessage');
        
        // Fallback: crear elementos si no existen
        if (!toast) {
            const container = document.body || document.documentElement;
            toast = document.createElement('div');
            toast.className = 'notification-toast';
            toast.id = 'notificationToast';
            
            toastMsg = document.createElement('span');
            toastMsg.id = 'toastMessage';
            toastMsg.textContent = mensaje;
            
            const icon = document.createElement('i');
            icon.className = 'bi';
            
            toast.appendChild(icon);
            toast.appendChild(toastMsg);
            container.appendChild(toast);
        }

        if (!toastMsg) {
            toastMsg = document.getElementById('toastMessage');
        }

        if (!toastMsg) {
            // Si aún no existe, crear un elemento fallback
            toastMsg = document.createElement('span');
            toastMsg.id = 'toastMessage';
            toast.appendChild(toastMsg);
        }

        // Establecer mensaje
        toastMsg.textContent = mensaje;
        
        // Remover clases anteriores
        toast.classList.remove('error', 'success', 'warning');
        
        // Agregar clase de tipo
        toast.classList.add(tipo);
        
        // Cambiar icono según tipo
        const icon = toast.querySelector('i');
        if (icon) {
            icon.className = 'bi';
            switch(tipo) {
                case 'success':
                    icon.classList.add('bi-check-circle');
                    break;
                case 'warning':
                    icon.classList.add('bi-exclamation-triangle');
                    break;
                case 'error':
                default:
                    icon.classList.add('bi-exclamation-circle');
            }
        }
        
        // Mostrar
        toast.classList.add('show');
        
        // Ocultar después del tiempo especificado
        setTimeout(() => {
            toast.classList.remove('show');
        }, duracion);
    };

    /* ======================================================
       ====  TOGGLE PASSWORD - Manejado por password_toggle.js
       ====  (Se comentó aquí para evitar conflictos)
    ====================================================== */
    // Ver password_toggle.js para la funcionalidad de toggle



    /* ======================================================
       ====  PREVIEW DE IMAGEN + VALIDACIÓN 2MB
    ====================================================== */
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
                if (container) container.classList.add("d-none");
                return;
            }

            // Validación tamaño
            if (file.size > MAX_SIZE) {
                alert("La imagen supera el tamaño máximo permitido (2MB).");
                input.value = "";
                preview.src = "";
                if (container) container.classList.add("d-none");
                return;
            }

            // Previsualización
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                if (container) {
                    container.classList.remove("d-none");
                } else if (preview.parentElement) {
                    preview.parentElement.classList.remove("d-none");
                }
            };
            reader.readAsDataURL(file);
        });
    }

    handleImagePreview("foto_crear", "preview_crear", "previewContainerCrear");
    handleImagePreview("foto_editar", "preview_editar", "previewContainerEditar");



    /* ======================================================
       ====  BÚSQUEDA / FILTROS / PAGINACIÓN AJAX
    ====================================================== */

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
                tbody.innerHTML = "";
                const rows = json.data || [];

                currentPage = json.page || 1;
                totalPages  = json.totalPages || 1;

                if (rows.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="11" class="text-center text-muted">No hay usuarios coincidentes.</td>
                        </tr>`;
                } else {
                    rows.forEach(u => {
                        const foto = u.foto
                            ? `<img src="${BASE_URL}/${u.foto}" width="40" height="40" class="rounded-circle" style="object-fit:cover;">`
                            : `<span class="text-muted">Sin foto</span>`;

                        const estadoHtml = u.estado == 1
                            ? `<span class="badge bg-success">Activo</span>`
                            : `<span class="badge bg-danger">Bloqueado</span>`;

                        const cel = u.celular || '<span class="text-muted">N/A</span>';
                        const car = u.cargo   || '<span class="text-muted">Sin cargo</span>';

                        const row = `
                        <tr>
                            <td>${u.id}</td>
                            <td>${escapeHtml(u.nombre)}</td>
                            <td>${escapeHtml(u.correo)}</td>
                            <td>${escapeHtml(u.nombre_usuario)}</td>
                            <td>${cel}</td>
                            <td>${car}</td>
                            <td>${foto}</td>
                            <td>${escapeHtml(u.rol)}</td>
                            <td>${estadoHtml}</td>
                            <td>${u.created_at ?? ""}</td>
                            <td class="text-end">
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
                        </tr>`;

                        tbody.insertAdjacentHTML("beforeend", row);
                    });
                }

                if (spanPagina) spanPagina.textContent = `${currentPage} / ${totalPages}`;
                if (info) info.textContent = `Total: ${json.total ?? 0} usuario(s)`;
            })
            .finally(() => {
                isLoading = false;
            });
    }


    // ESCAPE HTML
    function escapeHtml(txt) {
        return txt ? txt.replace(/[&<>"']/g, m => ({
            "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
        }[m])) : "";
    }


    // EVENTOS
    if (inputBusqueda) {
        inputBusqueda.addEventListener("keyup", () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchUsuarios(1), 300);
        });
    }

    if (filtroEstado) filtroEstado.addEventListener("change", () => fetchUsuarios(1));
    if (filtroRol)    filtroRol.addEventListener("change", () => fetchUsuarios(1));

    if (btnLimpiar) {
        btnLimpiar.addEventListener("click", () => {
            if (inputBusqueda) inputBusqueda.value = "";
            if (filtroEstado) filtroEstado.value = "";
            if (filtroRol) filtroRol.value = "";
            fetchUsuarios(1);
        });
    }

    if (btnPrev) btnPrev.addEventListener("click", () => currentPage > 1 && fetchUsuarios(currentPage - 1));
    if (btnNext) btnNext.addEventListener("click", () => currentPage < totalPages && fetchUsuarios(currentPage + 1));
});
