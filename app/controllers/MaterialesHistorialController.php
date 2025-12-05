<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../helpers/PermissionHelper.php';

/**
 * Controlador para gestión de historial de inventario.
 */
class MaterialesHistorialController extends Controller
{
    private $materialModel;

    public function __construct()
    {
        $this->materialModel = new Material();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            exit;
        }
    }

    /**
     * Ver historial de inventario.
     */
    public function index()
    {
        $this->requireAuth();

        try {
            $permissions = new PermissionHelper();
        } catch (Exception $e) {
            http_response_code(403);
            echo 'Error: ' . $e->getMessage();
            exit;
        }

        // Filtros
        $filtros = [];
        $filtros['tipo_movimiento'] = $_GET['tipo'] ?? '';
        $filtros['fecha_inicio'] = $_GET['fecha_inicio'] ?? '';
        $filtros['fecha_fin'] = $_GET['fecha_fin'] ?? '';
        $material_id = !empty($_GET['material_id']) ? intval($_GET['material_id']) : null;

        // Obtener historial de movimientos
        $historial = [];
        if ($filtros['tipo_movimiento'] !== 'eliminado' && $filtros['tipo_movimiento'] !== 'cambio') {
            $historial = $this->materialModel->getHistorialMovimientos($material_id, $filtros);
        }

        // Obtener eliminaciones de materiales
        $eliminaciones = [];
        if (empty($filtros['tipo_movimiento']) || $filtros['tipo_movimiento'] === 'eliminado') {
            $filtros['material_id'] = $material_id;
            $eliminaciones = $this->materialModel->getEliminacionesMateriales($filtros);
        }

        // Obtener cambios de auditoría (UPDATE de propiedades)
        $cambios = [];
        if (empty($filtros['tipo_movimiento']) || $filtros['tipo_movimiento'] === 'cambio') {
            $cambios = $this->materialModel->getHistorialCambios($material_id, $filtros);
        }

        // Combinar movimientos, eliminaciones y cambios, y ordenar por fecha
        $historialCompleto = $this->combinarHistorial($historial, $eliminaciones, $cambios);

        // Filtrar historial según permisos del usuario
        $historialCompleto = $this->filtrarPorPermisos($historialCompleto);

        // Ordenar por fecha descendente
        usort($historialCompleto, function ($a, $b) {
            $fechaA = strtotime($a['fecha_movimiento'] ?? $a['fecha_cambio'] ?? $a['fecha_creacion'] ?? 'now');
            $fechaB = strtotime($b['fecha_movimiento'] ?? $b['fecha_cambio'] ?? $b['fecha_creacion'] ?? 'now');
            return $fechaB - $fechaA;
        });

        // Obtener líneas y materiales accesibles
        $lineas = $permissions->getAccesibleLineas();
        $materiales = $this->getMaterialesAccesibles($permissions);

        $this->view('materiales/historial_inventario', [
            'historial' => $historialCompleto,
            'lineas' => $lineas,
            'materiales' => $materiales,
            'filtros' => $filtros,
            'permisos' => $permissions,
            'pageStyles' => ['materiales', 'usuarios'],
            'pageScripts' => ['materiales'],
        ]);
    }

    /**
     * Obtener detalles de un movimiento (AJAX).
     */
    public function detallesMovimiento()
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->requireAuth();

        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $movimiento = $this->materialModel->getMovimientoById($id);

        if (!$movimiento) {
            echo json_encode(['success' => false, 'message' => 'Movimiento no encontrado']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'movimiento' => $movimiento,
        ]);
        exit;
    }

    /**
     * Registrar movimiento de inventario.
     */
    public function registrarMovimiento()
    {
        header('Content-Type: application/json');
        $this->requireAuth();

        $rol = $_SESSION['user']['rol'] ?? 'usuario';

        if ($rol !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Solo administradores pueden registrar movimientos']);
            exit;
        }

        try {
            $material_id = $_POST['material_id'] ?? null;
            $tipo_movimiento = $_POST['tipo_movimiento'] ?? null;
            $cantidad = $_POST['cantidad'] ?? null;
            $motivo = $_POST['motivo'] ?? null;

            if (!$material_id || !$tipo_movimiento || !$cantidad) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                exit;
            }

            $usuario_id = $_SESSION['user']['id'];

            $success = $this->materialModel->registrarMovimiento([
                'material_id' => (int)$material_id,
                'tipo_movimiento' => $tipo_movimiento,
                'cantidad' => (int)$cantidad,
                'usuario_id' => $usuario_id,
                'motivo' => $motivo
            ]);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Movimiento registrado exitosamente']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al registrar movimiento']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Combinar historial de movimientos, eliminaciones y cambios.
     */
    private function combinarHistorial($historial, $eliminaciones, $cambios)
    {
        $historialCompleto = [];

        foreach ($historial as $mov) {
            $mov['tipo_registro'] = 'movimiento';
            $historialCompleto[] = $mov;
        }

        foreach ($eliminaciones as $elim) {
            $elim['tipo_registro'] = 'eliminacion';
            $historialCompleto[] = $elim;
        }

        foreach ($cambios as $cambio) {
            $cambio['tipo_registro'] = 'cambio';
            $historialCompleto[] = $cambio;
        }

        return $historialCompleto;
    }

    /**
     * Filtrar historial según permisos del usuario.
     */
    private function filtrarPorPermisos($historialCompleto)
    {
        $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
        $linea_user = $_SESSION['user']['linea_id'] ?? null;
        $rol = $_SESSION['user']['rol'];

        $historialFiltrado = [];

        foreach ($historialCompleto as $registro) {
            if ($rol === 'admin') {
                $historialFiltrado[] = $registro;
            } elseif ($rol === 'dinamizador') {
                if (($registro['nodo_id'] ?? null) == $nodo_user) {
                    $historialFiltrado[] = $registro;
                }
            } elseif ($rol === 'usuario') {
                if (($registro['nodo_id'] ?? null) == $nodo_user && ($registro['linea_id'] ?? null) == $linea_user) {
                    $historialFiltrado[] = $registro;
                }
            }
        }

        return $historialFiltrado;
    }

    /**
     * Obtener materiales accesibles según permisos.
     */
    private function getMaterialesAccesibles($permissions)
    {
        $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
        $linea_user = $_SESSION['user']['linea_id'] ?? null;
        $rol = $_SESSION['user']['rol'];

        $todosLosMateriales = $this->materialModel->all();
        $materiales = [];

        foreach ($todosLosMateriales as $mat) {
            if ($rol === 'admin') {
                $materiales[] = $mat;
            } elseif ($rol === 'dinamizador') {
                if ($mat['nodo_id'] == $nodo_user) {
                    $materiales[] = $mat;
                }
            } elseif ($rol === 'usuario') {
                if ($mat['nodo_id'] == $nodo_user && $mat['linea_id'] == $linea_user) {
                    $materiales[] = $mat;
                }
            }
        }

        return $materiales;
    }
}
