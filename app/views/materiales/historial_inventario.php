<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/?url=auth/login");
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
            <a href="<?= BASE_URL ?>/?url=materiales/index" class="btn btn-outline-secondary btn-sm w-100 w-sm-auto">
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
                            <option value="eliminado">Eliminado</option>
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
                            <th>#</th>
                            <th>Material</th>
                            <th class="d-none d-md-table-cell">Línea</th>
                            <th>Tipo</th>
                            <th class="text-center">Cantidad</th>
                            <th class="d-none d-lg-table-cell">Usuario</th>
                            <th class="d-none d-lg-table-cell">Descripción</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
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
                                    <td class="d-none d-md-table-cell">
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
                                    <td class="d-none d-lg-table-cell">
                                        <small><?= htmlspecialchars($mov['usuario_nombre'] ?? 'N/A') ?></small>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <?php if ($mov['tipo_registro'] === 'movimiento'): ?>
                                            <small class="text-muted"><?= htmlspecialchars($mov['descripcion']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">Material eliminado del inventario</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?php $fecha = $mov['tipo_registro'] === 'movimiento' ? $mov['fecha_movimiento'] : $mov['fecha_creacion']; ?>
                                            <?= date('d/m/Y', strtotime($fecha)) ?><br>
                                            <span class="text-muted"><?= date('H:i', strtotime($fecha)) ?></span>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($mov['tipo_registro'] === 'movimiento'): ?>
                                            <button class="btn btn-info btn-sm btn-detalles" data-id="<?= $mov['id'] ?>" title="Ver detalles">
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
                                <td colspan="9" class="text-center text-muted py-3">
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
                    <div class="col-12 col-md-4">
                        <small class="text-muted">
                            <i class="bi bi-plus-lg text-success"></i>
                            Entradas: <strong><?= count(array_filter($historial, fn($m) => ($m['tipo_registro'] ?? null) === 'movimiento' && ($m['tipo_movimiento'] ?? null) === 'entrada')) ?></strong>
                        </small>
                    </div>
                    <div class="col-12 col-md-4">
                        <small class="text-muted">
                            <i class="bi bi-dash-lg text-danger"></i>
                            Salidas: <strong><?= count(array_filter($historial, fn($m) => ($m['tipo_registro'] ?? null) === 'movimiento' && ($m['tipo_movimiento'] ?? null) === 'salida')) ?></strong>
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
    const BASE_URL = "<?= BASE_URL ?>";

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
            const url = `${BASE_URL}/?url=materiales/obtenerDetallesMovimiento&id=${movimientoId}`;
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
                            <p><strong>Usuario que registró:</strong></p>
                            <p>${escapeHtml(mov.usuario_nombre || 'N/A')}</p>
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

            const urlFinal = `${BASE_URL}/?url=materiales/historialInventario${params.toString() ? '&' + params.toString() : ''}`;
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
                
                const urlDestino = `${BASE_URL}/?url=materiales/historialInventario`;
                window.location.href = urlDestino;
            });
        }
    });
</script>
