            </div><!-- cierre de main-content -->
    </div><!-- cierre de main-wrapper -->

    <!-- JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toggle password -->
    <script src="<?= BASE_URL ?>/js/password_toggle.js"></script>

    <!-- JS global -->
    <script src="<?= BASE_URL ?>/js/app.js"></script>

    <!-- Scripts por página -->
    <?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
        <?php foreach ($pageScripts as $js): ?>
            <script src="<?= BASE_URL ?>/js/<?= htmlspecialchars($js) ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Sidebar -->
    <script src="<?= BASE_URL ?>/js/sidebar.js"></script>

</body>
</html>
