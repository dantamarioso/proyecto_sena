<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../models/User.php';

/**
 * AlertasController
 * - Devuelve notificaciones del sistema para el sidebar.
 * - Incluye materiales vencidos (segun permisos del usuario).
 * - Si el usuario es admin, incluye tambien usuarios pendientes.
 */
class AlertasController extends Controller
{
    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }
    }

    public function get()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $this->requireAuth();

        $rol = $_SESSION['user']['rol'] ?? 'usuario';
        $userNodoId = isset($_SESSION['user']['nodo_id']) ? (int)$_SESSION['user']['nodo_id'] : null;
        $userLineaId = isset($_SESSION['user']['linea_id']) ? (int)$_SESSION['user']['linea_id'] : null;

        // Filtro de materiales segun rol
        $nodoFilter = null;
        $lineaFilter = null;

        if (in_array($rol, ['dinamizador', 'usuario'], true)) {
            if (empty($userNodoId)) {
                echo json_encode([
                    'success' => true,
                    'count_total' => 0,
                    'count_usuarios_pendientes' => 0,
                    'count_materiales_vencidos' => 0,
                    'usuarios_pendientes' => [],
                    'materiales_vencidos' => [],
                ]);
                exit;
            }
            $nodoFilter = $userNodoId;
        }
        if ($rol === 'usuario') {
            if (empty($userLineaId)) {
                echo json_encode([
                    'success' => true,
                    'count_total' => 0,
                    'count_usuarios_pendientes' => 0,
                    'count_materiales_vencidos' => 0,
                    'usuarios_pendientes' => [],
                    'materiales_vencidos' => [],
                ]);
                exit;
            }
            $lineaFilter = $userLineaId;
        }

        $materialModel = new Material();
        $countMaterialesVencidos = $materialModel->countVencidos($nodoFilter, $lineaFilter);
        $materialesVencidos = $materialModel->getVencidos($nodoFilter, $lineaFilter, 20);

        // Usuarios pendientes solo para admin
        $countUsuariosPendientes = 0;
        $usuariosPendientes = [];
        if ($rol === 'admin') {
            $userModel = new User();
            $usuariosPendientes = $userModel->getPendingUsers();
            $countUsuariosPendientes = $userModel->countPendingUsers();
        }

        $total = $countUsuariosPendientes + $countMaterialesVencidos;

        echo json_encode([
            'success' => true,
            'count_total' => $total,
            'count_usuarios_pendientes' => $countUsuariosPendientes,
            'count_materiales_vencidos' => $countMaterialesVencidos,
            'usuarios_pendientes' => $usuariosPendientes,
            'materiales_vencidos' => $materialesVencidos,
        ]);
        exit;
    }
}
