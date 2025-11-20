/**
 * Sistema de Filtración - Historial de Auditoría
 */

document.addEventListener('DOMContentLoaded', function() {
    const btnFiltrar = document.getElementById('btn-filtrar');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    
    // Variables de estado
    let paginaActual = parseInt(document.getElementById('pagina-actual').textContent.split('/')[0]);
    let totalPages = parseInt(document.getElementById('pagina-actual').textContent.split('/')[1]);
    
    // Evento: Filtrar
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function() {
            aplicarFiltros(1); // Reiniciar a página 1
        });
    }
    
    // Evento: Página anterior
    if (btnPrev) {
        btnPrev.addEventListener('click', function() {
            if (paginaActual > 1) {
                aplicarFiltros(paginaActual - 1);
            }
        });
    }
    
    // Evento: Página siguiente
    if (btnNext) {
        btnNext.addEventListener('click', function() {
            if (paginaActual < totalPages) {
                aplicarFiltros(paginaActual + 1);
            }
        });
    }
    
    // Función para aplicar filtros
    function aplicarFiltros(pagina = 1) {
        const usuarioId = document.getElementById('filtro-usuario').value;
        const accion = document.getElementById('filtro-accion').value;
        const fechaInicio = document.getElementById('filtro-fecha-inicio').value;
        const fechaFin = document.getElementById('filtro-fecha-fin').value;
        
        // Construir URL con parámetros
        let url = BASE_URL + '/?url=audit/buscar';
        url += '&page=' + pagina;
        
        if (usuarioId) url += '&usuario_id=' + encodeURIComponent(usuarioId);
        if (accion) url += '&accion=' + encodeURIComponent(accion);
        if (fechaInicio) url += '&fecha_inicio=' + encodeURIComponent(fechaInicio);
        if (fechaFin) url += '&fecha_fin=' + encodeURIComponent(fechaFin);
        
        // Realizar petición AJAX
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta');
                return response.json();
            })
            .then(data => {
                actualizarTabla(data);
                actualizarPaginacion(data);
                actualizarURL(usuarioId, accion, fechaInicio, fechaFin, pagina);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al filtrar los datos. Intenta nuevamente.');
            });
    }
    
    // Función para actualizar la tabla
    function actualizarTabla(data) {
        const tbody = document.getElementById('historial-body');
        
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No hay cambios registrados.</td></tr>';
            return;
        }
        
        let html = '';
        data.data.forEach((cambio, index) => {
            const acciones = {
                'crear': { clase: 'badge bg-success', texto: 'Creado' },
                'actualizar': { clase: 'badge bg-info', texto: 'Actualizado' },
                'eliminar': { clase: 'badge bg-danger', texto: 'Eliminado' }
            };
            
            const accion = acciones[cambio.accion] || { clase: 'badge bg-secondary', texto: cambio.accion };
            
            const detalles = cambio.detalles ? JSON.parse(cambio.detalles) : {};
            const tieneDetalles = Object.keys(detalles).length > 0;
            
            html += `
                <tr>
                    <td>${cambio.id}</td>
                    <td><small class="text-muted">${formatearFecha(cambio.fecha_creacion)}</small></td>
                    <td>${escapeHtml(cambio.usuario_modificado || 'N/A')}</td>
                    <td><span class="${accion.clase}">${accion.texto}</span></td>
                    <td>
            `;
            
            if (tieneDetalles) {
                html += `
                    <button class="btn btn-sm btn-info" onclick="toggleDetalles(event)">
                        <i class="bi bi-eye"></i> Ver cambios
                    </button>
                    <div class="detalles-modal" style="display:none; margin-top:10px; padding:12px; background:#f9fafb; border-radius:6px; border-left:3px solid #0d6efd;">
                `;
                
                for (const [campo, valor] of Object.entries(detalles)) {
                    html += `
                        <div style="margin-bottom:8px; padding:8px; background:white; border-radius:4px;">
                            <strong style="color:#0d6efd;">${escapeHtml(campo)}</strong>
                            <div style="margin-top:4px; font-size:0.9rem;">
                    `;
                    
                    if (typeof valor === 'object' && valor !== null) {
                        const anterior = valor.anterior || '-';
                        const nuevo = valor.nuevo || '-';
                        html += `
                            <div style="display:flex; gap:12px; margin-top:4px;">
                                <div>
                                    <span style="color:#666; font-size:0.85rem;">Anterior:</span><br>
                                    <span style="color:#dc3545;">${escapeHtml(anterior)}</span>
                                </div>
                                <div style="border-left:1px solid #ddd; padding-left:12px;">
                                    <span style="color:#666; font-size:0.85rem;">Nuevo:</span><br>
                                    <span style="color:#198754;">${escapeHtml(nuevo)}</span>
                                </div>
                            </div>
                        `;
                    } else {
                        html += `<div>${escapeHtml(String(valor))}</div>`;
                    }
                    
                    html += `
                            </div>
                        </div>
                    `;
                }
                
                html += `
                    </div>
                `;
            } else {
                html += `<span class="text-muted">Sin detalles</span>`;
            }
            
            html += `
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    // Función para actualizar paginación
    function actualizarPaginacion(data) {
        paginaActual = data.page;
        totalPages = data.totalPages;
        
        document.getElementById('pagina-actual').textContent = `${paginaActual} / ${totalPages}`;
        
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');
        
        if (btnPrev) btnPrev.disabled = paginaActual <= 1;
        if (btnNext) btnNext.disabled = paginaActual >= totalPages;
    }
    
    // Función para actualizar URL (para que se vea en la barra del navegador)
    function actualizarURL(usuarioId, accion, fechaInicio, fechaFin, pagina) {
        let url = `${BASE_URL}/?url=audit/historial&page=${pagina}`;
        
        if (usuarioId) url += `&usuario_id=${encodeURIComponent(usuarioId)}`;
        if (accion) url += `&accion=${encodeURIComponent(accion)}`;
        if (fechaInicio) url += `&fecha_inicio=${encodeURIComponent(fechaInicio)}`;
        if (fechaFin) url += `&fecha_fin=${encodeURIComponent(fechaFin)}`;
        
        window.history.replaceState({}, document.title, url);
    }
    
    // Función para formatear fechas
    function formatearFecha(fecha) {
        if (!fecha) return '';
        const date = new Date(fecha);
        return date.toLocaleDateString('es-CO') + ' ' + date.toLocaleTimeString('es-CO', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
    
    // Función para escapar HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    
    // Función global para toggle detalles
    window.toggleDetalles = function(event) {
        event.preventDefault();
        const btn = event.target.closest('button');
        if (!btn) return;
        
        const modal = btn.nextElementSibling;
        if (modal && modal.classList.contains('detalles-modal')) {
            modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
        }
    };
});
