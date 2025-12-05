<?php
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/auth/login');
    exit;
}
?>

<style>
    tbody tr:only-child td {
        display: table-cell !important;
    }

    /* Responsive table adjustments */
    @media (max-width: 576px) {
        .table td {
            padding: 0.5rem 0.25rem !important;
            font-size: 0.875rem;
        }

        .table .btn-group {
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .table .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        .badge {
            font-size: 0.75rem;
        }
    }

    /* Documentos siempre visible */
    .table tbody td:nth-child(8) {
        min-width: 60px;
    }
</style>

<div class="row justify-content-center">
    <div class="col-12">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
            <h3 class="mb-0">Gestión de Inventario</h3>
            <div class="d-flex gap-2 flex-wrap">
                <?php
                $rol = $_SESSION['user']['rol'] ?? 'usuario';
                // Admin y dinamizador pueden crear
                if (in_array($rol, ['admin', 'dinamizador'])) :
                ?>
                    <a href="<?= BASE_URL ?>/materiales/crear" class="btn btn-success btn-sm w-100 w-sm-auto">
                        <i class="bi bi-plus-lg"></i> Nuevo Material
                    </a>
                <?php endif; ?>

                <!-- Importar CSV: Solo admin y dinamizador -->
                <?php if (in_array($rol, ['admin', 'dinamizador'])) : ?>
                    <button type="button" class="btn btn-primary btn-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#modalImportar">
                        <i class="bi bi-upload"></i> Importar CSV
                    </button>
                <?php endif; ?>

                <!-- Exportar: Todos pueden -->
                <button type="button" class="btn btn-success btn-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#modalExportar">
                    <i class="bi bi-download"></i> Descargar
                </button>

                <!-- Todos pueden ver historial -->
                <a href="<?= BASE_URL ?>/materialeshistorial/index" class="btn btn-info btn-sm w-100 w-sm-auto">
                    <i class="bi bi-clock-history"></i> Historial
                </a>
            </div>
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
                            <?php foreach ($lineas as $linea) : ?>
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
                            <?php if (in_array($_SESSION['user']['rol'] ?? 'usuario', ['admin', 'dinamizador'])) : ?>
                                <th class="d-none d-lg-table-cell">Nodo</th>
                            <?php endif; ?>
                            <th class="d-none d-md-table-cell">Línea</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Documentos</th>
                            <th class="d-none d-sm-table-cell">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="materiales-body">
                        <?php foreach ($materiales as $m) : ?>
                            <tr class="material-row" data-id="<?= $m['id'] ?>">
                                <td><?= $m['id'] ?></td>
                                <td><code><?= htmlspecialchars($m['codigo']) ?></code></td>
                                <td><strong><?= htmlspecialchars($m['nombre']) ?></strong></td>
                                <td class="d-none d-md-table-cell">
                                    <small><?= htmlspecialchars(substr($m['descripcion'], 0, 50)) ?><?= strlen($m['descripcion']) > 50 ? '...' : '' ?></small>
                                </td>
                                <?php if (in_array($_SESSION['user']['rol'] ?? 'usuario', ['admin', 'dinamizador'])) : ?>
                                    <td class="d-none d-lg-table-cell">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($m['nodo_nombre'] ?? 'N/A') ?></span>
                                    </td>
                                <?php endif; ?>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge bg-primary"><?= htmlspecialchars($m['linea_nombre'] ?? 'N/A') ?></span>
                                </td>
                                <td class="text-center">
                                    <strong><?= intval($m['cantidad']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/materiales/detalles?id=<?= $m['id'] ?>" class="text-decoration-none" title="Ver detalles y archivos">
                                        <span class="badge bg-secondary" id="docs-<?= $m['id'] ?>" style="cursor: pointer;">
                                            <i class="bi bi-hourglass-split"></i> Cargando...
                                        </span>
                                    </a>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <?php if ($m['estado'] == 1) : ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Ver: todos pueden -->
                                        <button class="btn btn-info btn-sm btn-ver" title="Ver detalles" data-id="<?= $m['id'] ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <?php
                                        $rol = $_SESSION['user']['rol'] ?? 'usuario';
                                        $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
                                        $linea_user = $_SESSION['user']['linea_id'] ?? null;

                                        $esDelUsuario = ($m['nodo_id'] == $nodo_user);
                                        $esDelUsuarioYLinea = $esDelUsuario && ($m['linea_id'] == $linea_user);

                                        // Entrada/Salida: Admin y Dinamizador su nodo, Usuario su nodo+linea
                                        if (
                                            ($rol === 'admin') ||
                                            ($rol === 'dinamizador' && $esDelUsuario) ||
                                            ($rol === 'usuario' && $esDelUsuarioYLinea)
                                        ) :
                                        ?>
                                            <button class="btn btn-warning btn-sm btn-entrada" title="Entrada" data-id="<?= $m['id'] ?>">
                                                <i class="bi bi-plus-square"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-salida" title="Salida" data-id="<?= $m['id'] ?>">
                                                <i class="bi bi-dash-square"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php
                                        // Editar: Admin y Dinamizador su nodo
                                        if (($rol === 'admin') || ($rol === 'dinamizador' && $esDelUsuario)) :
                                        ?>
                                            <a href="<?= BASE_URL ?>/materiales/editar?id=<?= $m['id'] ?>"
                                                class="btn btn-primary btn-sm" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php
                                        // Eliminar: Admin y Dinamizador su nodo
                                        if (($rol === 'admin') || ($rol === 'dinamizador' && $esDelUsuario)) :
                                        ?>
                                            <button class="btn btn-danger btn-sm btn-eliminar" title="Eliminar" data-id="<?= $m['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($materiales)) : ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">
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
                            <?php foreach ($estadoLineas as $linea) : ?>
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

<!-- Modal: Importar CSV -->
<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar Materiales desde CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <strong><i class="bi bi-info-circle"></i> Instrucciones:</strong>
                    <ul class="mb-0 mt-2">
                        <ul>
                            <li>El archivo debe ser <strong>CSV, TXT o XLSX</strong> con máximo <strong>5 MB</strong></li>
                            <li>Campos requeridos: <strong>Código</strong>, <strong>Nombre</strong>, <strong>Línea o Linea_ID</strong></li>
                            <li>Campos opcionales: Descripción, Cantidad, Estado, Nodo_ID</li>
                            <li>Los datos se limpiarán automáticamente (espacios, mayúsculas, etc.)</li>
                            <li>Para CSV/TXT: Se detectará automáticamente el delimitador (coma, punto y coma, tabulación)</li>
                        </ul>
                </div>

                <form id="formularioImportar" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="archivo-importar" class="form-label">Selecciona el archivo</label>
                        <input type="file" class="form-control" id="archivo-importar" name="archivo" accept=".csv,.txt,.xlsx,.xls" required>
                        <small class="form-text text-muted">CSV, TXT o XLSX (máximo 5 MB)</small>
                    </div>

                    <div id="import-preview" style="display: none;">
                        <div class="alert alert-warning">
                            <strong>Vista previa:</strong>
                            <div style="max-height: 200px; overflow-y: auto; margin-top: 10px;">
                                <pre id="preview-content" style="margin: 0; font-size: 0.85rem;"></pre>
                            </div>
                        </div>
                    </div>

                    <div id="import-errors" class="alert alert-danger" style="display: none;">
                        <strong>Errores encontrados:</strong>
                        <ul id="import-error-list" class="mb-0 mt-2"></ul>
                    </div>

                    <div id="import-success" class="alert alert-success" style="display: none;">
                        <div id="import-success-message"></div>
                        <hr>
                        <div id="import-warnings" style="display: none;">
                            <strong>Advertencias:</strong>
                            <ul id="import-warning-list" class="mb-0 mt-2"></ul>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-importar">
                    <i class="bi bi-upload"></i> Importar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Seleccionar formato de descarga -->
<div class="modal fade" id="modalExportar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Descargar Materiales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Selecciona el formato en el que deseas descargar los materiales:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-success btn-lg" id="btn-descargar-xlsx">
                        <i class="bi bi-file-earmark-excel"></i> Descargar como Excel (XLSX)
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-lg" id="btn-descargar-csv">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Descargar como CSV
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg" id="btn-descargar-txt">
                        <i class="bi bi-file-earmark-text"></i> Descargar como TXT
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-lg" id="btn-descargar-pdf">
                        <i class="bi bi-file-earmark-pdf"></i> Descargar como PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Seleccionar descarga CSV (solo o ZIP con 3 CSVs) -->
<div class="modal fade" id="modalDescargarCSV" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Descargar como CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">¿Cómo deseas descargar los datos?</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-lg" id="btn-csv-solo">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Solo Materiales (CSV)
                    </button>
                    <button type="button" class="btn btn-outline-info btn-lg" id="btn-csv-zip">
                        <i class="bi bi-file-earmark-zip"></i> ZIP con 3 Archivos (Materiales, Líneas, Nodos)
                    </button>
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
                    <small class="form-text text-muted" id="mov-cantidad-help">Solo se aceptan números enteros</small>
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

<script>
    // Cargar conteo de documentos para cada material
    document.addEventListener('DOMContentLoaded', () => {
        const materials = document.querySelectorAll('[id^="docs-"]');
        materials.forEach(badge => {
            const match = badge.id.match(/docs-(\d+)/);
            if (match) {
                const materialId = match[1];
                cargarDocumentos(materialId);
            }
        });

        // Botones de descarga
        document.getElementById('btn-descargar-xlsx').addEventListener('click', () => {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';

            fetch(`${window.BASE_URL}/?url=materialesexport/exportar&formato=excel`)
                .then(response => {
                    if (!response.ok) throw new Error('Error descargando');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `materiales_${new Date().toISOString().slice(0, 10)}.xlsx`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);

                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalExportar'));
                    if (modal) modal.hide();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error descargando archivo');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });

        document.getElementById('btn-descargar-csv').addEventListener('click', () => {
            // Mostrar modal para elegir entre CSV solo o ZIP
            const modalDescargarCSV = new bootstrap.Modal(document.getElementById('modalDescargarCSV'));

            // Cerrar modal de exportar
            const modalExportar = bootstrap.Modal.getInstance(document.getElementById('modalExportar'));
            if (modalExportar) modalExportar.hide();

            modalDescargarCSV.show();
        });

        document.getElementById('btn-descargar-txt').addEventListener('click', () => {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';

            fetch(`${window.BASE_URL}/?url=materialesexport/exportar&formato=txt`)
                .then(response => {
                    if (!response.ok) throw new Error('Error descargando');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `materiales_${new Date().toISOString().slice(0, 10)}.txt`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);

                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalExportar'));
                    if (modal) modal.hide();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error descargando archivo');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });
        document.getElementById('btn-descargar-pdf').addEventListener('click', () => {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';

            fetch(`${window.BASE_URL}/?url=materialesexport/exportar&formato=pdf`)
                .then(response => {
                    if (!response.ok) throw new Error('Error descargando');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `materiales_${new Date().toISOString().slice(0, 10)}.pdf`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);

                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalExportar'));
                    if (modal) modal.hide();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error descargando archivo');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });

        // Botones del modal de descargar CSV
        document.getElementById('btn-csv-solo').addEventListener('click', () => {
            const btn = document.getElementById('btn-descargar-csv');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';

            fetch(`${window.BASE_URL}/?url=materialesexport/exportar&formato=csv`)
                .then(response => {
                    if (!response.ok) throw new Error('Error descargando');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `materiales_${new Date().toISOString().slice(0, 10)}.csv`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);

                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalDescargarCSV'));
                    if (modal) modal.hide();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error descargando archivo');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });

        document.getElementById('btn-csv-zip').addEventListener('click', () => {
            const btn = document.getElementById('btn-descargar-csv');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando...';

            fetch(`${window.BASE_URL}/?url=materialesexport/exportar&formato=zip`)
                .then(response => {
                    if (!response.ok) throw new Error('Error descargando');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `materiales_${new Date().toISOString().slice(0, 10)}.zip`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);

                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalDescargarCSV'));
                    if (modal) modal.hide();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error descargando archivo');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        });
    });

    function cargarDocumentos(materialId) {
        const badgeElement = document.getElementById(`docs-${materialId}`);
        if (!badgeElement) return;

        fetch(`${window.BASE_URL}/materialesarchivos/contar?material_id=${materialId}`)
            .then(response => response.json())
            .then(data => {
                let badge = '';
                if (data.count === 0) {
                    badge = '<span class="badge bg-secondary">Sin docs</span>';
                } else {
                    badge = `<span class="badge bg-primary">${data.count} doc${data.count !== 1 ? 's' : ''}</span>`;
                }
                badgeElement.innerHTML = badge;
            })
            .catch(err => {
                console.error("Error cargando documentos:", err);
                badgeElement.innerHTML = '<span class="badge bg-danger">Error</span>';
            });
    }
</script>