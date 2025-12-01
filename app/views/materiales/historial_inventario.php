<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/auth/login");
    exit;
}
?>

<style>
    tbody tr:only-child td {
        display: table-cell !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-12">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
            <h3 class="mb-0">Historial de Inventario</h3>
            <a href="<?= BASE_URL ?>/materiales/index" class="btn btn-outline-secondary btn-sm w-100 w-sm-auto">
                <i class="bi bi-arrow-left"></i> Volver al Inventario
            </a>
        </div>

        <!-- Filtros (Siempre Visible) -->
        <div class="card mb-3" id="filtros-card">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Material</label>
                        <select id="filtro-material" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($materiales as $mat): ?>
                                <option value="<?= $mat['id'] ?>" <?= !empty($filtros['material_id']) && $filtros['material_id'] == $mat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label">Tipo</label>
                        <select id="filtro-tipo" class="form-select">
                            <option value="">Todos</option>
                            <option value="entrada" <?= $filtros['tipo_movimiento'] === 'entrada' ? 'selected' : '' ?>>Entrada</option>
                            <option value="salida" <?= $filtros['tipo_movimiento'] === 'salida' ? 'selected' : '' ?>>Salida</option>
                            <option value="cambio" <?= $filtros['tipo_movimiento'] === 'cambio' ? 'selected' : '' ?>>Cambio</option>
                            <option value="eliminado" <?= $filtros['tipo_movimiento'] === 'eliminado' ? 'selected' : '' ?>>Eliminado</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" id="filtro-fecha-inicio" class="form-control" value="<?= $filtros['fecha_inicio'] ?>">
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" id="filtro-fecha-fin" class="form-control" value="<?= $filtros['fecha_fin'] ?>">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <button type="button" class="btn btn-outline-secondary w-100" id="btn-limpiar">
                            <i class="bi bi-x-circle"></i> Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Historial -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Material</th>
                            <th>Nodo</th>
                            <th>Línea</th>
                            <th style="width: 100px;">Tipo</th>
                            <th class="text-center" style="width: 80px;">Cantidad</th>
                            <th>Usuario</th>
                            <th>Descripción</th>
                            <th class="text-center" style="width: 100px;">Documentos</th>
                            <th class="text-center" style="width: 120px;">Fecha</th>
                            <th class="text-center" style="width: 60px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="historial-body">
                        <?php if (!empty($historial)): ?>
                            <?php foreach ($historial as $mov): ?>
                                <tr>
                                    <td><?= $mov['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($mov['material_nombre']) ?></strong><br>
                                        <small class="text-muted">
                                            <?php if ($mov['tipo_registro'] === 'movimiento'): ?>
                                                Código: <?= htmlspecialchars($mov['material_id']) ?>
                                            <?php else: ?>
                                                Código: <?= htmlspecialchars($mov['material_codigo'] ?? 'N/A') ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($mov['nodo_nombre'] ?? 'Sin nodo') ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($mov['linea_nombre'] ?? 'Sin línea') ?></span>
                                    </td>
                                    <td>
                                        <?php if ($mov['tipo_registro'] === 'movimiento'): ?>
                                            <?php if ($mov['tipo_movimiento'] === 'entrada'): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-plus-lg"></i> Entrada
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-dash-lg"></i> Salida
                                                </span>
                                            <?php endif; ?>
                                        <?php elseif ($mov['tipo_registro'] === 'cambio'): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-pencil"></i> Cambio
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-dark">
                                                <i class="bi bi-trash"></i> Eliminado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($mov['tipo_registro'] === 'movimiento'): ?>
                                            <strong><?= intval($mov['cantidad']) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($mov['usuario_foto']): ?>
                                                <img src="<?= BASE_URL . '/' . htmlspecialchars($mov['usuario_foto']) ?>" 
                                                     width="28" height="28" class="rounded-circle" style="object-fit: cover;">
                                            <?php else: ?>
                                                <img src="<?= BASE_URL ?>/img/default_user.png" 
                                                     width="28" height="28" class="rounded-circle">
                                            <?php endif; ?>
                                            <small><?= htmlspecialchars($mov['usuario_nombre'] ?? 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($mov['tipo_registro'] === 'movimiento'): ?>
                                            <small class="text-muted"><?= htmlspecialchars($mov['descripcion']) ?></small>
                                        <?php elseif ($mov['tipo_registro'] === 'cambio'): ?>
                                            <small class="text-muted">Propiedades modificadas</small>
                                        <?php else: ?>
                                            <small class="text-muted">Material eliminado del inventario</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary doc-count-badge" id="docs-count-<?= $mov['id'] ?>" data-material-id="<?= $mov['material_id'] ?? 0 ?>" style="cursor: pointer;" title="Documentos adjuntos">
                                            <i class="bi bi-file-earmark"></i> <span class="count-value">0</span>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php $fecha = $mov['tipo_registro'] === 'movimiento' ? $mov['fecha_movimiento'] : ($mov['fecha_cambio'] ?? $mov['fecha_creacion'] ?? date('Y-m-d H:i:s')); ?>
                                            <?= date('d/m/Y', strtotime($fecha)) ?><br>
                                            <span class="text-muted"><?= date('H:i', strtotime($fecha)) ?></span>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($mov['tipo_registro'] === 'movimiento'): ?>
                                            <button class="btn btn-info btn-sm btn-detalles" data-id="<?= $mov['id'] ?>" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        <?php elseif ($mov['tipo_registro'] === 'cambio'): ?>
                                            <button class="btn btn-warning btn-sm btn-detalles-cambio" data-detalles="<?= htmlspecialchars(json_encode(json_decode($mov['detalles'], true))) ?>" title="Ver cambios">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-warning btn-sm btn-detalles-eliminacion" data-detalles="<?= htmlspecialchars(json_encode(json_decode($mov['detalles'], true))) ?>" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">
                                    <i class="bi bi-inbox"></i> No hay movimientos registrados.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Estadísticas del historial -->
            <div class="card-footer bg-light">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <small class="text-muted">
                            <i class="bi bi-clock-history"></i>
                            Total de registros: <strong><?= count($historial) ?></strong>
                        </small>
                    </div>
                    <div class="col-12 col-md-2">
                        <small class="text-muted">
                            <i class="bi bi-plus-lg text-success"></i>
                            Entradas: <strong><?= count(array_filter($historial, fn($m) => ($m['tipo_registro'] ?? null) === 'movimiento' && ($m['tipo_movimiento'] ?? null) === 'entrada')) ?></strong>
                        </small>
                    </div>
                    <div class="col-12 col-md-2">
                        <small class="text-muted">
                            <i class="bi bi-dash-lg text-danger"></i>
                            Salidas: <strong><?= count(array_filter($historial, fn($m) => ($m['tipo_registro'] ?? null) === 'movimiento' && ($m['tipo_movimiento'] ?? null) === 'salida')) ?></strong>
                        </small>
                    </div>
                    <div class="col-12 col-md-2">
                        <small class="text-muted">
                            <i class="bi bi-pencil text-warning"></i>
                            Cambios: <strong><?= count(array_filter($historial, fn($m) => ($m['tipo_registro'] ?? null) === 'cambio')) ?></strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalles del Movimiento -->
<div class="modal fade" id="modalDetallesMovimiento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalles-movimiento-content">
                <p class="text-center text-muted">Cargando...</p>
            </div>
        </div>
    </div>
</div>

<script>
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
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Función para obtener detalles del movimiento
    /**
     * Ver detalles de cambios de material
     */
    function verDetallesCambio(detalles) {
        console.log('=== DETALLES DE CAMBIOS ===');
        console.log('Detalles:', detalles);
        
        const detallesDiv = document.getElementById('detalles-movimiento-content');
        
        if (!detallesDiv) {
            console.error('❌ No se encontró elemento modal');
            return;
        }

        try {
            let cambiosHTML = '';
            let hayCambios = false;

            // Mapear nombres de campos más legibles
            const nombresAmigables = {
                'codigo': 'Código',
                'nombre': 'Nombre',
                'descripcion': 'Descripción',
                'nodo_id': 'Nodo',
                'linea_id': 'Línea',
                'cantidad': 'Cantidad',
                'estado': 'Estado'
            };

            // Iterar sobre cada campo que cambió
            for (const [campo, cambio] of Object.entries(detalles)) {
                if (typeof cambio === 'object' && cambio.antes !== undefined && cambio.despues !== undefined) {
                    hayCambios = true;
                    const nombreCampo = nombresAmigables[campo] || campo;
                    const valorAntes = escapeHtml(String(cambio.antes || 'N/A'));
                    const valorDespues = escapeHtml(String(cambio.despues || 'N/A'));
                    
                    cambiosHTML += `
                        <div class="card mb-3 border-left-3" style="border-left: 3px solid #ffc107;">
                            <div class="card-body">
                                <h6 class="card-title text-warning">${nombreCampo}</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Valor anterior:</p>
                                        <p class="mb-0"><code>${valorAntes}</code></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="text-muted mb-1">Valor nuevo:</p>
                                        <p class="mb-0"><code>${valorDespues}</code></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }

            if (!hayCambios) {
                cambiosHTML = '<div class="alert alert-info">No se encontraron cambios específicos.</div>';
            }

            detallesDiv.innerHTML = `
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-pencil"></i>
                    <strong>PROPIEDADES MODIFICADAS</strong>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted">Cambios Detectados</h6>
                    </div>
                </div>

                ${cambiosHTML}

                <hr>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Estos cambios fueron registrados en el sistema para auditoría.
                </div>
            `;
            
            // Mostrar modal
            const modalElement = document.getElementById('modalDetallesMovimiento');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('✅ Modal mostrado');
            }
        } catch (error) {
            console.error('❌ Error:', error);
            detallesDiv.innerHTML = `<div class="alert alert-danger">❌ Error: ${escapeHtml(error.message)}</div>`;
        }
    }

    /**
     * Ver detalles de eliminación de material
     */
    function verDetallesEliminacion(detalles) {
        console.log('=== DETALLES DE ELIMINACIÓN ===');
        console.log('Detalles:', detalles);
        
        const detallesDiv = document.getElementById('detalles-movimiento-content');
        
        if (!detallesDiv) {
            console.error('❌ No se encontró elemento modal');
            return;
        }

        try {
            const lineaNombre = detalles.linea_nombre || 'Sin línea';
            const usuarioNombre = detalles.usuario_nombre || 'Sistema';
            
            detallesDiv.innerHTML = `
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>MATERIAL ELIMINADO</strong>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted">Información del Material Eliminado</h6>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong></p>
                        <p class="mb-2">${escapeHtml(detalles.nombre || 'N/A')}</p>
                        <small class="text-muted">Código: <code>${escapeHtml(detalles.codigo || 'N/A')}</code></small>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Línea de Trabajo:</strong></p>
                        <p><span class="badge bg-info">${escapeHtml(lineaNombre)}</span></p>
                    </div>
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Información Adicional</h6>
                        <p class="mb-2"><strong>Cantidad al momento de eliminar:</strong> ${parseInt(detalles.cantidad || 0)}</p>
                        <p class="mb-0"><strong>Descripción:</strong> ${escapeHtml(detalles.descripcion || 'Sin descripción')}</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">✓ Eliminado por:</h6>
                        <div class="d-flex align-items-center gap-2">
                            <div class="badge bg-warning text-dark rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <p class="mb-0"><strong>${escapeHtml(usuarioNombre)}</strong></p>
                                ${detalles.usuario_id ? `<small class="text-muted">ID: ${escapeHtml(detalles.usuario_id)}</small>` : ''}
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Este material ha sido eliminado del inventario y sus registros se conservan para auditoría.
                </div>
            `;
            
            // Mostrar modal
            const modalElement = document.getElementById('modalDetallesMovimiento');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('✅ Modal mostrado');
            }
        } catch (error) {
            console.error('❌ Error:', error);
            detallesDiv.innerHTML = `<div class="alert alert-danger">❌ Error: ${escapeHtml(error.message)}</div>`;
        }
    }

    async function verDetallesMovimiento(movimientoId) {
        console.log('=== INICIANDO verDetallesMovimiento ===');
        console.log('Movimiento ID:', movimientoId);
        
        const detallesDiv = document.getElementById('detalles-movimiento-content');
        
        if (!detallesDiv) {
            console.error('❌ No se encontró elemento detalles-movimiento-content');
            return;
        }

        // Mostrar spinner
        detallesDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Cargando...</span></div> Cargando detalles...</div>';

        try {
            const url = `${window.BASE_URL}/materiales/obtenerDetallesMovimiento?id=${movimientoId}`;
            console.log('URL de fetch:', url);
            
            const response = await fetch(url);
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Datos recibidos:', data);

            if (!data.success) {
                detallesDiv.innerHTML = `<div class="alert alert-warning">⚠️ ${escapeHtml(data.message || 'Error desconocido')}</div>`;
                console.error('Error en respuesta:', data.message);
            } else {
                const mov = data.movimiento;
                console.log('Datos del movimiento:', mov);
                
                const tipoMovimiento = mov.tipo_movimiento === 'entrada' ? 'ENTRADA' : 'SALIDA';
                const badgeClass = mov.tipo_movimiento === 'entrada' ? 'bg-success' : 'bg-danger';
                
                // Obtener línea
                let lineaNombre = mov.linea_nombre || 'Sin línea asignada';
                console.log('Línea encontrada:', lineaNombre);
                
                // Calcular cantidad anterior
                const cantidadAnterior = mov.tipo_movimiento === 'entrada' 
                    ? mov.cantidad_actual - mov.cantidad 
                    : mov.cantidad_actual + mov.cantidad;

                detallesDiv.innerHTML = `
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <span class="badge ${badgeClass}">${tipoMovimiento}</span>
                                <span class="ms-2">${new Date(mov.fecha_movimiento).toLocaleString('es-CO')}</span>
                            </h6>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Material:</strong></p>
                            <p class="mb-2">${escapeHtml(mov.material_nombre || 'N/A')}</p>
                            <small class="text-muted">Código: <code>${escapeHtml(mov.material_codigo || 'N/A')}</code></small>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Línea de Trabajo:</strong></p>
                            <p><span class="badge bg-info">${escapeHtml(lineaNombre)}</span></p>
                        </div>
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Movimiento de Cantidad</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <p class="text-muted mb-1">Cantidad Anterior</p>
                                    <p class="h5"><strong>${parseInt(cantidadAnterior)}</strong></p>
                                </div>
                                <div class="col-4">
                                    <p class="text-muted mb-1">${tipoMovimiento}</p>
                                    <p class="h5 ${mov.tipo_movimiento === 'entrada' ? 'text-success' : 'text-danger'}">
                                        <strong>${mov.tipo_movimiento === 'entrada' ? '+' : '-'}${parseInt(mov.cantidad)}</strong>
                                    </p>
                                </div>
                                <div class="col-4">
                                    <p class="text-muted mb-1">Cantidad Actual</p>
                                    <p class="h5"><strong>${parseInt(mov.cantidad_actual)}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong>
                                    ${tipoMovimiento === 'ENTRADA' ? '✓ Entrada Registrada por:' : '✓ Salida Registrada por:'}
                                </strong>
                                <p class="mb-0 mt-2">${escapeHtml(mov.usuario_nombre || 'N/A')}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <p><strong>Descripción:</strong></p>
                            <p class="text-muted">${escapeHtml(mov.descripcion || 'Sin descripción')}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-muted small">
                        <div class="col-12">
                            <p><strong>Fecha Movimiento:</strong> ${new Date(mov.fecha_movimiento).toLocaleString('es-CO')}</p>
                            <p><strong>ID Movimiento:</strong> ${mov.id}</p>
                        </div>
                    </div>
                `;
                console.log('✅ Modal renderizado correctamente');
            }
        } catch (error) {
            console.error('❌ Error al obtener detalles:', error);
            detallesDiv.innerHTML = `<div class="alert alert-danger">❌ Error: ${escapeHtml(error.message)}</div>`;
        }

        // Mostrar modal
        const modalElement = document.getElementById('modalDetallesMovimiento');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('✅ Modal mostrado');
        } else {
            console.error('❌ No se encontró elemento modal');
        }
    }

    // Cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', () => {
        console.log('🔵 DOMContentLoaded disparado');
        
        // Event listeners para botones de detalles
        const botones = document.querySelectorAll('.btn-detalles');
        console.log(`Encontrados ${botones.length} botones .btn-detalles`);
        
        botones.forEach((btn, index) => {
            console.log(`Botón ${index}:`, btn);
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const movimientoId = this.getAttribute('data-id');
                console.log(`✅ Click en botón ${index}, Movimiento ID: ${movimientoId}`);
                verDetallesMovimiento(movimientoId);
            });
        });

        // Event listeners para botones de detalles de eliminaciones
        const botonesEliminacion = document.querySelectorAll('.btn-detalles-eliminacion');
        console.log(`Encontrados ${botonesEliminacion.length} botones .btn-detalles-eliminacion`);
        
        botonesEliminacion.forEach((btn) => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const detalles = JSON.parse(this.getAttribute('data-detalles'));
                console.log('✅ Detalles de eliminación:', detalles);
                verDetallesEliminacion(detalles);
            });
        });

        // Event listeners para botones de detalles de cambios
        const botonesCambios = document.querySelectorAll('.btn-detalles-cambio');
        console.log(`Encontrados ${botonesCambios.length} botones .btn-detalles-cambio`);
        
        botonesCambios.forEach((btn) => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const detalles = JSON.parse(this.getAttribute('data-detalles'));
                console.log('✅ Detalles de cambios:', detalles);
                verDetallesCambio(detalles);
            });
        });

        // Auto-filtrado en tiempo real (como en historial de usuarios)
        const filtroMaterial = document.getElementById('filtro-material');
        const filtroTipo = document.getElementById('filtro-tipo');
        const filtroFechaInicio = document.getElementById('filtro-fecha-inicio');
        const filtroFechaFin = document.getElementById('filtro-fecha-fin');
        const btnLimpiar = document.getElementById('btn-limpiar');

        function aplicarFiltros() {
            const params = new URLSearchParams();
            
            const material = filtroMaterial.value;
            const tipo = filtroTipo.value;
            const fechaInicio = filtroFechaInicio.value;
            const fechaFin = filtroFechaFin.value;

            // Validar que si hay fecha inicio, no sea después de fecha fin
            if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
                alert('La fecha inicio no puede ser posterior a la fecha fin');
                return;
            }

            if (material) params.append('material_id', material);
            if (tipo) params.append('tipo', tipo);
            if (fechaInicio) params.append('fecha_inicio', fechaInicio);
            if (fechaFin) params.append('fecha_fin', fechaFin);

            const urlFinal = `${window.BASE_URL}/materiales/historialInventario${params.toString() ? '?' + params.toString() : ''}`;
            window.location.href = urlFinal;
        }

        // Auto-aplicar filtros al cambiar
        [filtroMaterial, filtroTipo, filtroFechaInicio, filtroFechaFin].forEach(filtro => {
            if (filtro) {
                filtro.addEventListener('change', aplicarFiltros);
            }
        });

        // Limpiar filtros
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Limpiar los valores de los filtros en la UI
                filtroMaterial.value = '';
                filtroTipo.value = '';
                filtroFechaInicio.value = '';
                filtroFechaFin.value = '';
                
                const urlDestino = `${window.BASE_URL}/materiales/historialInventario`;
                window.location.href = urlDestino;
            });
        }

        // Cargar conteo de documentos para cada material
        const documentosBadges = document.querySelectorAll('[id^="docs-count-"]');
        documentosBadges.forEach(badge => {
            const materialId = badge.dataset.materialId;
            if (materialId && materialId > 0) {
                cargarDocumentosHistorial(materialId, badge.id);
            }
        });
    });

    // Función para cargar conteo de documentos en historial
    function cargarDocumentosHistorial(materialId, badgeId) {
        const badgeElement = document.getElementById(badgeId);
        if (!badgeElement) return;

        fetch(`${window.BASE_URL}/materiales/contarDocumentos?material_id=${materialId}`)
            .then(response => response.json())
            .then(data => {
                const countSpan = badgeElement.querySelector('.count-value');
                if (countSpan) {
                    countSpan.textContent = data.count || 0;
                }
                
                // Cambiar color del badge según cantidad
                if (data.count === 0) {
                    badgeElement.classList.remove('bg-secondary');
                    badgeElement.classList.add('bg-danger');
                    badgeElement.title = 'Sin documentos';
                } else if (data.count > 0) {
                    badgeElement.classList.remove('bg-secondary', 'bg-danger');
                    badgeElement.classList.add('bg-primary');
                    badgeElement.title = `${data.count} documento${data.count !== 1 ? 's' : ''} adjunto${data.count !== 1 ? 's' : ''}`;
                }
            })
            .catch(err => {
                console.error("Error cargando documentos:", err);
                const countSpan = badgeElement.querySelector('.count-value');
                if (countSpan) countSpan.textContent = '?';
            });
    }

    // Permitir hacer clic en el badge para ver documentos
    document.addEventListener('click', (e) => {
        if (e.target.closest('.doc-count-badge')) {
            const badge = e.target.closest('.doc-count-badge');
            const materialId = badge.dataset.materialId;
            if (materialId && materialId > 0) {
                window.location.href = `${window.BASE_URL}/materiales/detalles?id=${materialId}`;
            }
        }
    });

</script>
