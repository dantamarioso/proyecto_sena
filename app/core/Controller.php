<?php
abstract class Controller {
    protected function view($view, $data = []) {
        // extrae variables: $errores, $usuario, $pageStyles, $pageScripts, etc.
        extract($data);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    protected function redirect($route) {
        header('Location: ' . BASE_URL . '/?url=' . $route);
        exit;
    }
}
