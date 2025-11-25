document.addEventListener("DOMContentLoaded", () => {

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

    handleImagePreview("foto_editar", "preview_editar", "previewContainerEditar");

    // ====== FUNCIONALIDAD DE ZOOM PARA IMÁGENES ======
    function setupImageZoom() {
        const modalZoom = document.getElementById("modalZoomImage");
        const zoomImageSrc = document.getElementById("zoomImageSrc");
        const zoomImageClose = document.getElementById("zoomImageClose");

        if (!modalZoom || !zoomImageSrc || !zoomImageClose) return;

        // Hacer zoom en preview de editar
        const previewEditar = document.getElementById("preview_editar");
        if (previewEditar) {
            previewEditar.addEventListener("click", () => {
                if (previewEditar.src && previewEditar.src !== "") {
                    zoomImageSrc.src = previewEditar.src;
                    modalZoom.classList.add("show");
                }
            });
        }

        // Hacer zoom en preview del modal de cambiar foto
        const previewFoto = document.getElementById("previewFoto");
        if (previewFoto) {
            previewFoto.addEventListener("click", () => {
                if (previewFoto.src && previewFoto.src !== "") {
                    zoomImageSrc.src = previewFoto.src;
                    modalZoom.classList.add("show");
                }
            });
        }

        // Cerrar modal de zoom
        zoomImageClose.addEventListener("click", () => {
            modalZoom.classList.remove("show");
        });

        // Cerrar modal de zoom al hacer click fuera
        modalZoom.addEventListener("click", (e) => {
            if (e.target === modalZoom) {
                modalZoom.classList.remove("show");
            }
        });

        // Cerrar con tecla ESC
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && modalZoom.classList.contains("show")) {
                modalZoom.classList.remove("show");
            }
        });
    }

    setupImageZoom();

    // ====== CAMBIAR FOTO DESDE SIDEBAR (VER PERFIL) ======
    const fotoPerfil = document.getElementById("fotoPerfil");
    const fotoOverlay = document.getElementById("fotoOverlay");
    const inputFoto = document.getElementById("inputFoto");
    const modalCambiarFoto = document.getElementById("modalCambiarFoto");
    const previewFoto = document.getElementById("previewFoto");
    const textoEspera = document.getElementById("textoEspera");
    const btnConfirmarFoto = document.getElementById("btnConfirmarFoto");

    if (fotoPerfil && inputFoto) {
        fotoPerfil.addEventListener("mouseenter", () => {
            if (fotoOverlay) fotoOverlay.style.opacity = "1";
        });

        fotoPerfil.addEventListener("mouseleave", () => {
            if (fotoOverlay) fotoOverlay.style.opacity = "0";
        });

        fotoPerfil.addEventListener("click", () => {
            inputFoto.click();
        });

        fotoOverlay.addEventListener("click", () => {
            inputFoto.click();
        });

        inputFoto.addEventListener("change", function() {
            const file = this.files[0];

            if (!file) return;

            // Validar tipo
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert("Formato no permitido. Solo JPG, PNG, GIF o WebP.");
                this.value = "";
                return;
            }

            // Validar tamaño
            if (file.size > MAX_SIZE) {
                alert("La imagen es demasiado grande. Máximo 2MB.");
                this.value = "";
                return;
            }

            // Mostrar preview en modal
            const reader = new FileReader();
            reader.onload = (e) => {
                previewFoto.src = e.target.result;
                previewFoto.style.display = "block";
                textoEspera.style.display = "none";
                btnConfirmarFoto.style.display = "block";

                // Mostrar modal
                const modal = new bootstrap.Modal(modalCambiarFoto);
                modal.show();
            };
            reader.readAsDataURL(file);
        });

        btnConfirmarFoto.addEventListener("click", function() {
            const file = inputFoto.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('foto', file);

            // Mostrar loading
            btnConfirmarFoto.disabled = true;
            btnConfirmarFoto.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            fetch(BASE_URL + '/?url=perfil/cambiarFoto', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(json => {
                if (json.success) {
                    // Agregar timestamp para evitar caché
                    const fotoConTimestamp = json.foto + '?t=' + new Date().getTime();
                    
                    // Actualizar foto en la vista actual (perfil)
                    if (fotoPerfil) {
                        fotoPerfil.src = fotoConTimestamp;
                    }
                    
                    // Actualizar foto en el sidebar - buscar por múltiples selectores
                    const sidebarAvatar = document.querySelector('.sidebar-avatar');
                    if (sidebarAvatar) {
                        sidebarAvatar.src = fotoConTimestamp;
                    }
                    
                    // También buscar por atributo específico si existe
                    const avatarImg = document.querySelector('img[class*="sidebar-avatar"], .sidebar-header img');
                    if (avatarImg && avatarImg !== sidebarAvatar) {
                        avatarImg.src = fotoConTimestamp;
                    }
                    
                    inputFoto.value = "";

                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(modalCambiarFoto);
                    modal.hide();

                    // Mostrar éxito
                    showToast("Foto de perfil actualizada exitosamente", "success");

                    // Recargar página después de 2 segundos para sincronizar sesión completa
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    alert("Error: " + json.message);
                }
            })
            .catch(err => {
                alert("Error al cambiar la foto");
            })
            .finally(() => {
                btnConfirmarFoto.disabled = false;
                btnConfirmarFoto.innerHTML = '<i class="bi bi-check-lg me-2"></i>Guardar';
            });
        });
    }

    // ====== TOGGLE PASSWORD - Manejado por password_toggle.js ======

});

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
