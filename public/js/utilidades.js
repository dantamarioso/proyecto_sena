/**
 * UTILIDADES JAVASCRIPT - MEJORAS INTERACTIVAS
 * Archivo con funciones helper para mejorar la experiencia del usuario
 */

// =====================================================
// NOTIFICACIONES Y ALERTAS
// =====================================================

/**
 * Mostrar notificación toast
 * @param {string} title - Título de la notificación
 * @param {string} message - Mensaje de la notificación
 * @param {string} type - Tipo: 'success', 'danger', 'warning', 'info'
 * @param {number} duration - Duración en ms (0 = no auto-hide)
 */
function showToast(title, message, type = 'info', duration = 3000) {
    const container = document.querySelector('.toast-container') || createToastContainer();
    
    const toastEl = document.createElement('div');
    toastEl.className = `toast toast-${type}`;
    
    const iconMap = {
        success: 'bi-check-circle-fill',
        danger: 'bi-exclamation-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    
    toastEl.innerHTML = `
        <i class="bi ${iconMap[type]} toast-icon"></i>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="bi bi-x"></i>
        </button>
    `;
    
    container.appendChild(toastEl);
    
    if (duration > 0) {
        setTimeout(() => {
            toastEl.classList.add('hide');
            setTimeout(() => toastEl.remove(), 300);
        }, duration);
    }
}

/**
 * Crear contenedor de toast si no existe
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

/**
 * Mostrar alerta
 * @param {string} title - Título
 * @param {string} message - Mensaje
 * @param {string} type - Tipo: 'success', 'danger', 'warning', 'info'
 */
function showAlert(title, message, type = 'info') {
    const alertEl = document.createElement('div');
    alertEl.className = `alert alert-${type}`;
    
    const iconMap = {
        success: 'bi-check-circle-fill',
        danger: 'bi-exclamation-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    
    alertEl.innerHTML = `
        <i class="bi ${iconMap[type]} alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">${title}</div>
            <div class="alert-message">${message}</div>
        </div>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="bi bi-x"></i>
        </button>
    `;
    
    // Insertar al inicio del main content
    const mainContent = document.querySelector('main') || document.querySelector('.container');
    if (mainContent) {
        mainContent.insertBefore(alertEl, mainContent.firstChild);
    }
}

// =====================================================
// CONFIRMACIÓN DE ACCIONES
// =====================================================

/**
 * Mostrar diálogo de confirmación
 * @param {string} title - Título del diálogo
 * @param {string} message - Mensaje
 * @param {function} onConfirm - Callback si confirma
 * @param {function} onCancel - Callback si cancela
 * @param {object} options - Opciones adicionales
 */
function showConfirmDialog(title, message, onConfirm, onCancel = null, options = {}) {
    const {
        confirmText = 'Confirmar',
        cancelText = 'Cancelar',
        confirmClass = 'btn-danger',
        icon = 'bi-exclamation-triangle'
    } = options;
    
    const dialogEl = document.createElement('div');
    dialogEl.className = 'confirm-dialog';
    
    dialogEl.innerHTML = `
        <div class="confirm-dialog-content">
            <div class="confirm-icon">
                <i class="bi ${icon}"></i>
            </div>
            <h3 class="confirm-title">${title}</h3>
            <p class="confirm-message">${message}</p>
            <div class="confirm-actions">
                <button class="btn btn-secondary" onclick="this.closest('.confirm-dialog').remove()">
                    ${cancelText}
                </button>
                <button class="btn ${confirmClass}">
                    ${confirmText}
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(dialogEl);
    
    // Event listeners
    const confirmBtn = dialogEl.querySelector(`.btn.${confirmClass}`);
    confirmBtn.addEventListener('click', () => {
        dialogEl.remove();
        onConfirm?.();
    });
    
    dialogEl.addEventListener('click', (e) => {
        if (e.target === dialogEl) {
            dialogEl.remove();
            onCancel?.();
        }
    });
    
    // Cerrar con ESC
    const handleEsc = (e) => {
        if (e.key === 'Escape') {
            dialogEl.remove();
            onCancel?.();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
}

// =====================================================
// FORMULARIOS Y VALIDACIÓN
// =====================================================

/**
 * Validar email
 * @param {string} email
 * @returns {boolean}
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validar contraseña
 * @param {string} password
 * @returns {object} - { valid: boolean, errors: array }
 */
function validatePassword(password) {
    const errors = [];
    
    if (password.length < 8) {
        errors.push('Mínimo 8 caracteres');
    }
    if (!/[A-Z]/.test(password)) {
        errors.push('Debe incluir mayúsculas');
    }
    if (!/[0-9]/.test(password)) {
        errors.push('Debe incluir números');
    }
    if (!/[!@#$%^&*]/.test(password)) {
        errors.push('Debe incluir caracteres especiales (!@#$%^&*)');
    }
    
    return {
        valid: errors.length === 0,
        errors: errors
    };
}

/**
 * Toggle de visibilidad contraseña
 * @param {string} inputId - ID del input
 * @param {string} buttonId - ID del botón toggle
 */
function togglePasswordVisibility(inputId, buttonId = null) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    
    if (buttonId) {
        const btn = document.getElementById(buttonId);
        if (btn) {
            btn.innerHTML = isPassword ? 
                '<i class="bi bi-eye-slash"></i>' : 
                '<i class="bi bi-eye"></i>';
        }
    }
}

/**
 * Marcar campo como inválido
 * @param {HTMLElement|string} element - Elemento o ID
 * @param {string} message - Mensaje de error
 */
function markInvalid(element, message = '') {
    const el = typeof element === 'string' ? 
        document.getElementById(element) : element;
    
    if (!el) return;
    
    el.classList.remove('is-valid');
    el.classList.add('is-invalid');
    
    if (message) {
        const feedback = el.nextElementSibling;
        if (feedback?.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
        }
    }
}

/**
 * Marcar campo como válido
 * @param {HTMLElement|string} element - Elemento o ID
 */
function markValid(element) {
    const el = typeof element === 'string' ? 
        document.getElementById(element) : element;
    
    if (!el) return;
    
    el.classList.remove('is-invalid');
    el.classList.add('is-valid');
}

/**
 * Limpiar validación de campo
 * @param {HTMLElement|string} element - Elemento o ID
 */
function clearValidation(element) {
    const el = typeof element === 'string' ? 
        document.getElementById(element) : element;
    
    if (!el) return;
    
    el.classList.remove('is-valid', 'is-invalid');
}

// =====================================================
// CARGA DE DATOS
// =====================================================

/**
 * Mostrar spinner de carga
 * @returns {HTMLElement} - El elemento spinner
 */
function showLoadingSpinner() {
    const spinner = document.createElement('div');
    spinner.className = 'loading-overlay';
    spinner.innerHTML = `
        <div class="spinner"></div>
        <div class="loading-text">Cargando...</div>
    `;
    document.body.appendChild(spinner);
    return spinner;
}

/**
 * Ocultar spinner de carga
 */
function hideLoadingSpinner() {
    const spinner = document.querySelector('.loading-overlay');
    if (spinner) spinner.remove();
}

/**
 * Fetch mejorado con manejo de errores
 * @param {string} url
 * @param {object} options
 * @returns {Promise}
 */
async function fetchWithHandler(url, options = {}) {
    try {
        showLoadingSpinner();
        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }
        
        const data = await response.json();
        hideLoadingSpinner();
        return data;
    } catch (error) {
        hideLoadingSpinner();
        showToast('Error', error.message, 'danger');
        console.error('Fetch error:', error);
        throw error;
    }
}

// =====================================================
// MANIPULACIÓN DE CLASES Y ESTILOS
// =====================================================

/**
 * Toggle de clase
 * @param {HTMLElement|string} element
 * @param {string} className
 */
function toggleClass(element, className) {
    const el = typeof element === 'string' ? 
        document.getElementById(element) : element;
    
    if (el) {
        el.classList.toggle(className);
    }
}

/**
 * Agregar clase
 * @param {HTMLElement|string} element
 * @param {string} className
 */
function addClass(element, className) {
    const el = typeof element === 'string' ? 
        document.getElementById(element) : element;
    
    if (el) {
        el.classList.add(className);
    }
}

/**
 * Remover clase
 * @param {HTMLElement|string} element
 * @param {string} className
 */
function removeClass(element, className) {
    const el = typeof element === 'string' ? 
        document.getElementById(element) : element;
    
    if (el) {
        el.classList.remove(className);
    }
}

// =====================================================
// TABLAS Y DATOS
// =====================================================

/**
 * Expandir/contraer fila de tabla
 * @param {HTMLElement} button - Botón clickeado
 * @param {string} detailsId - ID de los detalles
 */
function toggleTableRowDetails(button, detailsId) {
    const details = document.getElementById(detailsId);
    if (!details) return;
    
    button.classList.toggle('expanded');
    details.classList.toggle('show');
}

/**
 * Filtrar tabla en tiempo real
 * @param {string} tableId - ID de la tabla
 * @param {string} searchId - ID del input de búsqueda
 * @param {number} columnIndex - Índice de columna a buscar (opcional)
 */
function filterTable(tableId, searchId, columnIndex = -1) {
    const table = document.getElementById(tableId);
    const searchInput = document.getElementById(searchId);
    
    if (!table || !searchInput) return;
    
    const filter = searchInput.value.toLowerCase();
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    let visibleCount = 0;
    
    for (let row of rows) {
        let text = '';
        
        if (columnIndex >= 0) {
            // Buscar en columna específica
            const cells = row.getElementsByTagName('td');
            if (cells[columnIndex]) {
                text = cells[columnIndex].textContent.toLowerCase();
            }
        } else {
            // Buscar en toda la fila
            text = row.textContent.toLowerCase();
        }
        
        if (text.includes(filter)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    }
    
    // Mostrar mensaje si no hay resultados
    const emptyMsg = table.parentElement.querySelector('.table-empty');
    if (emptyMsg) {
        emptyMsg.style.display = visibleCount === 0 ? 'flex' : 'none';
    }
}

/**
 * Ordenar tabla por columna
 * @param {string} tableId - ID de la tabla
 * @param {number} columnIndex - Índice de columna
 * @param {string} type - 'string', 'number', 'date'
 */
function sortTableColumn(tableId, columnIndex, type = 'string') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.getElementsByTagName('tbody')[0];
    const rows = Array.from(tbody.getElementsByTagName('tr'));
    
    rows.sort((a, b) => {
        const aVal = a.cells[columnIndex].textContent.trim();
        const bVal = b.cells[columnIndex].textContent.trim();
        
        if (type === 'number') {
            return parseFloat(aVal) - parseFloat(bVal);
        } else if (type === 'date') {
            return new Date(aVal) - new Date(bVal);
        } else {
            return aVal.localeCompare(bVal);
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// =====================================================
// FORMULARIOS AVANZADOS
// =====================================================

/**
 * Serializar formulario a JSON
 * @param {HTMLFormElement|string} form - Formulario o ID
 * @returns {object}
 */
function serializeFormToJSON(form) {
    const formEl = typeof form === 'string' ? 
        document.getElementById(form) : form;
    
    if (!formEl) return {};
    
    const formData = new FormData(formEl);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        if (key in data) {
            // Convertir a array si hay duplicados
            if (!Array.isArray(data[key])) {
                data[key] = [data[key]];
            }
            data[key].push(value);
        } else {
            data[key] = value;
        }
    }
    
    return data;
}

/**
 * Descargar archivo
 * @param {string} url - URL del archivo
 * @param {string} filename - Nombre del archivo
 */
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// =====================================================
// UTILIDADES DE TIEMPO
// =====================================================

/**
 * Formatear fecha
 * @param {Date|string} date
 * @param {string} format - 'short', 'long', 'datetime'
 * @returns {string}
 */
function formatDate(date, format = 'short') {
    const d = typeof date === 'string' ? new Date(date) : date;
    
    const options = {
        short: { year: 'numeric', month: '2-digit', day: '2-digit' },
        long: { year: 'numeric', month: 'long', day: 'numeric' },
        datetime: { year: 'numeric', month: '2-digit', day: '2-digit', 
                   hour: '2-digit', minute: '2-digit' }
    };
    
    return d.toLocaleDateString('es-ES', options[format] || options.short);
}

/**
 * Tiempo relativo (hace 2 horas, hace 3 días, etc)
 * @param {Date|string} date
 * @returns {string}
 */
function formatRelativeTime(date) {
    const d = typeof date === 'string' ? new Date(date) : date;
    const now = new Date();
    const diff = now - d;
    
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (seconds < 60) return 'Hace un momento';
    if (minutes < 60) return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
    if (hours < 24) return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;
    if (days < 7) return `Hace ${days} día${days > 1 ? 's' : ''}`;
    
    return formatDate(d, 'short');
}

// =====================================================
// DEBOUNCE Y THROTTLE
// =====================================================

/**
 * Debounce - esperar a que termine de escribir
 * @param {function} func - Función a ejecutar
 * @param {number} delay - Tiempo en ms
 * @returns {function}
 */
function debounce(func, delay = 300) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

/**
 * Throttle - limitar ejecución
 * @param {function} func - Función a ejecutar
 * @param {number} limit - Tiempo en ms
 * @returns {function}
 */
function throttle(func, limit = 300) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// =====================================================
// STORAGE LOCAL
// =====================================================

/**
 * Guardar en localStorage
 * @param {string} key
 * @param {any} value
 */
function saveToStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (error) {
        console.error('Error saving to localStorage:', error);
    }
}

/**
 * Leer de localStorage
 * @param {string} key
 * @param {any} defaultValue
 * @returns {any}
 */
function getFromStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
        console.error('Error reading from localStorage:', error);
        return defaultValue;
    }
}

/**
 * Remover de localStorage
 * @param {string} key
 */
function removeFromStorage(key) {
    localStorage.removeItem(key);
}

// =====================================================
// INICIALIZACIÓN AL CARGAR
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    // Buscar elementos con data-toggle="tooltip" y activar tooltips
    const tooltips = document.querySelectorAll('[data-toggle="tooltip"]');
    tooltips.forEach(el => {
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'popover';
            tooltip.textContent = this.getAttribute('title') || 'Tooltip';
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = (rect.left + rect.width / 2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.classList.add('show');
            
            this.addEventListener('mouseleave', () => tooltip.remove());
        });
    });
});

// Exportar funciones para módulos (si aplica)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showToast, showAlert, showConfirmDialog,
        validateEmail, validatePassword, togglePasswordVisibility,
        markInvalid, markValid, clearValidation,
        showLoadingSpinner, hideLoadingSpinner, fetchWithHandler,
        toggleClass, addClass, removeClass,
        toggleTableRowDetails, filterTable, sortTableColumn,
        serializeFormToJSON, downloadFile,
        formatDate, formatRelativeTime,
        debounce, throttle,
        saveToStorage, getFromStorage, removeFromStorage
    };
}
