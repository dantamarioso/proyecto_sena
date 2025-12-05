<?php
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/auth/login');
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">

                <!-- Foto de perfil grande -->
                <div style="position: relative; width: 150px; height: 150px; margin: 0 auto 20px;">
                    <img id="fotoPerfil"
                         src="<?= !empty($usuario['foto']) ? BASE_URL . '/' . htmlspecialchars($usuario['foto']) : BASE_URL . '/img/default_user.png' ?>"
                         width="150" height="150"
                         style="object-fit:cover; border-radius:50%; border:4px solid #00304D; cursor:pointer; display: block; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);"
                         title="Haz clic para cambiar la foto o ver a tamaño completo">

                    <!-- Overlay para cambiar foto -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 50%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; cursor: pointer;"
                         id="fotoOverlay">
                        <i class="bi bi-pencil-fill" style="color: white; font-size: 1.5rem; margin-right: 5px;"></i>
                        <span style="color: white; font-size: 0.85rem; font-weight: 600;">Cambiar</span>
                    </div>

                    <!-- Input oculto para archivo -->
                    <input type="file" id="inputFoto" accept="image/*" style="display: none;">
                </div>

                <!-- Información del usuario -->
                <h3 class="mb-1"><?= htmlspecialchars($usuario['nombre']) ?></h3>
                <p class="text-muted mb-3">
                    <span class="badge bg-info"><?= htmlspecialchars($usuario['rol'] ?? 'usuario') ?></span>
                </p>

                <div class="list-group list-group-flush mb-3">
                    <div class="list-group-item">
                        <small class="text-muted">Correo</small><br>
                        <strong><?= htmlspecialchars($usuario['correo']) ?></strong>
                    </div>
                    <div class="list-group-item">
                        <small class="text-muted">Usuario</small><br>
                        <strong><?= htmlspecialchars($usuario['nombre_usuario']) ?></strong>
                    </div>
                    <?php if (!empty($usuario['celular'])) : ?>
                        <div class="list-group-item">
                            <small class="text-muted">Celular</small><br>
                            <strong><?= htmlspecialchars($usuario['celular']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($usuario['cargo'])) : ?>
                        <div class="list-group-item">
                            <small class="text-muted">Cargo</small><br>
                            <strong><?= htmlspecialchars($usuario['cargo']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <div class="list-group-item">
                        <small class="text-muted">Estado</small><br>
                        <?php if ($usuario['estado'] == 1) : ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else : ?>
                            <span class="badge bg-danger">Bloqueado</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/perfil/editar" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-pencil"></i> Editar perfil
                    </a>

                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar foto -->
<div class="modal fade" id="modalCambiarFoto" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar foto de perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewFoto" src="" class="image-preview"
                     style="display:none; cursor:zoom-in;" alt="Vista previa">
                <p id="textoEspera" class="text-muted">Seleccionando archivo...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarFoto" style="display:none;">
                    <i class="bi bi-check-lg"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para zoom de imagen -->
<div class="modal-zoom-image" id="modalZoomImage">
    <div class="zoom-image-content">
        <button class="zoom-image-close" id="zoomImageClose">&times;</button>
        <img id="zoomImageSrc" src="" alt="Zoom imagen">
    </div>
</div>

<!-- Contenedor de toasts -->
<div id="toast-container"></div>
