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

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h4 class="mb-0"><?= htmlspecialchars($material['nombre']) ?></h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>Código:</strong> <?= htmlspecialchars($material['codigo']) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Cantidad:</strong> <?= intval($material['cantidad']) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Estado:</strong> 
                            <?= $material['estado'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>Descripción:</strong>
                        </div>
                        <p class="text-muted"><?= htmlspecialchars($material['descripcion'] ?? 'Sin descripción') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-paperclip"></i> Archivos Adjuntos</h5>
            </div>
            <div class="card-body">
                <?php if (empty($archivos)): ?>
                    <div class="alert alert-info">
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
                                        <?= formatearBytes($archivo['tamaño']) ?> • <?= date('d/m/Y H:i', strtotime($archivo['fecha_creacion'])) ?>
                                    </small>
                                </div>
                                <a href="/<?= $archivo['nombre_archivo'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Descargar">
                                    <i class="bi bi-download"></i> Descargar
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
