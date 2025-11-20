<?php
/**
 * Partial: Gestión de archivos de material
 * Variables esperadas: $material, $archivos
 */

$materialId = $material['id'] ?? 0;
$archivos = isset($archivos) ? $archivos : [];
?>

<div class="card mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="bi bi-paperclip"></i> Archivos Adjuntos
        </h5>
    </div>
    <div class="card-body">
        <!-- Área de carga -->
        <div class="mb-4">
            <label class="form-label">Subir nuevo archivo (máx. 10MB)</label>
            <div class="input-group">
                <input type="file" id="input-archivo-<?= $materialId ?>" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv">
                <button class="btn btn-outline-primary" type="button" onclick="subirArchivo(<?= $materialId ?>)">
                    <i class="bi bi-cloud-upload"></i> Subir
                </button>
            </div>
            <small class="form-text text-muted d-block mt-2">
                <strong>Formatos permitidos:</strong> PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV
            </small>
        </div>

        <!-- Lista de archivos -->
        <div id="lista-archivos-<?= $materialId ?>">
            <?php if (empty($archivos)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay archivos adjuntos aún.
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($archivos as $archivo): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center" id="archivo-<?= $archivo['id'] ?>">
                            <div>
                                <div class="fw-bold">
                                    <i class="bi bi-file-earmark"></i>
                                    <?= htmlspecialchars($archivo['nombre_original']) ?>
                                </div>
                                <small class="text-muted">
                                    <?= formatearBytes($archivo['tamaño']) ?> • 
                                    <?= date('d/m/Y H:i', strtotime($archivo['fecha_creacion'])) ?>
                                </small>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="/<?= $archivo['nombre_archivo'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Descargar">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArchivo(<?= $archivo['id'] ?>)" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Indicador de carga -->
        <div id="progreso-carga-<?= $materialId ?>" style="display:none;" class="mt-3">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
    </div>
</div>

<script>
function subirArchivo(materialId) {
    const input = document.getElementById(`input-archivo-${materialId}`);
    const archivo = input.files[0];

    if (!archivo) {
        alert('Por favor selecciona un archivo');
        return;
    }

    const formData = new FormData();
    formData.append('material_id', materialId);
    formData.append('archivo', archivo);

    const progreso = document.getElementById(`progreso-carga-${materialId}`);
    progreso.style.display = 'block';

    fetch(`${BASE_URL}/?url=materiales/subirArchivo`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        progreso.style.display = 'none';
        if (data.success) {
            alert(data.message);
            recargarArchivos(materialId);
            input.value = '';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        progreso.style.display = 'none';
        console.error('Error:', error);
        alert('Error al subir el archivo');
    });
}

function eliminarArchivo(archivoId) {
    if (!confirm('¿Eliminar este archivo? Esta acción no se puede deshacer.')) {
        return;
    }

    fetch(`${BASE_URL}/?url=materiales/eliminarArchivo`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${archivoId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`archivo-${archivoId}`).remove();
            recargarArchivos(MATERIAL_ID);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el archivo');
    });
}

function recargarArchivos(materialId) {
    fetch(`${BASE_URL}/?url=materiales/obtenerArchivos&material_id=${materialId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.archivos.length > 0) {
            let html = '<div class="list-group">';
            data.archivos.forEach(archivo => {
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center" id="archivo-${archivo.id}">
                        <div>
                            <div class="fw-bold">
                                <i class="bi bi-file-earmark"></i>
                                ${escapeHtml(archivo.nombre_original)}
                            </div>
                            <small class="text-muted">
                                ${formatearBytes(archivo.tamaño)} • 
                                ${new Date(archivo.fecha_creacion).toLocaleDateString('es-ES')}
                            </small>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="/${archivo.nombre_archivo}" class="btn btn-sm btn-outline-primary" target="_blank" title="Descargar">
                                <i class="bi bi-download"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArchivo(${archivo.id})" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            document.getElementById(`lista-archivos-${materialId}`).innerHTML = html;
        }
    })
    .catch(error => console.error('Error al recargar archivos:', error));
}

function formatearBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
