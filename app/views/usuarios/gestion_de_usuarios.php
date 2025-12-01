<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/auth/login");
    exit;
}
?>

<style>
    tbody tr:only-child td {
        display: table-cell !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-12">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
            <h3 class="mb-0">Panel de Usuarios</h3>
            <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-success btn-sm w-100 w-sm-auto">
                <i class="bi bi-plus-lg"></i> Nuevo usuario
            </a>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" id="busqueda" class="form-control" placeholder="Nombre, correo o usuario">
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label">Estado</label>
                        <select id="filtro-estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Bloqueados</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="form-label">Rol</label>
                        <select id="filtro-rol" class="form-select">
                            <option value="">Todos</option>
                            <option value="admin">Admin</option>
                            <option value="usuario">Usuario</option>
                            <option value="dinamizador">Dinamizador</option>
                            <option value="invitado">Invitado</option>
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
                        <th style="width: 40px;">#</th>
                        <th style="width: 50px;">Foto</th>
                        <th>Nombre</th>
                        <th style="min-width: 180px;">Correo</th>
                        <th style="min-width: 120px;">Usuario</th>
                        <th style="min-width: 100px;">Rol</th>
                        <th style="min-width: 100px;">Nodo</th>
                        <th style="min-width: 100px;">Línea</th>
                        <th style="width: 80px;">Estado</th>
                        <th style="width: 120px;" class="text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="usuarios-body">
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td>
                                <?php if ($u['foto']): ?>
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($u['foto']) ?>"
                                         width="40" height="40" class="rounded-circle" style="object-fit:cover;">
                                <?php else: ?>
                                    <img src="<?= BASE_URL ?>/img/default_user.png"
                                         width="40" height="40" class="rounded-circle" style="object-fit:cover;" alt="Usuario sin foto">
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($u['nombre']) ?></strong></td>
                            <td><small><?= htmlspecialchars($u['correo']) ?></small></td>
                            <td><small><?= htmlspecialchars($u['nombre_usuario']) ?></small></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($u['rol'] ?? 'usuario') ?></span></td>
                            <td>
                                <?php if ($u['rol'] !== 'admin'): ?>
                                    <?php 
                                    if ($u['nodo_id']): 
                                        $nodo_nombre = '';
                                        foreach ($nodos as $n) {
                                            if ($n['id'] == $u['nodo_id']) {
                                                $nodo_nombre = $n['nombre'];
                                                break;
                                            }
                                        }
                                        echo $nodo_nombre ? '<span class="badge bg-secondary">' . htmlspecialchars($nodo_nombre) . '</span>' : '<span class="text-muted small">—</span>';
                                    else:
                                        echo '<span class="text-muted small">—</span>';
                                    endif;
                                    ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['rol'] === 'usuario'): ?>
                                    <?php 
                                    if ($u['linea_id']): 
                                        $linea_nombre = '';
                                        foreach ($nodos as $n) {
                                            if (isset($n['lineas']) && is_array($n['lineas'])) {
                                                foreach ($n['lineas'] as $l) {
                                                    if ($l['id'] == $u['linea_id']) {
                                                        $linea_nombre = $l['nombre'];
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                        echo $linea_nombre ? '<span class="badge bg-warning">' . htmlspecialchars($linea_nombre) . '</span>' : '<span class="text-muted small">—</span>';
                                    else:
                                        echo '<span class="text-muted small">—</span>';
                                    endif;
                                    ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Bloqueado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/usuarios/detalles?id=<?= $u['id'] ?>"
                                       class="btn btn-info btn-sm" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/usuarios/editar?id=<?= $u['id'] ?>"
                                       class="btn btn-primary btn-sm" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-secondary btn-sm btn-asignar-nodo" 
                                            data-id="<?= $u['id'] ?>" 
                                            data-nombre="<?= htmlspecialchars($u['nombre']) ?>"
                                            data-rol="<?= $u['rol'] ?>"
                                            data-nodo="<?= $u['nodo_id'] ?? '' ?>"
                                            data-linea="<?= $u['linea_id'] ?? '' ?>"
                                            title="Asignar nodo/línea">
                                        <i class="bi bi-map"></i>
                                    </button>

                                    <?php if ($u['estado'] == 1): ?>
                                        <form class="d-inline" method="post"
                                              action="<?= BASE_URL ?>/usuarios/bloquear">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button class="btn btn-warning btn-sm" type="submit" title="Bloquear">
                                                <i class="bi bi-ban"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form class="d-inline" method="post"
                                              action="<?= BASE_URL ?>/usuarios/desbloquear">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button class="btn btn-success btn-sm" type="submit" title="Desbloquear">
                                                <i class="bi bi-unlock"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $u['id'] ?>" data-nombre="<?= htmlspecialchars($u['nombre']) ?>" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="card-footer bg-light">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                    <small id="usuarios-info" class="text-muted"></small>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" id="btn-prev">&laquo;</button>
                        <span id="pagina-actual" class="mx-2">1</span>
                        <button class="btn btn-sm btn-outline-secondary" id="btn-next">&raquo;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar nodo y línea -->
<div class="modal fade" id="modalAsignarNodo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Nodo y Línea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAsignarNodo">
                <div class="modal-body">
                    <input type="hidden" id="usuario-id">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong id="usuario-nombre"></strong></label>
                        <small class="d-block text-muted">Rol: <span id="usuario-rol"></span></small>
                    </div>

                    <div class="mb-3" id="div-nodo-modal">
                        <label class="form-label">Nodo <span class="text-danger">*</span></label>
                        <select id="select-nodo" class="form-select" required>
                            <option value="">-- Selecciona un nodo --</option>
                            <?php foreach ($nodos as $nodo): ?>
                                <option value="<?= $nodo['id'] ?>"><?= htmlspecialchars($nodo['nombre']) ?> (<?= htmlspecialchars($nodo['ciudad']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="div-linea" style="display: none;">
                        <label class="form-label">Línea <span class="text-danger">*</span></label>
                        <select id="select-linea" class="form-select">
                            <option value="">-- Selecciona una línea --</option>
                        </select>
                        <small class="text-muted">Solo para usuarios. Dinamizadores tienen acceso a todas las líneas del nodo.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('modalAsignarNodo'));
        const nodoSelect = document.getElementById('select-nodo');
        const lineaSelect = document.getElementById('select-linea');
        const divLinea = document.getElementById('div-linea');
        const divNodo = document.getElementById('div-nodo-modal');
        const formAsignar = document.getElementById('formAsignarNodo');
        
        // Datos de nodos y líneas
        const nodosData = <?= json_encode($nodos) ?>;

        // Cargar líneas cuando cambia el nodo
        nodoSelect.addEventListener('change', function() {
            const nodoId = this.value;
            lineaSelect.innerHTML = '<option value="">-- Selecciona una línea --</option>';
            
            if (nodoId) {
                const nodo = nodosData.find(n => n.id == nodoId);
                if (nodo && nodo.lineas) {
                    nodo.lineas.forEach(linea => {
                        const option = document.createElement('option');
                        option.value = linea.id;
                        option.textContent = linea.nombre;
                        lineaSelect.appendChild(option);
                    });
                }
            }
        });

        // Función para mostrar/ocultar campos según el rol
        function actualizarCamposSegunRol(rol) {
            if (rol === 'admin') {
                // Admin: ocultar nodo y línea
                divNodo.style.display = 'none';
                divLinea.style.display = 'none';
                nodoSelect.removeAttribute('required');
                lineaSelect.removeAttribute('required');
            } else if (rol === 'dinamizador') {
                // Dinamizador: mostrar nodo, ocultar línea
                divNodo.style.display = 'block';
                divLinea.style.display = 'none';
                nodoSelect.setAttribute('required', 'required');
                lineaSelect.removeAttribute('required');
            } else if (rol === 'usuario') {
                // Usuario: mostrar nodo y línea
                divNodo.style.display = 'block';
                divLinea.style.display = 'block';
                nodoSelect.setAttribute('required', 'required');
                lineaSelect.setAttribute('required', 'required');
            } else {
                // Invitado: ocultar nodo y línea
                divNodo.style.display = 'none';
                divLinea.style.display = 'none';
                nodoSelect.removeAttribute('required');
                lineaSelect.removeAttribute('required');
            }
        }

        // Manejar clic en botones de asignar nodo
        document.querySelectorAll('.btn-asignar-nodo').forEach(btn => {
            btn.addEventListener('click', function() {
                const usuarioId = this.dataset.id;
                const usuarioNombre = this.dataset.nombre;
                const usuarioRol = this.dataset.rol;
                const usuarioNodo = this.dataset.nodo;
                const usuarioLinea = this.dataset.linea;

                document.getElementById('usuario-id').value = usuarioId;
                document.getElementById('usuario-nombre').textContent = usuarioNombre;
                document.getElementById('usuario-rol').textContent = usuarioRol;
                
                // Actualizar campos visibles según rol
                actualizarCamposSegunRol(usuarioRol);

                // Cargar nodo actual si existe
                if (usuarioNodo) {
                    nodoSelect.value = usuarioNodo;
                    nodoSelect.dispatchEvent(new Event('change'));
                    if (usuarioLinea && usuarioRol === 'usuario') {
                        setTimeout(() => {
                            lineaSelect.value = usuarioLinea;
                        }, 100);
                    }
                }

                modal.show();
            });
        });

        // Manejar submit del formulario
        formAsignar.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const usuarioId = document.getElementById('usuario-id').value;
            const nodoId = nodoSelect.value || null;
            const lineaId = lineaSelect.value || null;
            const rol = document.getElementById('usuario-rol').textContent;

            // Validar según el rol
            if (rol === 'usuario' && !nodoId) {
                alert('Debe seleccionar un nodo');
                return;
            }

            if (rol === 'usuario' && !lineaId) {
                alert('Debe seleccionar una línea para usuarios');
                return;
            }

            if (rol === 'dinamizador' && !nodoId) {
                alert('Debe seleccionar un nodo para dinamizadores');
                return;
            }

            // Enviar datos al servidor
            fetch('<?= BASE_URL ?>/usuarios/asignarNodo', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'usuario_id=' + usuarioId + '&nodo_id=' + (nodoId || '') + '&linea_id=' + (lineaId || '')
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Nodo/Línea asignado correctamente');
                    modal.hide();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al asignar nodo/línea');
            });
        });
    });
</script>
