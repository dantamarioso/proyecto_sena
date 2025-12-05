<div class="login-page">

    <div class="login-container">

        <!-- Columna izquierda: tarjeta de login -->
        <div class="login-wrapper">
            <div class="card login-card">
                <div class="card-body">

                    <h3 class="text-center mb-4">Iniciar sesión</h3>

                    <!-- Flash messages se manejan con toast -->
                    <?php if (!empty($_SESSION['flash_success'])) :
                        ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                mostrarNotificacion('<?= addslashes($_SESSION['flash_success']) ?>', 'success', 5000);
                            });
                        </script>
                        <?php unset($_SESSION['flash_success']); ?>
                        <?php
                    endif; ?>
                    
                    <?php if (!empty($_SESSION['flash_error'])) :
                        ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                mostrarNotificacion('<?= addslashes($_SESSION['flash_error']) ?>', 'error', 5000);
                            });
                        </script>
                        <?php unset($_SESSION['flash_error']); ?>
                        <?php
                    endif; ?>

                    <!-- Errores de login -->
                    <?php if (!empty($errores)) :
                        ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errores as $e) :
                                    ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de login -->
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="login" class="form-control" 
                                   placeholder="ejemplo@correo.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="login-password"
                                    class="form-control" required>
                                <span class="input-group-text" id="toggleLoginPassword">
                                    <i class="bi bi-eye-fill"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button class="btn btn-login" type="submit">Ingresar</button>
                        </div>

                        <p class="text-center register-link">
                            ¿No tienes cuenta?
                            <a href="<?= BASE_URL ?>/auth/register">Regístrate aquí</a>
                        </p>

                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>/auth/forgot" class="text-decoration-none olvide">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>


                    </form>

                </div>
            </div>
        </div>

        <!-- Columna derecha: carrusel de texto (solo en pantallas grandes) -->
        <div class="login-carousel d-none d-lg-flex">
            <div id="loginTextCarousel" class="carousel slide w-100"
                data-bs-ride="carousel" data-bs-interval="4500">
                <div class="carousel-inner">

                    <div class="carousel-item active">
                        <h4 class="mb-2">Gestión de inventario eficiente</h4>
                        <p class="mb-0">
                            Controla tus productos, entradas y salidas en tiempo real
                            para evitar pérdidas y mejorar la trazabilidad.
                        </p>
                    </div>

                    <div class="carousel-item">
                        <h4 class="mb-2">Usuarios y roles</h4>
                        <p class="mb-0">
                            Define perfiles para administradores, auxiliares y otros cargos,
                            con permisos personalizados para cada uno.
                        </p>
                    </div>

                    <div class="carousel-item">
                        <h4 class="mb-2">Reportes claros</h4>
                        <p class="mb-0">
                            Genera reportes de stock, movimientos y alertas de reposición
                            para tomar decisiones rápidas y acertadas.
                        </p>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
