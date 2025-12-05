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
            
            // Formatear valor de compra
            const valorCompra = material.valor_compra 
                ? '$ ' + parseFloat(material.valor_compra).toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                : 'No especificado';
            
            // Formatear fecha de adquisición
            const fechaAdquisicion = material.fecha_adquisicion 
                ? new Date(material.fecha_adquisicion + 'T00:00:00').toLocaleDateString('es-CO')
                : 'No especificada';
            
            detallesDiv.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-4">
                        <p class="mb-2"><strong>Código:</strong></p>
                        <p><code class="bg-light p-2 rounded">${escapeHtml(material.codigo)}</code></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-2"><strong>Nodo:</strong></p>
                        <p><span class="badge bg-secondary">${escapeHtml(material.nodo_nombre || 'Sin nodo')}</span></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-2"><strong>Línea:</strong></p>
                        <p><span class="badge bg-primary">${escapeHtml(material.linea_nombre || 'Sin línea')}</span></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <p class="mb-2"><strong>Nombre:</strong></p>
                        <p class="h5">${escapeHtml(material.nombre)}</p>
                    </div>
                </div>

                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Información del Producto</h6>
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Fecha de Adquisición</small>
                                <strong>${fechaAdquisicion}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Categoría</small>
                                <strong>${escapeHtml(material.categoria || 'No especificada')}</strong>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Presentación</small>
                                <strong>${escapeHtml(material.presentacion || 'N/A')}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Medida</small>
                                <strong>${escapeHtml(material.medida || 'N/A')}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Cantidad</small>
                                <strong class="text-primary">${parseInt(material.cantidad)}</strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Valor de Compra</small>
                                <strong>${valorCompra}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Proveedor</small>
                                <strong>${escapeHtml(material.proveedor || 'No especificado')}</strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Marca</small>
                                <strong>${escapeHtml(material.marca || 'No especificada')}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <p class="mb-2"><strong>Estado:</strong></p>
                        <p><span class="badge ${material.estado == 1 ? 'bg-success' : 'bg-danger'}">${material.estado == 1 ? 'Activo' : 'Inactivo'}</span></p>
                    </div>
                </div>

                <hr>

                <div class="text-muted small">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Creado:</strong> ${new Date(material.fecha_creacion).toLocaleString('es-CO')}
                        </div>
                        <div class="col-md-6">
                            <strong>Última actualización:</strong> ${new Date(material.fecha_actualizacion).toLocaleString('es-CO')}
                        </div>
                    </div>
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
        const response = await fetch(`${window.BASE_URL}/?url=materialeshistorial/registrarMovimiento`, {
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

/* =========================================================
   IMPORTACIÓN Y EXPORTACIÓN DE MATERIALES
========================================================== */

/**
 * Evento del botón Exportar
 */
document.addEventListener('DOMContentLoaded', () => {
    const btnExportar = document.getElementById('btn-exportar');
    if (btnExportar) {
        btnExportar.addEventListener('click', exportarMateriales);
    }

    const btnImportar = document.getElementById('btn-importar');
    if (btnImportar) {
        btnImportar.addEventListener('click', importarMateriales);
    }

    const inputArchivo = document.getElementById('archivo-importar');
    if (inputArchivo) {
        inputArchivo.addEventListener('change', mostrarPreviewArchivo);
    }
});

/**
 * Mostrar vista previa del archivo
 */
function mostrarPreviewArchivo(e) {
    const archivo = e.target.files[0];
    if (!archivo) return;

    const previewDiv = document.getElementById('import-preview');
    const previewContent = document.getElementById('preview-content');
    
    // Si es XLSX, no mostrar preview (es binario)
    const extension = archivo.name.split('.').pop().toLowerCase();
    if (extension === 'xlsx' || extension === 'xls') {
        previewContent.textContent = `Archivo Excel seleccionado: ${archivo.name}\nTamaño: ${(archivo.size / 1024).toFixed(2)} KB\n\nLos archivos Excel se procesarán automáticamente al importar.`;
        previewDiv.style.display = 'block';
        return;
    }

    // Para CSV y TXT, mostrar preview
    const reader = new FileReader();
    reader.onload = (event) => {
        const contenido = event.target.result;
        const lineas = contenido.split('\n').slice(0, 5); // Primeras 5 líneas
        
        previewContent.textContent = lineas.join('\n');
        previewDiv.style.display = 'block';
    };
    reader.readAsText(archivo);
}

/**
 * Descargar materiales en Excel (CSV)
 */
function exportarMateriales() {
    const btn = document.getElementById('btn-exportar');
    const textoOriginal = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Descargando...';

    fetch(`${window.BASE_URL}/?url=materialesexport/exportar`, {
        method: 'GET',
        headers: {
            'Accept': 'text/csv'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en la descarga');
        return response.blob();
    })
    .then(blob => {
        // Crear enlace temporal para descargar
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `materiales_${new Date().toISOString().slice(0,10)}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        // Restaurar botón
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        
        // Mostrar notificación
        mostrarNotificacion('Archivo descargado exitosamente', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        mostrarNotificacion('Error al descargar el archivo', 'danger');
    });
}

/**
 * Importar materiales desde CSV
 */
function importarMateriales() {
    const fileInput = document.getElementById('archivo-importar');
    const archivo = fileInput.files[0];

    if (!archivo) {
        mostrarNotificacion('Por favor selecciona un archivo', 'warning');
        return;
    }

    const btn = document.getElementById('btn-importar');
    const textoOriginal = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Importando...';

    // Limpiar mensajes previos
    document.getElementById('import-errors').style.display = 'none';
    document.getElementById('import-success').style.display = 'none';
    document.getElementById('import-warning-list').innerHTML = '';

    const formData = new FormData();
    formData.append('archivo', archivo);

    fetch(`${window.BASE_URL}/?url=materialesimport/importar`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;

        if (data.success) {
            // Mostrar mensaje de éxito
            const successDiv = document.getElementById('import-success');
            const successMsg = document.getElementById('import-success-message');
            successMsg.innerHTML = `
                <i class="bi bi-check-circle"></i>
                <strong>${data.message}</strong><br>
                <small>Se crearon ${data.materiales_creados} de ${data.total_procesados} materiales.</small>
            `;
            successDiv.style.display = 'block';

            // Mostrar advertencias si las hay
            if (data.advertencias && data.errores_por_linea) {
                const warningsDiv = document.getElementById('import-warnings');
                const warningsList = document.getElementById('import-warning-list');
                
                warningsList.innerHTML = '';
                Object.entries(data.errores_por_linea).forEach(([linea, error]) => {
                    const li = document.createElement('li');
                    li.textContent = `Línea ${parseInt(linea) + 1}: ${escapeHtml(error)}`;
                    warningsList.appendChild(li);
                });

                warningsDiv.style.display = 'block';
            }

            // Resetear formulario después de 2 segundos
            setTimeout(() => {
                document.getElementById('formularioImportar').reset();
                document.getElementById('import-preview').style.display = 'none';
                document.getElementById('import-success').style.display = 'none';
                
                // Recargar tabla
                window.location.href = `${window.BASE_URL}/?url=materiales/index`;
            }, 2000);
        } else {
            // Mostrar errores
            const errorsDiv = document.getElementById('import-errors');
            const errorsList = document.getElementById('import-error-list');
            
            errorsList.innerHTML = '';
            
            if (typeof data.message === 'string') {
                const li = document.createElement('li');
                li.textContent = escapeHtml(data.message);
                errorsList.appendChild(li);
            }

            // Mostrar encabezados esperados si está disponible
            if (data.encabezados_esperados) {
                const li = document.createElement('li');
                li.innerHTML = `<strong>Encabezados esperados:</strong> ${escapeHtml(data.encabezados_esperados.join(', '))}`;
                errorsList.appendChild(li);
            }

            errorsDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = textoOriginal;

        const errorsDiv = document.getElementById('import-errors');
        const errorsList = document.getElementById('import-error-list');
        const li = document.createElement('li');
        li.textContent = 'Error de conexión: ' + error.message;
        errorsList.innerHTML = '';
        errorsList.appendChild(li);
        errorsDiv.style.display = 'block';
    });
}

/**
 * Mostrar notificación (si existe el sistema)
 */
function mostrarNotificacion(mensaje, tipo = 'info') {
    // Intentar usar Bootstrap Toast si existe
    const toast = document.createElement('div');
    toast.className = `alert alert-${tipo} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        ${mensaje}
    `;
    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 4000);
}

