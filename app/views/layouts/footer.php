            </div><!-- cierre de main-content -->
            </div><!-- cierre de main-wrapper -->

            <!-- Notificación Toast Emergente -->
            <div class="notification-toast" id="notificationToast">
                <i class="bi bi-exclamation-circle"></i>
                <span id="toastMessage"></span>
            </div>

            <!-- JS de Bootstrap -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

            <?php $v = time(); // Cache buster para desarrollo
            ?>

            <!-- Utilidades JS -->
            <script src="/proyecto_sena/public/js/utilidades.js?v=<?= $v ?>"></script>

            <!-- Toggle password -->
            <script src="/proyecto_sena/public/js/password_toggle.js?v=<?= $v ?>"></script>

            <!-- JS global -->
            <script src="/proyecto_sena/public/js/app.js?v=<?= $v ?>"></script>

            <!-- Scripts por página -->
            <?php if (!empty($pageScripts) && is_array($pageScripts)) :
            ?>
                <?php foreach ($pageScripts as $js) :
                ?>
                    <script src="/proyecto_sena/public/js/<?= htmlspecialchars($js) ?>.js?v=<?= $v ?>"></script>
                <?php endforeach; ?>
            <?php
            endif; ?>

            <!-- Modal Helper para mobile -->
            <script src="/proyecto_sena/public/js/modal-helper.js?v=<?= $v ?>"></script>

            <!-- Image Editor Modal -->
            <script src="/proyecto_sena/public/js/image-editor.js?v=<?= $v ?>"></script>

            <!-- Sidebar -->
            <script src="/proyecto_sena/public/js/sidebar.js?v=<?= $v ?>"></script>

            </body>

            </html>