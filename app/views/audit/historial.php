<?php
if (!isset($_SESSION['user']) || ($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
    header("Location: " . BASE_URL . "/?url=auth/login");
    exit;
}
?>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>

<div class="row justify-content-center">
    <div class="col-12">

        <h3 class="mb-3">Historial de Cambios</h3>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Usuario</label>
                        <select id="filtro-usuario" class="form-select">
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
                        <select id="filtro-accion" class="form-select">
                            <option value="">Todas</option>
                            <option value="crear" <?= $filtro['accion'] == 'crear' ? 'selected' : '' ?>>Crear</option>
                            <option value="actualizar" <?= $filtro['accion'] == 'actualizar' ? 'selected' : '' ?>>Actualizar</option>
                            <option value="desactivar/activar" <?= $filtro['accion'] == 'desactivar/activar' ? 'selected' : '' ?>>Desactivar/Activar</option>
                            <option value="eliminar" <?= $filtro['accion'] == 'eliminar' ? 'selected' : '' ?>>Eliminar</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" id="filtro-fecha-inicio" class="form-control" 
                               value="<?= htmlspecialchars($filtro['fecha_inicio']) ?>">
                    </div>

                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" id="filtro-fecha-fin" class="form-control"
                               value="<?= htmlspecialchars($filtro['fecha_fin']) ?>">
                    </div>

                </div>
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
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Detalles</th>
                    </tr>
                    </thead>
                    <tbody id="historial-body">
                    <?php foreach ($cambios as $cambio): ?>
                        <?php 
                        $detalles = json_decode($cambio['detalles'], true) ?? [];
                        $tieneDetalles = !empty($detalles);
                        $detallesId = 'detalles-' . $cambio['id'] . '-0';
                        $acciones = [
                            'crear' => ['badge bg-success', 'Creado'],
                            'actualizar' => ['badge bg-info', 'Actualizado'],
                            'desactivar/activar' => ['badge bg-warning', 'Desactivar/Activar'],
                            'eliminar' => ['badge bg-danger', 'Eliminado']
                        ];
                        $accion = $cambio['accion'];
                        [$clase, $texto] = $acciones[$accion] ?? ['badge bg-secondary', $accion];
                        ?>
                        <tr>
                            <td><?= $cambio['id'] ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($cambio['fecha_creacion']) ?></small></td>
                            <td><?= htmlspecialchars($cambio['usuario_modificado'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge <?= $clase ?>"><?= $texto ?></span>
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
                                    <button class="btn btn-sm btn-info" onclick="window.toggleDetalles('<?= $detallesId ?>', event)" type="button">
                                        <i class="bi bi-eye"></i> Ver cambios
                                    </button>
                                    <div id="<?= $detallesId ?>" class="detalles-modal" style="display:none; margin-top:10px; padding:12px; background:#f9fafb; border-radius:6px; border-left:3px solid #0d6efd;">
                                        <?php foreach ($detalles as $campo => $valor): ?>
                                            <div style="margin-bottom:8px; padding:8px; background:white; border-radius:4px;">
                                                <strong style="color:#0d6efd;"><?= htmlspecialchars($campo) ?></strong>
                                                <div style="margin-top:4px; font-size:0.9rem;">
                                                    <?php if (is_array($valor)): ?>
                                                        <div style="display:flex; gap:12px; margin-top:4px;">
                                                            <div>
                                                                <span style="color:#666; font-size:0.85rem;">Anterior:</span><br>
                                                                <span style="color:#dc3545;"><?= htmlspecialchars($valor['anterior'] ?? '-') ?></span>
                                                            </div>
                                                            <div style="border-left:1px solid #ddd; padding-left:12px;">
                                                                <span style="color:#666; font-size:0.85rem;">Nuevo:</span><br>
                                                                <span style="color:#198754;"><?= htmlspecialchars($valor['nuevo'] ?? '-') ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($valor) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
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
