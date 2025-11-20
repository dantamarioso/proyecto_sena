<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/?url=auth/login");
    exit;
}
?>

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>

<style>
    tbody tr:only-child td {
        display: table-cell !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-12">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
            <h3 class="mb-0">Gestión de Inventario</h3>
            <?php if (($_SESSION['user']['rol'] ?? 'usuario') === 'admin'): ?>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= BASE_URL ?>/?url=materiales/crear" class="btn btn-success btn-sm w-100 w-sm-auto">
                        <i class="bi bi-plus-lg"></i> Nuevo Material
                    </a>
                    <a href="<?= BASE_URL ?>/?url=materiales/historialInventario" class="btn btn-info btn-sm w-100 w-sm-auto">
                        <i class="bi bi-clock-history"></i> Historial
                    </a>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/?url=materiales/historialInventario" class="btn btn-info btn-sm w-100 w-sm-auto">
                    <i class="bi bi-clock-history"></i> Ver Historial
                </a>
            <?php endif; ?>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" id="busqueda" class="form-control" placeholder="Nombre, código o descripción" value="<?= htmlspecialchars($busqueda) ?>">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label">Línea</label>
                        <select id="filtro-linea" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($lineas as $linea): ?>
                                <option value="<?= $linea['id'] ?>" <?= $linea_id == $linea['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($linea['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label">Estado</label>
                        <select id="filtro-estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" <?= $estado === 1 ? 'selected' : '' ?>>Activos</option>
                            <option value="0" <?= $estado === 0 ? 'selected' : '' ?>>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button class="btn btn-outline-secondary w-100" id="btn-limpiar">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Responsiva -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th class="d-none d-md-table-cell">Descripción</th>
                            <th>Línea</th>
                            <th class="text-center">Cantidad</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="materiales-body">
                        <?php foreach ($materiales as $m): ?>
                            <tr class="material-row" data-id="<?= $m['id'] ?>">
                                <td><?= $m['id'] ?></td>
                                <td><code><?= htmlspecialchars($m['codigo']) ?></code></td>
                                <td><strong><?= htmlspecialchars($m['nombre']) ?></strong></td>
                                <td class="d-none d-md-table-cell">
                                    <small><?= htmlspecialchars(substr($m['descripcion'], 0, 50)) ?><?= strlen($m['descripcion']) > 50 ? '...' : '' ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($m['linea_nombre'] ?? 'N/A') ?></span>
                                </td>
                                <td class="text-center">
                                    <strong><?= intval($m['cantidad']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($m['estado'] == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-info btn-sm btn-ver" title="Ver detalles" data-id="<?= $m['id'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if (($_SESSION['user']['rol'] ?? 'usuario') === 'admin'): ?>
                                            <button class="btn btn-warning btn-sm btn-entrada" title="Entrada" data-id="<?= $m['id'] ?>">
                                                <i class="bi bi-plus-square"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-salida" title="Salida" data-id="<?= $m['id'] ?>">
                                                <i class="bi bi-dash-square"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>/?url=materiales/editar&id=<?= $m['id'] ?>"
                                               class="btn btn-primary btn-sm" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm btn-eliminar" title="Eliminar" data-id="<?= $m['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($materiales)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">
                                    <i class="bi bi-inbox"></i> No hay materiales registrados.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Estadísticas -->
            <div class="card-footer bg-light">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-box2-heart"></i>
                            Mostrando <strong><?= count($materiales) ?></strong> material(es)
                        </small>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="row g-2 text-center">
                            <?php foreach ($estadoLineas as $linea): ?>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">
                                        <?= htmlspecialchars($linea['nombre']) ?>
                                    </small>
                                    <strong class="d-block"><?= $linea['total'] ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ver Detalles -->
<div class="modal fade" id="modalDetalles" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalles-content">
                <p class="text-center text-muted">Cargando...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Movimiento (Entrada/Salida) -->
<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="movimiento-titulo">Registrar Entrada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Material</label>
                    <input type="text" id="mov-material" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad a registrar</label>
                    <input type="number" id="mov-cantidad" class="form-control" placeholder="0" min="1" step="1">
                    <small class="form-text text-muted">Solo se aceptan números enteros</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción (opcional)</label>
                    <textarea id="mov-descripcion" class="form-control" rows="2" placeholder="Motivo o detalles del movimiento"></textarea>
                </div>
                <div id="movimiento-errors" class="alert alert-danger" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-movimiento">Guardar Movimiento</button>
            </div>
        </div>
    </div>
</div>
