/**
 * Gestión de Materiales - JavaScript
 */

let modalMovimiento = null;
let movimientoActual = { id: null, tipo: null, nombre: null };

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar modales de Bootstrap
    modalMovimiento = new bootstrap.Modal(document.getElementById('modalMovimiento') || document.createElement('div'));

    // Event listeners para búsqueda y filtros
    // Solo aplicar si estamos en la página de índice de materiales (NO en historial)
    const busquedaInput = document.getElementById('busqueda');
    const filtroLinea = document.getElementById('filtro-linea');
    const filtroEstado = document.getElementById('filtro-estado');
    
    // Solo procesar el botón Limpiar de la página de índice
    const btnLimpiarIndice = busquedaInput ? document.getElementById('btn-limpiar') : null;

    if (busquedaInput) {
        busquedaInput.addEventListener('keyup', () => aplicarFiltros());
    }
    if (filtroLinea) {
        filtroLinea.addEventListener('change', () => aplicarFiltros());
    }
    if (filtroEstado) {
        filtroEstado.addEventListener('change', () => aplicarFiltros());
    }
    if (btnLimpiarIndice) {
        btnLimpiarIndice.addEventListener('click', () => limpiarFiltros());
    }

    // Event listeners para acciones de tabla
    document.querySelectorAll('.btn-ver').forEach(btn => {
        btn.addEventListener('click', (e) => verDetalles(e.target.closest('.btn-ver').dataset.id));
    });

    document.querySelectorAll('.btn-entrada').forEach(btn => {
        btn.addEventListener('click', (e) => abrirModalMovimiento(e.target.closest('.btn-entrada').dataset.id, 'entrada'));
    });

    document.querySelectorAll('.btn-salida').forEach(btn => {
        btn.addEventListener('click', (e) => abrirModalMovimiento(e.target.closest('.btn-salida').dataset.id, 'salida'));
    });

    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', (e) => confirmarEliminar(e.target.closest('.btn-eliminar').dataset.id));
    });

    // Guardar movimiento
    const btnGuardarMov = document.getElementById('btn-guardar-movimiento');
    if (btnGuardarMov) {
        btnGuardarMov.addEventListener('click', guardarMovimiento);
    }
});

/**
 * Aplicar filtros a la tabla
 */
function aplicarFiltros() {
    const busqueda = (document.getElementById('busqueda')?.value || '').toLowerCase();
    const linea = document.getElementById('filtro-linea')?.value || '';
    const estado = document.getElementById('filtro-estado')?.value || '';

    const params = new URLSearchParams();
    if (busqueda) params.append('busqueda', busqueda);
    if (linea) params.append('linea_id', linea);
    if (estado !== '') params.append('estado', estado);

    window.location.href = `${window.BASE_URL}/?url=materiales/index&${params.toString()}`;
}

/**
 * Limpiar filtros
 */
function limpiarFiltros() {
    window.location.href = `${window.BASE_URL}/?url=materiales/index`;
}

/**
 * Ver detalles del material
 */
