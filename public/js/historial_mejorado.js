/**
 * Historial Mejorado - Funcionalidades avanzadas para el módulo de auditoría
 * Enfocado en RENDIMIENTO y PREVENCIÓN DE PARPADEO
 */

document.addEventListener('DOMContentLoaded', function() {
    inicializarHistorialMejorado();
});

function inicializarHistorialMejorado() {
    // Configurar filtros para envío automático
    const filtros = ['filtro-usuario', 'filtro-accion', 'filtro-fecha-inicio', 'filtro-fecha-fin'];
    
    filtros.forEach(id => {
        const elem = document.getElementById(id);
        if (elem) {
            elem.addEventListener('change', function() {
                aplicarFiltros();
            });
        }
    });
}

/**
 * Aplicar filtros de forma segura
 */
function aplicarFiltros() {
    const usuario = document.getElementById('filtro-usuario')?.value || '';
    const accion = document.getElementById('filtro-accion')?.value || '';
    const fecha_inicio = document.getElementById('filtro-fecha-inicio')?.value || '';
    const fecha_fin = document.getElementById('filtro-fecha-fin')?.value || '';
    
    const url = new URL(window.location);
    url.searchParams.set('url', 'audit/historial');
    if (usuario) url.searchParams.set('usuario_id', usuario);
    else url.searchParams.delete('usuario_id');
    if (accion) url.searchParams.set('accion', accion);
    else url.searchParams.delete('accion');
    if (fecha_inicio) url.searchParams.set('fecha_inicio', fecha_inicio);
    else url.searchParams.delete('fecha_inicio');
    if (fecha_fin) url.searchParams.set('fecha_fin', fecha_fin);
    else url.searchParams.delete('fecha_fin');
    
    window.location.href = url.toString();
}

/**
 * Abrir detalles en modal emergente (no ventana nueva)
 */
function abrirModalDetalles(cambioId) {
    const container = document.getElementById('modal-detalles-' + cambioId);
    if (!container) return;

    // Crear modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
    
    // Crear contenedor del modal
    const modalDiv = document.createElement('div');
    modalDiv.className = 'modal-emergente';
    modalDiv.style.cssText = `
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        max-width: 1000px;
        width: 90%;
        max-height: 85vh;
        overflow-y: auto;
        position: relative;
    `;
    
    // Copiar contenido
    modalDiv.innerHTML = container.innerHTML;
    
    // Agregar botón de cerrar
    const closeBtn = document.createElement('button');
    closeBtn.className = 'btn btn-close';
    closeBtn.style.cssText = `
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10000;
    `;
    closeBtn.onclick = () => {
        overlay.remove();
    };
    modalDiv.appendChild(closeBtn);
    
    overlay.appendChild(modalDiv);
    document.body.appendChild(overlay);
    
    // Cerrar al hacer click en el overlay
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.remove();
        }
    });
    
    // Cerrar con tecla Escape
    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            overlay.remove();
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
}

/**
 * Limpiar filtros completamente
 */
function limpiarFiltrosHistorial() {
    const form = document.getElementById('form-filtros');
    if (form) {
        form.reset();
        aplicarFiltros();
    }
}

// Exportar función para uso externo
window.historicalUtils = {
    abrirModalDetalles: abrirModalDetalles,
    limpiarFiltrosHistorial: limpiarFiltrosHistorial,
    aplicarFiltros: aplicarFiltros
};



