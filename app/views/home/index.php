<?php
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/auth/login");
    exit;
}
?>

