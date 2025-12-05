<?php

abstract class Controller
{
    protected function view($view, $data = [])
    {
        // extrae variables: $errores, $usuario, $pageStyles, $pageScripts, etc.
        extract($data);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    protected function redirect($route)
    {
        header('Location: ' . BASE_URL . '/?url=' . $route);
        exit;
    }

    /**
     * Redirige usando location.replace en el cliente para sustituir la entrada
     * actual del historial (evita que el botón Atrás vuelva a la página previa).
     */
    protected function redirectReplace($route)
    {
        $url = BASE_URL . '/?url=' . $route;
        // Enviar cabeceras para evitar caching
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<!doctype html><html><head><meta charset="utf-8"><title>Redirecting</title></head><body>';
        echo '<script>location.replace("' . $url . '");</script>';
        echo '</body></html>';
        exit;
    }
}
