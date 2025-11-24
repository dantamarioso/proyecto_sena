<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="mb-3">Editar usuario</h3>

                <script>const BASE_URL = "<?= BASE_URL ?>";</script>

                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre" class="form-control"
                               value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                    </div>

                    <!-- Correo -->
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control"
                               value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                    </div>

                    <!-- Usuario -->
                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario</label>
                        <div class="input-group">
                            <input type="text" name="nombre_usuario" id="nombre_usuario_edit" class="form-control" 
                                   value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" required>
                            <span class="input-group-text" id="iconoUsuarioEdit" style="cursor:default;">
                                <i class="bi bi-question-circle"></i>
                            </span>
                        </div>
                        <small id="mensajeUsuarioEdit" class="form-text"></small>
                    </div>

                    <!-- Celular -->
                    <div class="mb-3">
                        <label class="form-label">Celular</label>
                        <input type="text" name="celular" class="form-control"
                               value="<?= htmlspecialchars($usuario['celular'] ?? '') ?>"
                               placeholder="Ej: +57 123 456 7890">
                    </div>

                    <!-- Cargo -->
                    <div class="mb-3">
                        <label class="form-label">Cargo</label>
                        <input type="text" name="cargo" class="form-control"
                               value="<?= htmlspecialchars($usuario['cargo'] ?? '') ?>"
                               placeholder="Ej: Gerente">
                    </div>

                    <!-- Rol -->
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" id="select-rol-edit" class="form-select">
                            <option value="admin"       <?= ($usuario['rol'] ?? '') === 'admin'       ? 'selected' : '' ?>>Admin</option>
                            <option value="dinamizador" <?= ($usuario['rol'] ?? '') === 'dinamizador' ? 'selected' : '' ?>>Dinamizador</option>
                            <option value="usuario"     <?= ($usuario['rol'] ?? '') === 'usuario'     ? 'selected' : '' ?>>Usuario</option>
                            <option value="invitado"    <?= ($usuario['rol'] ?? '') === 'invitado'    ? 'selected' : '' ?>>Invitado</option>
                        </select>
                    </div>

                    <!-- Nodo -->
                    <div class="mb-3" id="div-nodo-edit" style="display: none;">
                        <label class="form-label">Nodo <span class="text-danger">*</span></label>
                        <select name="nodo_id" id="select-nodo-edit" class="form-select">
                            <option value="">-- Selecciona un nodo --</option>
                            <?php foreach ($nodos as $nodo): ?>
                                <option value="<?= $nodo['id'] ?>" <?= ($usuario['nodo_id'] ?? '') == $nodo['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nodo['nombre']) ?> (<?= htmlspecialchars($nodo['ciudad']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Línea -->
                    <div class="mb-3" id="div-linea-edit" style="display: none;">
                        <label class="form-label">Línea <span class="text-danger">*</span></label>
                        <select name="linea_id" id="select-linea-edit" class="form-select">
                            <option value="">-- Selecciona una línea --</option>
                        </select>
                        <small class="text-muted">Solo para usuarios. Dinamizadores tienen acceso a todas las líneas del nodo.</small>
                    </div>

                    <!-- Foto -->
                    <div class="mb-3">
                        <label class="form-label">Foto de perfil</label>
                        <input type="file" name="foto" id="foto_editar" class="form-control" accept="image/*">
                        <small class="text-muted">Formatos permitidos: JPG, PNG — Máximo 2MB.</small>

                        <!-- PREVIEW ACTUAL O NUEVA -->
                        <div class="mt-3" id="previewContainerEditar" style="<?= !empty($usuario['foto']) ? '' : 'display:none;' ?>">
                            <label class="form-label">Foto actual/nueva</label>
                            <img id="preview_editar"
                                 src="<?= !empty($usuario['foto']) ? BASE_URL . '/' . htmlspecialchars($usuario['foto']) : BASE_URL . '/img/default_user.png' ?>"
                                 width="80" height="80"
                                 style="object-fit:cover;border-radius:50%;border:3px solid #3b82f6;display:block;">
                        </div>
                    </div>

                    <!-- Nueva contraseña -->
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña (opcional)</label>
                        <div class="input-group">
                            <input type="password" id="password_edit" name="password" class="form-control"
                                   placeholder="Dejar vacío para mantener la actual">
                            <span class="input-group-text" id="togglePasswordEdit" style="cursor:pointer;">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                        <small class="text-muted">Déjalo vacío para no cambiarla.</small>
                    </div>

                    <!-- Checklist de Contraseña -->
                    <div id="checklistEdit" class="password-checklist mb-3" style="display:none;">
                        <p class="mb-1" style="font-size:0.9rem; font-weight:600;">La contraseña debe incluir:</p>
                        <ul>
                            <li id="chk-length-edit" class="invalid">✖ Mínimo 8 caracteres</li>
                            <li id="chk-uppercase-edit" class="invalid">✖ Al menos una letra mayúscula</li>
                            <li id="chk-special-edit" class="invalid">✖ Al menos un carácter especial (!@#$%&*)</li>
                        </ul>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="1" <?= $usuario['estado'] == 1 ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= $usuario['estado'] == 0 ? 'selected' : '' ?>>Bloqueado</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="<?= BASE_URL ?>/?url=usuarios/gestionDeUsuarios" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-check-lg"></i> Guardar cambios
                        </button>
                    </div>
                </form>


            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rolSelect = document.getElementById('select-rol-edit');
        const nodoSelect = document.getElementById('select-nodo-edit');
        const lineaSelect = document.getElementById('select-linea-edit');
        const divNodo = document.getElementById('div-nodo-edit');
        const divLinea = document.getElementById('div-linea-edit');

        // Datos de nodos con líneas
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

        // Mostrar/ocultar campos según el rol
        function actualizarCamposSegunRol() {
            const rol = rolSelect.value;
            
            if (rol === 'admin') {
                // Admin no necesita nodo
                divNodo.style.display = 'none';
                divLinea.style.display = 'none';
                nodoSelect.removeAttribute('required');
                lineaSelect.removeAttribute('required');
            } else if (rol === 'dinamizador') {
                // Dinamizador necesita nodo pero no línea
                divNodo.style.display = 'block';
                divLinea.style.display = 'none';
                nodoSelect.setAttribute('required', 'required');
                lineaSelect.removeAttribute('required');
            } else if (rol === 'usuario') {
                // Usuario necesita nodo y línea
                divNodo.style.display = 'block';
                divLinea.style.display = 'block';
                nodoSelect.setAttribute('required', 'required');
                lineaSelect.setAttribute('required', 'required');
            } else {
                // Invitado no necesita nada
                divNodo.style.display = 'none';
                divLinea.style.display = 'none';
                nodoSelect.removeAttribute('required');
                lineaSelect.removeAttribute('required');
            }
        }

        // Cambio de rol
        rolSelect.addEventListener('change', function() {
            actualizarCamposSegunRol();
        });

        // Inicializar en carga
        actualizarCamposSegunRol();
        
        // Si hay nodo seleccionado, cargar sus líneas
        if (nodoSelect.value) {
            nodoSelect.dispatchEvent(new Event('change'));
        }
    });
</script>

