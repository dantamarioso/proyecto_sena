<?php
if (!isset($_SESSION['user']) || ($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login");
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-12">

        <h3 class="mb-3">Historial de Cambios Usuario</h3>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="<?= BASE_URL ?>/audit/historial" id="form-filtros">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Usuario</label>
                            <select name="usuario_id" class="form-select" id="filtro-usuario">
                                <option value="">Todos los usuarios</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $filtro['usuario_id'] == $u['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-md-2">
                            <label class="form-label">Acción</label>
                            <select name="accion" class="form-select" id="filtro-accion">
                                <option value="">Todas</option>
                                <?php foreach ($accionesDisponibles as $accionOpt): ?>
                                    <option value="<?= $accionOpt ?>" <?= $filtro['accion'] == $accionOpt ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $accionOpt))) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-md-2">
                            <label class="form-label">Desde</label>
                            <input type="date" name="fecha_inicio" class="form-control" id="filtro-fecha-inicio"
                                   value="<?= htmlspecialchars($filtro['fecha_inicio']) ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-md-2">
                            <label class="form-label">Hasta</label>
                            <input type="date" name="fecha_fin" class="form-control" id="filtro-fecha-fin"
                                   value="<?= htmlspecialchars($filtro['fecha_fin']) ?>">
                        </div>

                        <div class="col-12 col-md-2">
                            <a href="<?= BASE_URL ?>/audit/historial" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla Historial -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Usuario Afectado</th>
                        <th>Realizado por</th>
                        <th>Acción</th>
                        <th>Detalles</th>
                    </tr>
                    </thead>
                    <tbody id="historial-body">
                    <?php foreach ($cambios as $cambio): ?>
                        <?php 
                        $detallesRaw = json_decode($cambio['detalles'], true);
                        $detalles = is_array($detallesRaw) ? $detallesRaw : [];
                        $tieneDetalles = !empty($detalles);
                        
                        // Mapa de acciones con colores - alineado con ENUM de la BD
                        $acciones = [
                            'crear' => ['bg-success', 'Creado', 'pencil-plus'],
                            'actualizar' => ['bg-primary', 'Actualizado', 'pencil-square'],
                            'eliminar' => ['bg-danger', 'Eliminado', 'trash'],
                            'sin_accion' => ['bg-secondary', 'Sin acción', 'question-circle'],
                            // Fallbacks para datos antiguos
                            'UPDATE' => ['bg-primary', 'Actualizado', 'pencil-square'],
                            'actualizar_rol' => ['bg-info', 'Rol Actualizado', 'shield-check'],
                            'actualizar_estado' => ['bg-warning text-dark', 'Estado Actualizado', 'toggle-on'],
                            'asignar_nodo' => ['bg-secondary', 'Nodo Asignado', 'link-45deg'],
                            'desactivar/activar' => ['bg-warning text-dark', 'Desactivar/Activar', 'toggle-off'],
                            'delete' => ['bg-danger', 'Eliminado', 'trash'],
                            'ver' => ['bg-light text-dark', 'Visto', 'eye']
                        ];
                        
                        $accion = $cambio['accion'] ?? '';
                        [$clase, $texto, $icono] = $acciones[$accion] ?? ['bg-secondary', htmlspecialchars($accion ?: 'Desconocido'), 'question-circle'];
                        ?>
                        <tr>
                            <td><?= $cambio['id'] ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($cambio['fecha_cambio']) ?></small></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <small><?= htmlspecialchars($cambio['usuario_modificado'] ?? 'N/A') ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($cambio['admin_foto']): ?>
                                        <img src="<?= BASE_URL . '/' . htmlspecialchars($cambio['admin_foto']) ?>" 
                                             width="24" height="24" class="rounded-circle" style="object-fit: cover;"
                                             title="<?= htmlspecialchars($cambio['admin_nombre'] ?? 'Admin') ?>">
                                    <?php else: ?>
                                        <img src="<?= BASE_URL ?>/img/default_user.png" 
                                             width="24" height="24" class="rounded-circle" 
                                             title="<?= htmlspecialchars($cambio['admin_nombre'] ?? 'Admin') ?>">
                                    <?php endif; ?>
                                    <small><?= htmlspecialchars($cambio['admin_nombre'] ?? 'Sistema') ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $clase ?>"><i class="bi bi-<?= $icono ?>"></i> <?= $texto ?></span>
                                <?php if ($accion === 'desactivar/activar' && isset($detalles['Acción'])): ?>
                                    <?php if (strpos($detalles['Acción'], 'Desactivado') !== false): ?>
                                        <span class="badge bg-danger ms-1"><i class="bi bi-lock"></i> Desactivado</span>
                                    <?php elseif (strpos($detalles['Acción'], 'Activado') !== false): ?>
                                        <span class="badge bg-success ms-1"><i class="bi bi-unlock"></i> Activado</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($tieneDetalles): ?>
                                    <button class="btn btn-sm btn-primary" onclick="abrirModalDetalles(<?= $cambio['id'] ?>)" type="button">
                                        <i class="bi bi-eye"></i> Ver cambios
                                    </button>
                                    
                                    <!-- Contenedor de detalles (oculto, para ser extraído por JavaScript) -->
                                    <div id="modal-detalles-<?= $cambio['id'] ?>" class="modal-detalles-data" style="display: none;">
                                        <div class="modal-header" style="background: linear-gradient(135deg, #00304D 0%, #007832 100%);

 color: white;">
                                            <div class="w-100">
                                                <h5 class="modal-title" id="modal-label-<?= $cambio['id'] ?>">
                                                    <i class="bi bi-clock-history"></i> Historial Detallado de Cambio
                                                </h5>
                                                <small class="d-block opacity-75">ID de Cambio: #<?= $cambio['id'] ?></small>
                                            </div>
                                        </div>
                                        
                                        <div class="modal-body">
                                            <!-- Sección de Metadatos -->
                                            <div class="mb-4">
                                                <h6 class="mb-3" style="color: #667eea;">
                                                    <i class="bi bi-info-circle"></i> Información del Cambio
                                                </h6>
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <div class="p-3 rounded" style="background-color: #f8f9fa; border-left: 4px solid #00304D;">
                                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Fecha y Hora</small>
                                                            <div class="mt-2">
                                                                <strong class="d-block"><?= date('d/m/Y', strtotime($cambio['fecha_cambio'])) ?></strong>
                                                                <small class="text-muted"><?= date('H:i:s', strtotime($cambio['fecha_cambio'])) ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="p-3 rounded" style="background-color: #f8f9fa; border-left: 4px solid #39A900;">
                                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Usuario Modificado</small>
                                                            <div class="mt-2">
                                                                <strong class="d-block"><?= htmlspecialchars($cambio['usuario_modificado'] ?? 'N/A') ?></strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="p-3 rounded" style="background-color: #f8f9fa; border-left: 4px solid #b30c1c;">
                                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Tipo de Acción</small>
                                                            <div class="mt-2">
                                                                <span class="accion-badge accion-<?= str_replace(['/', ' '], '-', strtolower($accion)) ?>"><?= htmlspecialchars($texto) ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="p-3 rounded" style="background-color: #f8f9fa; border-left: 4px solid #0dcaf0;">
                                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Tabla Afectada</small>
                                                            <div class="mt-2">
                                                                <code class="d-block"><?= htmlspecialchars($cambio['tabla'] ?? 'usuarios') ?></code>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="p-3 rounded" style="background-color: #f8f9fa; border-left: 4px solid #fd7e14;">
                                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Realizado por</small>
                                                            <div class="mt-2">
                                                                <strong class="d-block"><?= htmlspecialchars($cambio['admin_nombre'] ?? 'Sistema') ?></strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="my-4">
                                            
                                            <!-- Sección de Cambios Detallados -->
                                            <div>
                                                <h6 class="mb-3" style="color: #667eea;">
                                                    <i class="bi bi-list-check"></i> Campos Modificados (<?= count($detalles) ?> cambio<?= count($detalles) !== 1 ? 's' : '' ?>)
                                                </h6>
                                                
                                                <div class="cambios-detallados">
                                                    <?php $contador = 0; foreach ($detalles as $campo => $valor): $contador++; ?>
                                                        <div class="cambio-item">
                                                            <div class="cambio-header">
                                                                <i class="bi bi-pencil-square"></i>
                                                                <span class="cambio-campo"><?= htmlspecialchars($campo) ?></span>
                                                                <span class="badge badge-pill" style="background-color: #667eea; font-size: 0.7rem;">Cambio <?= $contador ?></span>
                                                            </div>
                                                            
                                                            <?php if (is_array($valor)): ?>
                                                                <div class="cambio-valores">
                                                                    <div class="valor-anterior">
                                                                        <span class="valor-label">
                                                                            <i class="bi bi-arrow-left"></i> Valor Anterior
                                                                        </span>
                                                                        <div class="valor-box <?= empty($valor['anterior']) ? 'vacio' : '' ?>">
                                                                            <?php if (empty($valor['anterior'])): ?>
                                                                                (sin valor previo)
                                                                            <?php else: ?>
                                                                                <?= htmlspecialchars($valor['anterior']) ?>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="valor-nuevo">
                                                                        <span class="valor-label">
                                                                            <i class="bi bi-arrow-right"></i> Valor Nuevo
                                                                        </span>
                                                                        <div class="valor-box <?= empty($valor['nuevo']) ? 'vacio' : '' ?>">
                                                                            <?php if (empty($valor['nuevo'])): ?>
                                                                                (sin valor)
                                                                            <?php else: ?>
                                                                                <?= htmlspecialchars($valor['nuevo']) ?>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="alert alert-info mb-0 mt-2" style="font-size: 0.9rem;">
                                                                    <i class="bi bi-info-circle"></i> <?= htmlspecialchars($valor) ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Sin detalles</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($cambios)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No hay cambios registrados.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="card-footer bg-light">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                    <small class="text-muted">Total: <?= $total ?> cambio(s)</small>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" id="btn-prev">&laquo; Anterior</button>
                        <span id="pagina-actual" class="mx-2"><?= $page ?> / <?= $totalPages ?></span>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-next">Siguiente &raquo;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
