<?php
if (!isset($_SESSION['user']) || ($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

// Verificar si estamos en modo edición (después de crear)
$editId = intval($_GET['edit'] ?? 0);
$material = null;
$archivos = [];

if ($editId > 0) {
    $materialModel = new Material();
    $material = $materialModel->getById($editId);
    if ($material) {
        $archivoModel = new MaterialArchivo();
        $archivos = $archivoModel->getByMaterial($editId);
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="<?= BASE_URL ?>/?url=materiales/index" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <h3 class="mb-0"><?= $material ? 'Agregar Archivos' : 'Crear Nuevo Material' ?></h3>
        </div>

        <?php if (!$material): ?>
            <!-- Formulario de creación -->
            <div class="card">
                <div class="card-body">
                    <form id="form-crear-material" class="needs-validation">
                        <!-- Código y Nombre -->
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Código del Producto *</label>
                                <input type="text" name="codigo" class="form-control" placeholder="Ej: MAT-001" required maxlength="50">
                                <small class="form-text text-muted">Código único del material</small>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Nombre del Material *</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Silicona" required maxlength="100">
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalles adicionales del material..."></textarea>
                        </div>

                        <!-- Línea de Trabajo -->
                        <div class="mb-3">
                            <label class="form-label">Línea de Trabajo *</label>
                            <select name="linea_id" class="form-select" required>
                                <option value="">-- Seleccionar línea --</option>
                                <?php foreach ($lineas as $linea): ?>
                                    <option value="<?= $linea['id'] ?>">
                                        <?= htmlspecialchars($linea['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Cantidad y Estado -->
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Cantidad Inicial *</label>
                                <input type="number" name="cantidad" class="form-control" placeholder="0" value="0" min="0" step="1" required>
                                <small class="form-text text-muted">Solo se aceptan números enteros</small>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="1" selected>Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <!-- Errores -->
                        <div id="form-errors" class="alert alert-danger" style="display:none;"></div>

                        <!-- Botones -->
                        <div class="d-flex gap-2 pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Crear Material
                            </button>
                            <a href="<?= BASE_URL ?>/?url=materiales/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Formulario de archivos después de crear -->
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                <strong>Material creado exitosamente: <?= htmlspecialchars($material['nombre']) ?></strong>
            </div>

            <!-- Incluir partial de archivos -->
            <?php include __DIR__ . '/partials/archivos.php'; ?>

            <div class="mt-4">
                <a href="<?= BASE_URL ?>/?url=materiales/index" class="btn btn-primary">
                    <i class="bi bi-check"></i> Terminar
                </a>
                <a href="<?= BASE_URL ?>/?url=materiales/editar&id=<?= $material['id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil"></i> Editar Detalles
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const BASE_URL = "<?= BASE_URL ?>";

const form = document.getElementById('form-crear-material');
if (form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const erroresDiv = document.getElementById('form-errors');

        try {
            const response = await fetch(`${BASE_URL}/?url=materiales/crear`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Redirigir a crear con parámetro edit para mostrar archivos
                window.location.href = `${BASE_URL}/?url=materiales/crear&edit=${data.id}`;
            } else {
                erroresDiv.innerHTML = '<strong>Errores:</strong><ul>';
                data.errors.forEach(err => {
                    erroresDiv.innerHTML += `<li>${err}</li>`;
                });
                erroresDiv.innerHTML += '</ul>';
                erroresDiv.style.display = 'block';
            }
        } catch (error) {
            console.error('Error:', error);
            erroresDiv.innerHTML = 'Error al crear el material. Intenta de nuevo.';
            erroresDiv.style.display = 'block';
        }
    });
}
</script>
