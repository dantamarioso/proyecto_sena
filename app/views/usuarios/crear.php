<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">

                <h3 class="mb-3">Crear nuevo usuario</h3>

                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" id="crear-usuario-form">

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre_completo" class="form-control" required>
                    </div>

                    <!-- Correo -->
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <div class="input-group">
                            <input type="email" name="correo" class="form-control" required>
                            <span class="input-group-text" id="iconoCorreoCrear" style="cursor:default;">
                                <i class="bi bi-question-circle"></i>
                            </span>
                        </div>
                        <small id="mensajeCorreoCrear" class="form-text"></small>
                    </div>

                    <!-- Usuario -->
                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario</label>
                        <div class="input-group">
                            <input type="text" name="nombre_usuario" id="nombre_usuario_crear" class="form-control" required>
                            <span class="input-group-text" id="iconoUsuarioCrear" style="cursor:default;">
                                <i class="bi bi-question-circle"></i>
                            </span>
                        </div>
                        <small id="mensajeUsuarioCrear" class="form-text"></small>
                    </div>

                    <!-- Celular -->
                    <div class="mb-3">
                        <label class="form-label">Celular (opcional)</label>
                        <input type="text" name="celular" class="form-control" placeholder="Ej: +57 123 456 7890">
                    </div>

                    <!-- Cargo -->
                    <div class="mb-3">
                        <label class="form-label">Cargo (opcional)</label>
                        <input type="text" name="cargo" class="form-control" placeholder="Ej: Gerente">
                    </div>

                    <!-- Foto -->
                    <div class="mb-3">
                        <label class="form-label">Foto de perfil (opcional)</label>
                        <input type="file" name="foto" id="foto_crear" class="form-control" accept="image/*">
                        <small class="text-muted">Formatos permitidos: JPG, PNG — Máximo 2MB.</small>

                        <!-- PREVIEW -->
                        <div class="mt-3 d-none" id="previewContainerCrear">
                            <label class="form-label">Vista previa</label>
                            <img id="preview_crear" src="" 
                                 width="80" height="80"
                                 style="object-fit:cover;border-radius:50%;border:3px solid #3b82f6;display:block;">
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password_crear" class="form-control" required>
                            <span class="input-group-text" id="togglePasswordCrear" style="cursor:pointer;">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Repetir contraseña -->
                    <div class="mb-3">
                        <label class="form-label">Repetir contraseña</label>
                        <div class="input-group">
                            <input type="password" name="password2" id="password2_crear" class="form-control" required>
                            <span class="input-group-text" id="togglePassword2Crear" style="cursor:pointer;">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                        <small id="matchMessageCrear" class="text-danger d-none">Las contraseñas no coinciden</small>
                    </div>

                    <!-- Checklist de Contraseña -->
                    <div id="checklistCrear" class="password-checklist mb-3">
                        <p class="mb-1" style="font-size:0.9rem; font-weight:600;">La contraseña debe incluir:</p>
                        <ul>
                            <li id="chk-length-crear" class="invalid">✖ Mínimo 8 caracteres</li>
                            <li id="chk-uppercase-crear" class="invalid">✖ Al menos una letra mayúscula</li>
                            <li id="chk-special-crear" class="invalid">✖ Al menos un carácter especial (!@#$%&*)</li>
                        </ul>
                    </div>

                    <!-- Rol -->
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" id="select-rol" class="form-select">
                            <option value="usuario" selected>Usuario</option>
                            <option value="dinamizador">Dinamizador</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <!-- Nodo (para usuario y dinamizador) -->
                    <div class="mb-3" id="div-nodo">
                        <label class="form-label">Nodo <span class="text-danger">*</span></label>
                        <select name="nodo_id" id="select-nodo" class="form-select" required>
                            <option value="">-- Selecciona un nodo --</option>
                            <?php foreach ($nodos as $nodo): ?>
                                <option value="<?= $nodo['id'] ?>"><?= htmlspecialchars($nodo['nombre']) ?> (<?= htmlspecialchars($nodo['ciudad']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Requerido para usuarios y dinamizadores</small>
                    </div>

                    <!-- Línea (solo para usuarios) -->
                    <div class="mb-3" id="div-linea" style="display: none;">
                        <label class="form-label">Línea <span class="text-danger">*</span></label>
                        <select name="linea_id" id="select-linea" class="form-select">
                            <option value="">-- Selecciona una línea --</option>
                        </select>
                        <small class="text-muted">Requerido solo para usuarios</small>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="1" selected>Activo</option>
                            <option value="0">Bloqueado</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <a href="<?= BASE_URL ?>/?url=usuarios/gestionDeUsuarios" class="btn btn-secondary flex-grow-1">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="bi bi-check-lg"></i> Crear usuario
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rolSelect = document.getElementById('select-rol');
        const nodoSelect = document.getElementById('select-nodo');
        const lineaSelect = document.getElementById('select-linea');
        const divNodo = document.getElementById('div-nodo');
        const divLinea = document.getElementById('div-linea');

        // Datos de nodos - convertir a array si es necesario
        let nodosData = <?= json_encode($nodos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        if (!Array.isArray(nodosData)) {
            nodosData = Object.values(nodosData || {});
        }

        /**
         * Actualizar visibilidad de campos según el rol
         */
        function actualizarCamposPorRol() {
            const rol = rolSelect.value;
            
            // Por defecto, ocultar todo
            divNodo.style.display = 'none';
            divLinea.style.display = 'none';
            
            nodoSelect.removeAttribute('required');
            lineaSelect.removeAttribute('required');
            
            // Mostrar campos según el rol
            if (rol === 'usuario') {
                // Usuario: nodo y línea
                divNodo.style.display = 'block';
                divLinea.style.display = 'block';
                nodoSelect.setAttribute('required', 'required');
                lineaSelect.setAttribute('required', 'required');
            } else if (rol === 'dinamizador') {
                // Dinamizador: solo nodo
                divNodo.style.display = 'block';
                nodoSelect.setAttribute('required', 'required');
            }
            // Admin: no muestra nada (por defecto oculto)
            
            // Limpiar campos
            nodoSelect.value = '';
            lineaSelect.value = '';
            lineaSelect.innerHTML = '<option value="">-- Selecciona una línea --</option>';
        }

        // Cambio de rol
        rolSelect.addEventListener('change', actualizarCamposPorRol);

        // Cambio de nodo - cargar líneas
        nodoSelect.addEventListener('change', function() {
            const nodoId = this.value;
            lineaSelect.innerHTML = '<option value="">-- Selecciona una línea --</option>';
            
            if (nodoId && Array.isArray(nodosData) && nodosData.length > 0) {
                // Buscar el nodo en el array
                let nodoEncontrado = null;
                for (let i = 0; i < nodosData.length; i++) {
                    if (String(nodosData[i].id) === String(nodoId)) {
                        nodoEncontrado = nodosData[i];
                        break;
                    }
                }
                
                if (nodoEncontrado && nodoEncontrado.lineas && Array.isArray(nodoEncontrado.lineas) && nodoEncontrado.lineas.length > 0) {
                    nodoEncontrado.lineas.forEach(function(linea) {
                        const option = document.createElement('option');
                        option.value = linea.id;
                        option.textContent = linea.nombre;
                        lineaSelect.appendChild(option);
                    });
                }
            }
        });

        // Inicializar con el rol seleccionado
        actualizarCamposPorRol();

        /* ================================================================
           VALIDACIÓN DINÁMICA DE CONTRASEÑA
        ================================================================ */
        const passwordInput = document.getElementById('password_crear');
        const password2Input = document.getElementById('password2_crear');
        const chkLength = document.getElementById('chk-length-crear');
        const chkUppercase = document.getElementById('chk-uppercase-crear');
        const chkSpecial = document.getElementById('chk-special-crear');
        const matchMessage = document.getElementById('matchMessageCrear');

        function validarContraseña() {
            const password = passwordInput.value;
            
            // Validar longitud
            const hasLength = password.length >= 8;
            actualizarCheck(chkLength, hasLength);
            
            // Validar mayúscula
            const hasUppercase = /[A-Z]/.test(password);
            actualizarCheck(chkUppercase, hasUppercase);
            
            // Validar carácter especial
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-]/.test(password);
            actualizarCheck(chkSpecial, hasSpecial);
            
            // Validar coincidencia de contraseñas
            validarCoincidencia();
        }

        function validarCoincidencia() {
            const password = passwordInput.value;
            const password2 = password2Input.value;
            
            if (password2 !== '') {
                if (password === password2) {
                    matchMessage.classList.add('d-none');
                    matchMessage.classList.remove('text-danger');
                } else {
                    matchMessage.classList.remove('d-none');
                    matchMessage.classList.add('text-danger');
                }
            }
        }

        function actualizarCheck(element, isValid) {
            if (isValid) {
                element.classList.remove('invalid');
                element.classList.add('valid');
                element.innerHTML = element.innerHTML.replace('✖', '✓');
                element.style.color = '#28a745';
            } else {
                element.classList.remove('valid');
                element.classList.add('invalid');
                element.innerHTML = element.innerHTML.replace('✓', '✖');
                element.style.color = '#dc3545';
            }
        }

        // Event listeners para validación dinámica
        passwordInput.addEventListener('input', validarContraseña);
        passwordInput.addEventListener('keyup', validarContraseña);
        password2Input.addEventListener('input', validarCoincidencia);
        password2Input.addEventListener('keyup', validarCoincidencia);

        // Toggle password visibility
        document.getElementById('togglePasswordCrear').addEventListener('click', function() {
            const input = document.getElementById('password_crear');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        });

        document.getElementById('togglePassword2Crear').addEventListener('click', function() {
            const input = document.getElementById('password2_crear');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        });

        // Preview de foto
        document.getElementById('foto_crear').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('preview_crear');
                    preview.src = event.target.result;
                    document.getElementById('previewContainerCrear').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
