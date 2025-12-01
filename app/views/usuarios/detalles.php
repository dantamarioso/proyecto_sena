<?php
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}
?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="<?= BASE_URL . '/' . ($usuario['foto'] ?: 'img/default_user.png') ?>" width="80" height="80" class="rounded-circle border" style="object-fit:cover;">
                <div>
                    <h4 class="mb-1"><?= htmlspecialchars($usuario['nombre']) ?></h4>
                    <div class="mb-1"><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></div>
                    <div class="mb-1"><strong>Usuario:</strong> <?= htmlspecialchars($usuario['nombre_usuario']) ?></div>
                    <div class="mb-1"><strong>Rol:</strong> <span class="badge bg-info"><?= htmlspecialchars($usuario['rol']) ?></span></div>
                    <div class="mb-1"><strong>Estado:</strong> <?= $usuario['estado'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Bloqueado</span>' ?></div>
                    <?php if ($usuario['nodo_id']): ?>
                        <div class="mb-1"><strong>Nodo:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($nodo_nombre ?? 'Nodo ' . $usuario['nodo_id']) ?></span></div>
                    <?php endif; ?>
                    <?php if ($usuario['linea_id']): ?>
                        <div class="mb-1"><strong>Línea:</strong> <span class="badge bg-warning"><?= htmlspecialchars($linea_nombre ?? 'Línea ' . $usuario['linea_id']) ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-paperclip"></i> Archivos subidos por el usuario</h5>
            </div>
            <div class="card-body">
                <?php if (empty($archivos)): ?>
                    <div class="alert alert-info"><i class="bi bi-info-circle"></i> Este usuario no ha subido archivos.</div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($archivos as $archivo): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">
                                        <i class="bi bi-file-earmark"></i>
                                        <?= htmlspecialchars($archivo['nombre_original']) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= formatearBytes($archivo['tamaño']) ?> • <?= date('d/m/Y H:i', strtotime($archivo['fecha_creacion'])) ?>
                                    </small>
                                </div>
                                <a href="/<?= $archivo['nombre_archivo'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Descargar">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>


        <a href="<?= BASE_URL ?>/usuarios/gestionDeUsuarios" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al panel
        </a>
    </div>
</div>
