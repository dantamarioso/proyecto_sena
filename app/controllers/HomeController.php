<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class HomeController extends Controller
{
    private function checkAuth()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $this->checkAuth();
        $usuario = $_SESSION['user'];

        // Obtener datos completos del usuario con línea y nodo
        $userModel = new User();
        $usuarioCompleto = $userModel->findById($usuario['id']);

        // Obtener nombre de línea y nodo
        $lineaNombre = null;
        $nodoNombre = null;

        if (!empty($usuarioCompleto['linea_id'])) {
            $lineaModel = new Linea();
            $linea = $lineaModel->getById($usuarioCompleto['linea_id']);
            $lineaNombre = $linea['nombre'] ?? null;
        }

        if (!empty($usuarioCompleto['nodo_id'])) {
            $nodoModel = new Nodo();
            $nodo = $nodoModel->getById($usuarioCompleto['nodo_id']);
            $nodoNombre = $nodo['nombre'] ?? null;
        }

        $this->view('home/index', [
            'usuario' => $usuario,
            'lineaNombre' => $lineaNombre,
            'nodoNombre' => $nodoNombre,
            'pageStyles' => ['home'],
            'pageScripts' => [],
        ]);
    }
}
