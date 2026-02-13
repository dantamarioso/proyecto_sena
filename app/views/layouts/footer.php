            </div><!-- cierre de main-content -->
            </div><!-- cierre de main-wrapper -->

            <!-- Notificación Toast Emergente -->
            <div class="notification-toast" id="notificationToast">
                <i class="bi bi-exclamation-circle"></i>
                <span id="toastMessage"></span>
            </div>

            <?php
            // Versionado por mtime: permite cache y se actualiza al editar archivos
            if (!function_exists('asset_version')) {
                function asset_version($relativePath)
                {
                    static $cache = [];
                    $relativePath = ltrim((string)$relativePath, '/');
                    if (isset($cache[$relativePath])) {
                        return $cache[$relativePath];
                    }

                    $fullPath = __DIR__ . '/../../../public/' . $relativePath;
                    $v = file_exists($fullPath) ? (int)filemtime($fullPath) : time();
                    $cache[$relativePath] = $v;
                    return $v;
                }
            }
            ?>

            <!-- jQuery (requerido por DataTables) - Local -->
            <script src="/proyecto_sena/public/vendor/jquery/jquery-3.7.1.min.js?v=<?= asset_version('vendor/jquery/jquery-3.7.1.min.js') ?>"></script>

            <!-- DataTables (Bootstrap 5) - Local -->
            <script src="/proyecto_sena/public/vendor/datatables/jquery.dataTables.min.js?v=<?= asset_version('vendor/datatables/jquery.dataTables.min.js') ?>"></script>
            <script src="/proyecto_sena/public/vendor/datatables/dataTables.bootstrap5.min.js?v=<?= asset_version('vendor/datatables/dataTables.bootstrap5.min.js') ?>"></script>

            <!-- Inicialización DataTables (tablas con .js-datatable) -->
            <script src="/proyecto_sena/public/js/datatables-init.js?v=<?= asset_version('js/datatables-init.js') ?>"></script>

            <!-- JS de Bootstrap -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

            <!-- Utilidades JS -->
            <script src="/proyecto_sena/public/js/utilidades.js?v=<?= asset_version('js/utilidades.js') ?>"></script>

            <!-- Toggle password -->
            <script src="/proyecto_sena/public/js/password_toggle.js?v=<?= asset_version('js/password_toggle.js') ?>"></script>

            <!-- JS global -->
            <script src="/proyecto_sena/public/js/app.js?v=<?= asset_version('js/app.js') ?>"></script>

            <!-- Scripts por página -->
            <?php if (!empty($pageScripts) && is_array($pageScripts)) :
            ?>
                <?php foreach ($pageScripts as $js) :
                ?>
                    <?php
                    $jsName = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$js);
                    ?>
                    <script src="/proyecto_sena/public/js/<?= htmlspecialchars($jsName) ?>.js?v=<?= asset_version('js/' . $jsName . '.js') ?>"></script>
                <?php endforeach; ?>
            <?php
            endif; ?>

            <!-- Modal Helper para mobile -->
            <script src="/proyecto_sena/public/js/modal-helper.js?v=<?= asset_version('js/modal-helper.js') ?>"></script>

            <!-- Image Editor Modal -->
            <script src="/proyecto_sena/public/js/image-editor.js?v=<?= asset_version('js/image-editor.js') ?>"></script>

            <!-- Sidebar -->
            <script src="/proyecto_sena/public/js/sidebar.js?v=<?= asset_version('js/sidebar.js') ?>"></script>

            </body>

            </html>
