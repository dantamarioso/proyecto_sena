<?php
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

$materialId = intval($_GET['id'] ?? 0);
if ($materialId <= 0) {
    http_response_code(404);
    echo "Material no encontrado.";
    exit;
}

$materialModel = new Material();
$material = $materialModel->getById($materialId);

if (!$material) {
    http_response_code(404);
    echo "Material no encontrado.";
    exit;
}

$archivoModel = new MaterialArchivo();
$archivos = $archivoModel->getByMaterial($materialId);
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="<?= BASE_URL ?>/?url=materiales/index" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Volver
        </a>

        <!-- Información Principal del Material -->
        <div class="card mb-4">
            <div class="card-header bg-light border-bottom">
                <h4 class="mb-0"><?= htmlspecialchars($material['nombre']) ?></h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-muted">Código</h6>
                            <p class="mb-0"><code class="bg-light p-2 rounded"><?= htmlspecialchars($material['codigo']) ?></code></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-muted">Cantidad Actual</h6>
                            <p class="mb-0"><span class="badge bg-info"><?= intval($material['cantidad']) ?> unidades</span></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-muted">Estado</h6>
                            <p class="mb-0">
                                <?= $material['estado'] == 1 ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Activo</span>' : '<span class="badge bg-secondary"><i class="bi bi-dash-circle"></i> Inactivo</span>' ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6 class="text-muted">Línea de Trabajo</h6>
                            <p class="mb-0"><span class="badge bg-primary"><?= htmlspecialchars($material['linea_nombre'] ?? 'Sin línea') ?></span></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-muted">Fecha Creación</h6>
                            <p class="mb-0"><small><?= date('d/m/Y H:i', strtotime($material['fecha_creacion'] ?? 'now')) ?></small></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-muted">Última Actualización</h6>
                            <p class="mb-0"><small><?= date('d/m/Y H:i', strtotime($material['fecha_actualizacion'] ?? 'now')) ?></small></p>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-0">
                    <h6 class="text-muted">Descripción</h6>
                    <p><?= htmlspecialchars($material['descripcion'] ?? 'Sin descripción') ?></p>
                </div>
            </div>
        </div>

        <!-- Archivos Adjuntos -->
        <div class="card">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0"><i class="bi bi-paperclip"></i> Archivos Adjuntos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($archivos)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Este material no tiene archivos adjuntos.
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($archivos as $archivo): ?>
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
                                    <a href="<?= BASE_URL ?>/<?= $archivo['nombre_archivo'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Descargar">
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
