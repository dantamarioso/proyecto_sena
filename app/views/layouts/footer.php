</div> <!-- /.container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS global -->
<script src="<?= BASE_URL ?>/js/app.js"></script>

<!-- JS por página -->
<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $js): ?>
        <script src="<?= BASE_URL ?>/js/<?= htmlspecialchars($js) ?>.js"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
