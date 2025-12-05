<?php

/**
 * Partial: Gestión de archivos de material
 * Variables esperadas: $material, $archivos.
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
            <?php if (empty($archivos)) : ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay archivos adjuntos aún.
                </div>
            <?php else : ?>
                <div class="list-group">
                    <?php foreach ($archivos as $archivo) : ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center" id="archivo-<?= $archivo['id'] ?>">
                            <div>
                                <div class="fw-bold">
                                    <i class="bi bi-file-earmark"></i>
                                    <?= htmlspecialchars($archivo['nombre_original']) ?>
                                </div>
                                <small class="text-muted">
                                    <?= formatearBytes($archivo['tamano'] ?? $archivo['tamaño'] ?? 0) ?> • 
                                    <?= date('d/m/Y H:i', strtotime($archivo['fecha_creacion'])) ?> •
                                    Por: <strong><?= htmlspecialchars($archivo['usuario_nombre'] ?? $archivo['usuario_correo'] ?? 'N/A') ?></strong>
                                </small>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="<?= BASE_URL ?>/<?= $archivo['nombre_archivo'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Descargar">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArchivo(<?= $archivo['id'] ?>, <?= $materialId ?>)" title="Eliminar">
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
// Asegurar que window.BASE_URL está disponible (fallback si header no se cargó)
if (typeof window.BASE_URL === 'undefined' || !window.BASE_URL) {
    let protocol = window.location.protocol;
    const host = window.location.host;
    
    // Para ngrok, forzar HTTPS
    if (host.includes('ngrok') && protocol === 'http:') {
        protocol = 'https:';
    }
    
    window.BASE_URL = protocol + '//' + host + '/proyecto_sena/public';
    console.warn('BASE_URL no estaba definido, se estableció fallback:', window.BASE_URL);
} else {
    console.log('BASE_URL ya definido:', window.BASE_URL);
}

function subirArchivo(materialId) {
    // Validar materialId
    if (!materialId || materialId <= 0) {
        alert('Error: Material ID inválido (' + materialId + '). Por favor recarga la página.');
        console.error('Material ID inválido:', materialId);
        return;
    }
    
    const input = document.getElementById(`input-archivo-${materialId}`);
    if (!input) {
        alert('Error: No se encontró el campo de archivo');
        console.error('Input no encontrado para material:', materialId);
        return;
    }
    
    const archivo = input.files[0];
    if (!archivo) {
        alert('Por favor selecciona un archivo');
        return;
    }

    // Validar tamaño en cliente (máximo 10MB)
    if (archivo.size > 10 * 1024 * 1024) {
        alert('El archivo supera el tamaño máximo de 10MB');
        return;
    }

    const progreso = document.getElementById(`progreso-carga-${materialId}`);
    if (progreso) {
        progreso.style.display = 'block';
    }

    // Leer archivo como base64 (evita problemas de multipart con ngrok)
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const base64Data = e.target.result.split(',')[1]; // Eliminar "data:...;base64," prefix
        
        // Construir JSON payload
        const payload = {
            material_id: materialId,
            archivo_nombre: archivo.name,
            archivo_tipo: archivo.type,
            archivo_tamaño: archivo.size,
            archivo_data: base64Data
        };

        // USAR ENDPOINT DEL CONTROLADOR (MaterialesController->subirArchivo)
        const urlSubida = window.BASE_URL + '/materialesarchivos/subir';
        
        console.log('Iniciando carga base64 (materialesarchivos/subir):', {
            url: urlSubida,
            materialId: materialId,
            archivo: archivo.name,
            tamaño: archivo.size,
            dataLength: base64Data.length
        });

        // Usar timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);

        // Enviar como JSON
        fetch(urlSubida, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=utf-8',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload),
            signal: controller.signal,
            credentials: 'include'
        })
        .then(response => {
            clearTimeout(timeoutId);
            console.log('Respuesta del servidor:', response.status);
            
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error HTTP:', response.status, text);
                    throw new Error(`Error HTTP ${response.status}`);
                });
            }
            return response.json().catch(e => {
                console.error('Error al parsear JSON:', e);
                throw new Error('Respuesta inválida del servidor');
            });
        })
        .then(data => {
            if (progreso) {
                progreso.style.display = 'none';
            }
            
            if (data.success) {
                console.log('Éxito:', data.message);
                alert(data.message || 'Archivo subido exitosamente');
                recargarArchivos(materialId);
                input.value = '';
            } else {
                console.warn('Error del servidor:', data.message);
                alert('Error: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            if (progreso) {
                progreso.style.display = 'none';
            }
            
            console.error('Error completo:', error);
            
            if (error.name === 'AbortError') {
                alert('Tiempo de espera agotado. El servidor tardó demasiado en responder.');
            } else {
                alert('Error al subir el archivo:\n' + error.message);
            }
        });
    };
    
    reader.onerror = function() {
        if (progreso) {
            progreso.style.display = 'none';
        }
        alert('Error al leer el archivo');
    };
    
    // Leer archivo como data URL (base64)
    reader.readAsDataURL(archivo);
}

function eliminarArchivo(archivoId, materialId) {
    if (!confirm('¿Eliminar este archivo? Esta acción no se puede deshacer.')) {
        return;
    }

    let urlEliminar = window.BASE_URL + '/materialesarchivos/eliminar';
    if (urlEliminar.includes('ngrok')) {
        urlEliminar = urlEliminar.replace('http://', 'https://');
    }

    fetch(urlEliminar, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${archivoId}`,
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`archivo-${archivoId}`).remove();
            recargarArchivos(materialId);
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
    let urlObtener = window.BASE_URL + '/materialesarchivos/listar?material_id=' + materialId;
    if (urlObtener.includes('ngrok')) {
        urlObtener = urlObtener.replace('http://', 'https://');
    }
    
    fetch(urlObtener, {
        credentials: 'include',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.archivos.length > 0) {
            let html = '<div class="list-group">';
            data.archivos.forEach(archivo => {
                const tamanio = archivo.tamano ?? archivo.tamaño ?? 0;
                const usuario = archivo.usuario_nombre ?? archivo.usuario_correo ?? 'N/A';
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center" id="archivo-${archivo.id}">
                        <div>
                            <div class="fw-bold">
                                <i class="bi bi-file-earmark"></i>
                                ${escapeHtml(archivo.nombre_original)}
                            </div>
                            <small class="text-muted">
                                ${formatearBytes(tamanio)} • 
                                ${new Date(archivo.fecha_creacion).toLocaleDateString('es-ES')} •
                                Por: <strong>${escapeHtml(usuario)}</strong>
                            </small>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="${window.BASE_URL}/${archivo.nombre_archivo}" class="btn btn-sm btn-outline-primary" target="_blank" title="Descargar">
                                <i class="bi bi-download"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArchivo(${archivo.id}, ${materialId})" title="Eliminar">
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