async function verDetalles(materialId) {
    const detallesDiv = document.getElementById('detalles-content');
    if (!detallesDiv) return;

    detallesDiv.innerHTML = '<p class="text-center text-muted">Cargando...</p>';

    try {
        const response = await fetch(`${window.BASE_URL}/?url=materiales/obtenerDetalles&id=${materialId}`);
        const data = await response.json();

        if (data.success) {
            const material = data.material;
            detallesDiv.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Código:</strong> <code>${escapeHtml(material.codigo)}</code></p>
                        <p><strong>Nombre:</strong> ${escapeHtml(material.nombre)}</p>
                        <p><strong>Línea:</strong> <span class="badge bg-primary">${escapeHtml(material.linea_nombre)}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Cantidad:</strong> <strong>${parseInt(material.cantidad)}</strong> unidades</p>
                        <p><strong>Estado:</strong> <span class="badge ${material.estado == 1 ? 'bg-success' : 'bg-danger'}">${material.estado == 1 ? 'Activo' : 'Inactivo'}</span></p>
                    </div>
                </div>
                <div class="mb-3">
                    <p><strong>Descripción:</strong></p>
                    <p class="text-muted">${escapeHtml(material.descripcion || 'Sin descripción')}</p>
                </div>
                <div class="text-muted small">
                    <p>Creado: ${new Date(material.fecha_creacion).toLocaleString()}</p>
                    <p>Última actualización: ${new Date(material.fecha_actualizacion).toLocaleString()}</p>
                </div>
            `;
        } else {
            detallesDiv.innerHTML = '<p class="text-danger">Error al cargar los detalles.</p>';
        }
    } catch (error) {
        detallesDiv.innerHTML = '<p class="text-danger">Error al cargar los detalles.</p>';
    }

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
    modal.show();
}

/**
 * Abrir modal de movimiento (entrada/salida)
 */
function abrirModalMovimiento(materialId, tipo) {
    const row = document.querySelector(`.material-row[data-id="${materialId}"]`);
    if (!row) return;

    const nombreMaterial = row.querySelector('td:nth-child(3)').textContent.trim();
    const cantidadActual = row.querySelector('td:nth-child(6)').textContent.trim();

    // Llenar datos del modal
    document.getElementById('movimiento-titulo').textContent = `Registrar ${tipo === 'entrada' ? 'Entrada' : 'Salida'} de Inventario`;
    document.getElementById('mov-material').value = `${nombreMaterial} (Actual: ${cantidadActual})`;
    document.getElementById('mov-cantidad').value = '';
    document.getElementById('mov-descripcion').value = '';
    document.getElementById('movimiento-errors').style.display = 'none';

    // Guardar datos del movimiento
    movimientoActual = { id: materialId, tipo: tipo, nombre: nombreMaterial };

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalMovimiento'));
    modal.show();
}

/**
 * Guardar movimiento de inventario
 */
async function guardarMovimiento() {
    const cantidad = parseInt(document.getElementById('mov-cantidad').value);
    const descripcion = document.getElementById('mov-descripcion').value;
    const erroresDiv = document.getElementById('movimiento-errors');

    // Validar
    if (isNaN(cantidad) || cantidad <= 0) {
        erroresDiv.innerHTML = 'La cantidad debe ser un número entero positivo.';
        erroresDiv.style.display = 'block';
        return;
    }

    const formData = new FormData();
    formData.append('id', movimientoActual.id);
    formData.append('tipo', movimientoActual.tipo);
    formData.append('cantidad', cantidad);
    formData.append('descripcion', descripcion);

    try {
        const response = await fetch(`${window.BASE_URL}/?url=materiales/registrarMovimiento`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalMovimiento'));
            if (modal) modal.hide();

            // Mostrar mensaje de éxito
            alert(data.message || 'Movimiento registrado exitosamente');

            // Recargar página
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            erroresDiv.innerHTML = '<strong>Errores:</strong><ul>';
            if (Array.isArray(data.errors)) {
                data.errors.forEach(err => {
                    erroresDiv.innerHTML += `<li>${err}</li>`;
                });
            } else {
                erroresDiv.innerHTML += `<li>${data.errors || data.message || 'Error desconocido'}</li>`;
            }
            erroresDiv.innerHTML += '</ul>';
            erroresDiv.style.display = 'block';
        }
    } catch (error) {
        erroresDiv.innerHTML = 'Error al registrar el movimiento. Intenta de nuevo.';
        erroresDiv.style.display = 'block';
    }
}

/**
 * Confirmar y eliminar material
 */
function confirmarEliminar(materialId) {
    const row = document.querySelector(`.material-row[data-id="${materialId}"]`);
    if (!row) return;

    const nombreMaterial = row.querySelector('td:nth-child(3)').textContent.trim();

    if (!confirm(`¿Estás seguro de que deseas eliminar el material "${nombreMaterial}"?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('id', materialId);

    fetch(`${window.BASE_URL}/?url=materiales/eliminar`, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Material eliminado exitosamente');
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                alert(data.message || 'Error al eliminar el material');
            }
        })
        .catch(error => {
            alert('Error al eliminar el material');
        });
}

/**
 * Escapar HTML para evitar XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
