            </div><!-- cierre de main-content -->
    </div><!-- cierre de main-wrapper -->

    <!-- Notificación Toast Emergente -->
    <div class="notification-toast" id="notificationToast">
        <i class="bi bi-exclamation-circle"></i>
        <span id="toastMessage"></span>
    </div>

    <!-- JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Utilidades JS -->
    <script src="/proyecto_sena/public/js/utilidades.js"></script>

    <!-- Toggle password -->
    <script src="/proyecto_sena/public/js/password_toggle.js"></script>

    <!-- JS global -->
    <script src="/proyecto_sena/public/js/app.js"></script>

    <!-- Scripts por página -->
    <?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
        <?php foreach ($pageScripts as $js): ?>
            <script src="/proyecto_sena/public/js/<?= htmlspecialchars($js) ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Sidebar -->
    <script src="/proyecto_sena/public/js/sidebar.js"></script>

</body>
</html>
