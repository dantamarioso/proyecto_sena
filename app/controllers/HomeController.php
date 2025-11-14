<?php
class HomeController extends Controller {

    private function checkAuth() {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
        }
    }

    public function index() {
        $this->checkAuth();
        $usuario = $_SESSION['user'];
        $this->view('home/index', ['usuario' => $usuario]);
    }
}
