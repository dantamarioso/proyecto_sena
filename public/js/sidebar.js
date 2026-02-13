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
            link.addEventListener("click", (e) => {
                // Solo cerrar si estamos en mobile
                if (window.innerWidth <= 768) {
                    // Pequeño delay para que el navegador procese el clic
                    setTimeout(() => {
                        sidebar.classList.remove("mobile-open");
                    }, 100);
                }
            });
        });
    }

    // ========== CERRAR SIDEBAR AL HACER CLIC FUERA ==========
    function setupClickOutside() {
        document.addEventListener("click", (e) => {
            // Verificar si estamos en mobile
            if (window.innerWidth > 768) return;

            // NO cerrar si es el botón de toggle
            const toggleBtn = document.getElementById("sidebar-toggle-btn");
            if (toggleBtn && toggleBtn.contains(e.target)) return;

            // NO cerrar si está dentro del sidebar (incluyendo sus descendientes)
            if (sidebar.contains(e.target)) return;

            // Cerrar sidebar si el clic fue fuera
            sidebar.classList.remove("mobile-open");
        }, true); // Usar captura para ser más preciso
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
    setupNotifications();

    // ========== EVENT LISTENERS ==========
    window.addEventListener("resize", handleResize);
    
    // ========== CERRAR SIDEBAR AL ABRIR MODALES DE BOOTSTRAP ==========
    setupModalHandlers();
}

// ========== CERRAR SIDEBAR CUANDO SE ABRE UN MODAL ==========
function setupModalHandlers() {
    // Escuchar eventos de apertura de modales de Bootstrap
    document.addEventListener('show.bs.modal', function(event) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar && window.innerWidth <= 768) {
            sidebar.classList.remove('mobile-open');
        }
    });
    
    // También manejar modales que no usan Bootstrap
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                const target = mutation.target;
                if (target.classList.contains('modal') && target.style.display === 'block') {
                    const sidebar = document.getElementById('sidebar');
                    if (sidebar && window.innerWidth <= 768) {
                        sidebar.classList.remove('mobile-open');
                    }
                }
            }
        });
    });
    
    // Observar todos los modales existentes
    document.querySelectorAll('.modal').forEach(modal => {
        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    });
}

