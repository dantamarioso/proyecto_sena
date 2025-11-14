<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3">
                    <?php if (!empty($usuario['foto'])): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($usuario['foto']) ?>"
                             alt="Foto de perfil"
                             class="rounded-circle"
                             width="80" height="80"
                             style="object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-secondary rounded-circle d-flex justify-content-center align-items-center"
                             style="width:80px;height:80px;color:white;">
                            <span><?= strtoupper(substr($usuario['nombre'],0,1)) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <h4 class="mb-1">
                        Bienvenido, <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>
                    </h4>
                    <p class="mb-0 text-muted">
                        Cargo: <?= htmlspecialchars($usuario['cargo']) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <h5>Panel principal</h5>
            <p>Aquí luego puedes cargar el módulo de gestión de inventario (productos, entradas, salidas, etc.).</p>
        </div>
    </div>
</div>
