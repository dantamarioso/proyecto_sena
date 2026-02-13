<?php
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo 'Acceso denegado.';
    exit;
}

$materialId = intval($_GET['id'] ?? 0);
if ($materialId <= 0) {
    http_response_code(404);
    echo 'Material no encontrado.';
    exit;
}

$materialModel = new Material();
$material = $materialModel->getById($materialId);

if (!$material) {
    http_response_code(404);
    echo 'Material no encontrado.';
    exit;
}

$archivoModel = new MaterialArchivo();
$archivos = $archivoModel->getByMaterial($materialId);
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="<?= BASE_URL ?>/materiales/index" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>

        <!-- Información Principal del Material -->
        <div class="card mb-4">
            <div class="card-header bg-light border-bottom">
                <h4 class="mb-0"><?= htmlspecialchars($material['nombre']) ?></h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Código</h6>
                            <p class="mb-0"><code class="bg-light p-2 rounded"><?= htmlspecialchars($material['codigo']) ?></code></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Nodo</h6>
                            <p class="mb-0"><span class="badge bg-secondary"><?= htmlspecialchars($material['nodo_nombre'] ?? 'Sin nodo') ?></span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Línea</h6>
                            <p class="mb-0"><span class="badge bg-primary"><?= htmlspecialchars($material['linea_nombre'] ?? 'Sin línea') ?></span></p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-muted">Fecha de Compra</h6>
                            <p class="mb-0"><?= $material['fecha_adquisicion'] ? date('d/m/Y', strtotime($material['fecha_adquisicion'])) : 'No especificada' ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-muted">Categoría</h6>
                            <p class="mb-0"><?= htmlspecialchars($material['categoria'] ?? 'No especificada') ?></p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-muted">Fecha de Fabricación</h6>
                            <p class="mb-0"><?= !empty($material['fecha_fabricacion']) ? date('d/m/Y', strtotime($material['fecha_fabricacion'])) : 'No especificada' ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-muted">Fecha de Vencimiento</h6>
                            <?php
                            $fechaV = $material['fecha_vencimiento'] ?? null;
                            $hoy = date('Y-m-d');
                            $estaVencido = (!empty($fechaV) && $fechaV <= $hoy);
                            ?>
                            <p class="mb-0">
                                <?= !empty($fechaV) ? date('d/m/Y', strtotime($fechaV)) : 'No especificada' ?>
                                <?php if (empty($fechaV)) : ?>
                                    <span class="badge bg-secondary ms-2">Sin fecha</span>
                                <?php elseif ($estaVencido) : ?>
                                    <span class="badge bg-danger ms-2">Vencido</span>
                                <?php else : ?>
                                    <span class="badge bg-success ms-2">Vigente</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Presentación</h6>
                            <p class="mb-0"><?= htmlspecialchars($material['presentacion'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Medida</h6>
                            <p class="mb-0"><?= htmlspecialchars($material['medida'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Cantidad en Stock</h6>
                            <p class="mb-0"><span class="badge bg-info"><?= htmlspecialchars(formatearCantidad($material['cantidad'] ?? 0)) ?></span></p>
                        </div>
                    </div>
                </div>

                <?php
                $reqVal = (float)($material['cantidad_requerida'] ?? 0);
                $stockVal = (float)($material['cantidad'] ?? 0);
                $faltanteVal = $reqVal - $stockVal;

                $req = formatearCantidad($material['cantidad_requerida'] ?? 0);
                $stock = formatearCantidad($material['cantidad'] ?? 0);
                $faltante = ($faltanteVal > 0) ? formatearCantidad($faltanteVal) : '0';
                ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Cantidad Requerida</h6>
                            <p class="mb-0"><span class="badge bg-secondary"><?= htmlspecialchars($req) ?></span></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Cantidad Faltante</h6>
                            <p class="mb-0">
                                <?php if ((float)$faltante > 0) : ?>
                                    <span class="badge bg-danger"><?= htmlspecialchars($faltante) ?></span>
                                <?php else : ?>
                                    <span class="badge bg-success">0</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Ubicación</h6>
                            <p class="mb-0"><?= htmlspecialchars($material['ubicacion'] ?? 'No especificada') ?></p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Valor de Compra</h6>
                            <p class="mb-0"><?= $material['valor_compra'] ? '$ ' . number_format($material['valor_compra'], 2) : 'No especificado' ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Fabricante</h6>
                            <p class="mb-0"><?= htmlspecialchars($material['fabricante'] ?? $material['marca'] ?? $material['proveedor'] ?? 'No especificado') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <h6 class="text-muted">Proveedor</h6>
                            <p class="mb-0"><?= htmlspecialchars($material['proveedor'] ?? 'No especificado') ?></p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h6 class="text-muted">Observación</h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($material['observacion'] ?? '')) ?: 'No especificada' ?></p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h6 class="text-muted">Estado</h6>
                            <p class="mb-0">
                                <?= $material['estado'] == 1 ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Activo</span>' : '<span class="badge bg-secondary"><i class="bi bi-dash-circle"></i> Inactivo</span>' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted"><strong>Fecha Creación:</strong> <?= date('d/m/Y H:i', strtotime($material['fecha_creacion'] ?? 'now')) ?></small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted"><strong>Última Actualización:</strong> <?= date('d/m/Y H:i', strtotime($material['fecha_actualizacion'] ?? 'now')) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Archivos Adjuntos -->
        <div class="card">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0"><i class="bi bi-paperclip"></i> Archivos Adjuntos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($archivos)) : ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Este material no tiene archivos adjuntos.
                    </div>
                <?php else : ?>
                    <div class="list-group">
                        <?php foreach ($archivos as $archivo) : ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
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
                                    <?php
                                    $rutaArchivo = $archivo['ruta'] ?? null;
                                    if (!$rutaArchivo) {
                                        $rutaArchivo = 'uploads/materiales/' . ($archivo['nombre_archivo'] ?? '');
                                    }
                                    ?>
                                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($rutaArchivo) ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Descargar">
                                        <i class="bi bi-download"></i> Descargar
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