// ========== SISTEMA DE NOTIFICACIONES ==========
function setupNotifications() {
    const notificationsBtn = document.getElementById('notificationsBtn');
    let notificationsModal = document.getElementById('notificationsModal');
    
    if (!notificationsBtn || !notificationsModal) {
        return;
    }

    const closeModalBtn = document.getElementById('closeNotificationsModal');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationCount = document.getElementById('notificationCount');
    const notificationsList = document.getElementById('notificationsList');
    const sidebar = document.getElementById('sidebar');

    // Cargar notificaciones inicialmente
    loadNotifications();

    // Recargar cada 30 segundos
    setInterval(loadNotifications, 30000);

    // Abrir modal
    notificationsBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        notificationsModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // En móvil, cerrar el sidebar
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('mobile-open');
        }
    });

    // Agregar listeners directamente al botón y overlay
    const closeBtn = document.getElementById('closeNotificationsModal');
    const overlay = document.querySelector('#notificationsModal .notifications-modal-overlay');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            notificationsModal.style.display = 'none';
            document.body.style.overflow = '';
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            notificationsModal.style.display = 'none';
            document.body.style.overflow = '';
        });
    }

    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && notificationsModal.style.display === 'flex') {
            notificationsModal.style.display = 'none';
            document.body.style.overflow = '';
        }
    });

    function loadNotifications() {
        // Agregar timestamp para evitar caché
        const url = BASE_URL + '/alertas/get?t=' + Date.now();
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const total = Number(data.count_total ?? data.count ?? 0) || 0;
                    updateNotificationBadge(total, data);
                    updateNotificationsList(data);
                    
                    // AUTO-CERRAR MODAL si no hay notificaciones
                    if (total === 0 && notificationsModal.style.display === 'flex') {
                        notificationsModal.style.display = 'none';
                        document.body.style.overflow = '';
                    }
                }
            });
    }

    function updateNotificationBadge(count, payload) {
        if (count > 0) {
            notificationBadge.textContent = count;
            notificationBadge.style.display = 'inline-block';

            const u = Number(payload?.count_usuarios_pendientes ?? 0) || 0;
            const m = Number(payload?.count_materiales_vencidos ?? 0) || 0;
            const parts = [];
            if (m > 0) parts.push(m + ' material' + (m > 1 ? 'es' : '') + ' vencido' + (m > 1 ? 's' : ''));
            if (u > 0) parts.push(u + ' usuario' + (u > 1 ? 's' : '') + ' pendiente' + (u > 1 ? 's' : ''));
            notificationCount.textContent = parts.length ? parts.join(' • ') : (count + ' alerta' + (count > 1 ? 's' : ''));
            
            // Agregar animación a la campana
            notificationsBtn.classList.add('has-notifications');
        } else {
            notificationBadge.style.display = 'none';
            notificationCount.textContent = '0 alertas';
            
            // Quitar animación
            notificationsBtn.classList.remove('has-notifications');
        }
    }

    function updateNotificationsList(payload) {
        const materiales = Array.isArray(payload?.materiales_vencidos) ? payload.materiales_vencidos : [];
        const usuarios = Array.isArray(payload?.usuarios_pendientes) ? payload.usuarios_pendientes : (Array.isArray(payload?.users) ? payload.users : []);

        const totalMateriales = Number(payload?.count_materiales_vencidos ?? materiales.length ?? 0) || 0;
        const totalUsuarios = Number(payload?.count_usuarios_pendientes ?? usuarios.length ?? 0) || 0;

        if (materiales.length === 0 && usuarios.length === 0) {
            notificationsList.innerHTML = `
                <div class="notification-empty">
                    <i class="bi bi-check-circle"></i>
                    <p class="mb-0">No hay notificaciones</p>
                </div>
            `;
            return;
        }

        let html = '';

        if (materiales.length > 0) {
            html += `<div class="px-3 pt-3 pb-1 text-uppercase text-muted small">Materiales vencidos (${totalMateriales})</div>`;
            html += materiales.map(mat => {
                const nodo = mat.nodo_nombre || 'Sin nodo';
                const linea = mat.linea_nombre || 'Sin línea';
                return `
                    <div class="notification-item" onclick="openMaterialDetails(${mat.id})" title="Click para ver detalles del material">
                        <strong>
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            ${escapeHtml(mat.nombre)}
                        </strong>
                        <small><i class="bi bi-upc-scan"></i> ${escapeHtml(mat.codigo)}</small>
                        <small><i class="bi bi-geo-alt"></i> ${escapeHtml(nodo)} • ${escapeHtml(linea)}</small>
                        <small><i class="bi bi-calendar-x"></i> Fecha vencimiento: ${formatDateOnly(mat.fecha_vencimiento)}</small>
                        <small><i class="bi bi-x-circle"></i> Estado: <strong>VENCIDO</strong></small>
                    </div>
                `;
            }).join('');
        }

        if (usuarios.length > 0) {
            html += `<div class="px-3 pt-3 pb-1 text-uppercase text-muted small">Usuarios pendientes (${totalUsuarios})</div>`;
            html += usuarios.map(user => `
                <div class="notification-item" onclick="openUserAssignment(${user.id})" title="Click para asignar rol, nodo y línea">
                    <strong>
                        <i class="bi bi-person-plus-fill"></i>
                        ${escapeHtml(user.nombre)}
                    </strong>
                    <small><i class="bi bi-envelope"></i> ${escapeHtml(user.correo)}</small>
                    <small><i class="bi bi-clock"></i> ${formatDate(user.fecha_creacion)}</small>
                </div>
            `).join('');
        }

        notificationsList.innerHTML = html;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 60) return `Hace ${diffMins} min`;
        if (diffHours < 24) return `Hace ${diffHours} h`;
        if (diffDays < 7) return `Hace ${diffDays} días`;
        return date.toLocaleDateString('es-ES');
    }

    function formatDateOnly(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString + 'T00:00:00');
        if (isNaN(date.getTime())) return 'N/A';
        return date.toLocaleDateString('es-ES');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Abrir detalles de material desde notificaciones
window.openMaterialDetails = function(materialId) {
    const modal = document.getElementById('notificationsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    const sidebar = document.getElementById('sidebar');
    if (sidebar && window.innerWidth <= 768) {
        sidebar.classList.remove('mobile-open');
    }

    window.location.href = BASE_URL + '/materiales/detalles?id=' + materialId;
};

// Función global para abrir el modal de asignación de usuario
window.openUserAssignment = function(userId) {
    // Cerrar modal de notificaciones
    const modal = document.getElementById('notificationsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Cerrar sidebar en móvil
    const sidebar = document.getElementById('sidebar');
    if (sidebar && window.innerWidth <= 768) {
        sidebar.classList.remove('mobile-open');
    }
    
    // Redirigir a la página de gestión con el usuario seleccionado para abrir el modal
    window.location.href = BASE_URL + '/usuarios/gestionDeUsuarios?assign_user_id=' + userId;
};

// Función global para abrir el editor de usuario (mantener por compatibilidad)
window.openUserEditor = function(userId) {
    // Cerrar modal
    const modal = document.getElementById('notificationsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    // Cerrar sidebar en móvil
    const sidebar = document.getElementById('sidebar');
    if (sidebar && window.innerWidth <= 768) {
        sidebar.classList.remove('mobile-open');
    }
    
    // Redirigir a la página de gestión con el usuario seleccionado
    window.location.href = BASE_URL + '/usuarios/gestionDeUsuarios?user_id=' + userId;
};

// Ejecutar al cargar el DOM
document.addEventListener("DOMContentLoaded", initializeSidebar);

// Para aplicaciones que no hacen full reload (opcional pero recomendado)
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeSidebar);
} else {
    initializeSidebar();
}
