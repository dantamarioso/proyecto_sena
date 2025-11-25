<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/?url=auth/login");
    exit;
}

$rol = $_SESSION['user']['rol'] ?? 'usuario';
if (!in_array($rol, ['admin', 'dinamizador'])) {
    http_response_code(403);
    echo "Acceso denegado. Solo administradores y dinamizadores pueden editar materiales.";
    exit;
}
?>

<div class="col-12 col-lg-8">
        <div class="d-flex align-items-center gap-2 mb-4">
            <a href="<?= BASE_URL ?>/?url=materiales/index" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <h3 class="mb-0">Editar Material</h3>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="form-editar-material" class="needs-validation">
                    <input type="hidden" name="id" value="<?= $material['id'] ?>">

                    <!-- Código y Nombre -->
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Código del Producto *</label>
                            <input type="text" name="codigo" class="form-control" value="<?= htmlspecialchars($material['codigo']) ?>" placeholder="Ej: MAT-001" required maxlength="50">
                            <small class="form-text text-muted">Código único del material</small>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Nombre del Material *</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($material['nombre']) ?>" placeholder="Ej: Silicona" required maxlength="100">
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalles adicionales del material..."><?= htmlspecialchars($material['descripcion'] ?? '') ?></textarea>
                    </div>

                    <?php 
                        $rol = $_SESSION['user']['rol'] ?? 'usuario';
                    ?>

                    <!-- Nodo (solo Admin puede cambiar) -->
                    <?php if ($rol === 'admin'): ?>
                        <div class="mb-3">
                            <label class="form-label">Nodo *</label>
                            <select name="nodo_id" id="nodo-select" class="form-select" required>
                                <option value="">-- Seleccionar nodo --</option>
                                <?php 
                                    require_once __DIR__ . '/../../models/Nodo.php';
                                    $nodoModel = new Nodo();
                                    $nodos = $nodoModel->getActivosConLineas();
                                    foreach ($nodos as $nodo): 
                                ?>
                                    <option value="<?= $nodo['id'] ?>" <?= $nodo['id'] == $material['nodo_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nodo['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Selecciona el nodo para este material</small>
                        </div>
                    <?php else: ?>
                        <!-- Para usuario/dinamizador: mostrar su nodo actual (no editable) -->
                        <div class="mb-3">
                            <label class="form-label">Nodo Actual</label>
                            <div class="alert alert-info mb-0">
                                <strong>Tu Nodo:</strong> 
                                <?php 
                                    $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
                                    if ($nodo_user) {
                                        require_once __DIR__ . '/../../models/Nodo.php';
                                        $nodoModel = new Nodo();
                                        $nodo = $nodoModel->getById($nodo_user);
                                        echo $nodo ? htmlspecialchars($nodo['nombre']) : 'No asignado';
                                    } else {
                                        echo 'No asignado';
                                    }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Línea de Trabajo -->
                    <div class="mb-3">
                        <label class="form-label">Línea de Trabajo *</label>
                        <select name="linea_id" class="form-select" required>
                            <option value="">-- Seleccionar línea --</option>
                            <?php foreach ($lineas as $linea): ?>
                                <option value="<?= $linea['id'] ?>" <?= $linea['id'] == $material['linea_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($linea['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Cantidad y Estado -->
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Cantidad Actual *</label>
                            <input type="number" name="cantidad" class="form-control" placeholder="0" value="<?= intval($material['cantidad']) ?>" min="0" step="1" required>
                            <small class="form-text text-muted">Solo se aceptan números enteros. Use entrada/salida para registrar cambios</small>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="1" <?= $material['estado'] == 1 ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= $material['estado'] == 0 ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            <strong>Creado:</strong> <?= isset($material['fecha_creacion']) ? date('d/m/Y H:i', strtotime($material['fecha_creacion'])) : 'N/A' ?><br>
                            <strong>Última actualización:</strong> <?= isset($material['fecha_actualizacion']) ? date('d/m/Y H:i', strtotime($material['fecha_actualizacion'])) : 'N/A' ?>
                        </small>
                    </div>

                    <!-- Errores -->
                    <div id="form-errors" class="alert alert-danger" style="display:none;"></div>

                    <!-- Botones -->
                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                        <a href="<?= BASE_URL ?>/?url=materiales/index" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sección de Archivos -->
        <?php include __DIR__ . '/partials/archivos.php'; ?>
    </div>
</div>

<script>
// Manejo de cambio de nodo para admin (solo carga líneas dinámicamente)
const nodoSelect = document.getElementById('nodo-select');
const lineaSelect = document.querySelector('select[name="linea_id"]');

if (nodoSelect && lineaSelect) {
    nodoSelect.addEventListener('change', async function() {
        const nodoId = this.value;
        
        if (!nodoId) {
            lineaSelect.innerHTML = '<option value="">-- Seleccionar línea --</option>';
            return;
        }

        // Cargar líneas del nodo seleccionado
        try {
            const response = await fetch(`${window.BASE_URL}/?url=materiales/obtenerLineasPorNodo&nodo_id=${nodoId}`);
            const data = await response.json();
            
            if (data.success && data.lineas) {
                let html = '<option value="">-- Seleccionar línea --</option>';
                data.lineas.forEach(linea => {
                    html += `<option value="${linea.id}">${linea.nombre}</option>`;
                });
                lineaSelect.innerHTML = html;
            } else {
                lineaSelect.innerHTML = '<option value="">-- Error al cargar líneas --</option>';
            }
        } catch (error) {
            console.error('Error:', error);
            lineaSelect.innerHTML = '<option value="">-- Error al cargar líneas --</option>';
        }
    });
}

document.getElementById('form-editar-material').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    const materialId = formData.get('id');
    const erroresDiv = document.getElementById('form-errors');

    try {
        const response = await fetch(`${window.BASE_URL}/?url=materiales/editar&id=${materialId}`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Material actualizado exitosamente');
            window.location.href = `${window.BASE_URL}/?url=materiales/index`;
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
        erroresDiv.innerHTML = 'Error al actualizar el material. Intenta de nuevo.';
        erroresDiv.style.display = 'block';
    }
});
</script>
