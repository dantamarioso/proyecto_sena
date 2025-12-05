<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../models/Audit.php';
require_once __DIR__ . '/../models/MaterialArchivo.php';
require_once __DIR__ . '/../helpers/PermissionHelper.php';

/**
 * Controlador principal de Materiales - Solo CRUD.
 *
 * Funcionalidad movida a controladores especializados:
 * - Importación → MaterialesImportController
 * - Exportación → MaterialesExportController
 * - Archivos → MaterialesArchivosController
 * - Historial → MaterialesHistorialController
 */
class MaterialesController extends Controller
{
    /* ========================================================= HELPERS / VALIDACIÓN
    ========================================================== */

    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            exit;
        }
    }

    private function requirePermission()
    {
        $this->requireAuth();
        $rol = $_SESSION['user']['rol'] ?? 'invitado';

        if (!in_array($rol, ['admin', 'dinamizador', 'usuario'])) {
            http_response_code(403);
            echo 'Acceso denegado.';
            exit;
        }
    }

    private function requireEditPermission($material_id)
    {
        $this->requireAuth();

        try {
            $permissions = new PermissionHelper();

            if (!$permissions->canEditMaterial($material_id)) {
                http_response_code(403);
                echo 'No tiene permiso para editar este material.';
                exit;
            }
        } catch (Exception $e) {
            http_response_code(403);
            echo 'Error al verificar permisos: ' . $e->getMessage();
            exit;
        }
    }

    private function requireDeletePermission($material_id)
    {
        $this->requireAuth();

        try {
            $permissions = new PermissionHelper();

            if (!$permissions->canDeleteMaterial($material_id)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }

    private function validarMaterial($data, $exceptoId = null)
    {
        $errores = [];

        if (empty($data['codigo'])) {
            $errores[] = 'El código del producto es obligatorio.';
        } elseif (strlen($data['codigo']) > 50) {
            $errores[] = 'El código no debe exceder 50 caracteres.';
        }

        if (empty($data['nombre'])) {
            $errores[] = 'El nombre del material es obligatorio.';
        } elseif (strlen($data['nombre']) > 100) {
            $errores[] = 'El nombre no debe exceder 100 caracteres.';
        }

        if (empty($data['linea_id'])) {
            $errores[] = 'Debe seleccionar una línea.';
        }

        if (!isset($data['cantidad']) || $data['cantidad'] === '') {
            $errores[] = 'La cantidad es obligatoria.';
        } elseif (!is_numeric($data['cantidad'])) {
            $errores[] = 'La cantidad debe ser un número entero.';
        } elseif (intval($data['cantidad']) < 0) {
            $errores[] = 'La cantidad no puede ser negativa.';
        }

        $materialModel = new Material();
        if ($materialModel->codigoExiste($data['codigo'], $exceptoId)) {
            $errores[] = 'El código del producto ya existe en el sistema.';
        }

        return $errores;
    }

    private function registrarAuditoria($accion, $tabla, $detalles, $materialId)
    {
        try {
            $audit = new Audit();
            $audit->registrarCambio(
                $_SESSION['user']['id'],
                $tabla,
                $materialId,
                $accion,
                $detalles,
                $_SESSION['user']['id']
            );
        } catch (Exception $e) {
            // Silencioso
        }
    }

    private function getNodoNombre($nodo_id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT nombre FROM nodos WHERE id = :id');
        $stmt->execute([':id' => $nodo_id]);
        $nodo = $stmt->fetch(PDO::FETCH_ASSOC);
        return $nodo['nombre'] ?? '';
    }

    private function getLineaNombre($linea_id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT nombre FROM lineas WHERE id = :id');
        $stmt->execute([':id' => $linea_id]);
        $linea = $stmt->fetch(PDO::FETCH_ASSOC);
        return $linea['nombre'] ?? '';
    }

    /* =========================================================
       LISTAR / INDEX - Panel de materiales
    ========================================================== */

    public function index()
    {
        $this->requirePermission();

        $materialModel = new Material();

        try {
            $permissions = new PermissionHelper();
        } catch (Exception $e) {
            http_response_code(403);
            echo 'Error: ' . $e->getMessage();
            exit;
        }

        $busqueda = $_GET['busqueda'] ?? '';
        $linea_id = !empty($_GET['linea_id']) ? intval($_GET['linea_id']) : null;
        $estado = isset($_GET['estado']) && $_GET['estado'] !== '' ? intval($_GET['estado']) : null;

        if ($busqueda || $linea_id || $estado !== null) {
            $materiales = $materialModel->search($busqueda, $linea_id, $estado);
        } else {
            $materiales = $materialModel->all();
        }

        $where_clause = $permissions->getMaterialesWhereClause('m');
        if ($where_clause !== '1=1') {
            $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
            $linea_user = $_SESSION['user']['linea_id'] ?? null;
            $rol = $_SESSION['user']['rol'];

            $materiales_filtrados = [];
            foreach ($materiales as $mat) {
                if ($rol === 'admin') {
                    $materiales_filtrados[] = $mat;
                } elseif ($rol === 'dinamizador') {
                    if ($mat['nodo_id'] == $nodo_user) {
                        $materiales_filtrados[] = $mat;
                    }
                } elseif ($rol === 'usuario') {
                    if ($mat['nodo_id'] == $nodo_user && $mat['linea_id'] == $linea_user) {
                        $materiales_filtrados[] = $mat;
                    }
                }
            }
            $materiales = $materiales_filtrados;
        }

        $lineas = $permissions->getAccesibleLineas();

        $estadoLineas = [];
        if ($permissions->isAdmin()) {
            $estadoLineas = $materialModel->contarPorLinea();
        } else {
            foreach ($lineas as $linea) {
                $count = 0;
                foreach ($materiales as $mat) {
                    if ($mat['linea_id'] == $linea['id']) {
                        $count++;
                    }
                }
                $estadoLineas[] = array_merge($linea, ['total' => $count]);
            }
        }

        $this->view('materiales/index', [
            'materiales' => $materiales,
            'lineas' => $lineas,
            'estadoLineas' => $estadoLineas,
            'busqueda' => $busqueda,
            'linea_id' => $linea_id,
            'estado' => $estado,
            'permisos' => $permissions,
            'pageStyles' => ['materiales', 'usuarios'],
            'pageScripts' => ['materiales'],
        ]);
    }

    /* =========================================================
       CREAR MATERIAL
    ========================================================== */

    public function crear()
    {
        $this->requirePermission();

        try {
            $permissions = new PermissionHelper();
            if (!$permissions->canCreateMaterial()) {
                http_response_code(403);
                echo 'No tiene permiso para crear materiales.';
                exit;
            }
        } catch (Exception $e) {
            http_response_code(403);
            echo 'Error: ' . $e->getMessage();
            exit;
        }

        $materialModel = new Material();
        $lineas = $permissions->getAccesibleLineas();
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=utf-8');

            $data = [
                'codigo' => trim($_POST['codigo'] ?? ''),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'linea_id' => intval($_POST['linea_id'] ?? 0),
                'nodo_id' => null,
                'fecha_adquisicion' => trim($_POST['fecha_adquisicion'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'presentacion' => trim($_POST['presentacion'] ?? ''),
                'MEDIDA' => trim($_POST['MEDIDA'] ?? ''),
                'cantidad' => intval($_POST['cantidad'] ?? 0),
                'valor_compra' => trim($_POST['valor_compra'] ?? ''),
                'proveedor' => trim($_POST['proveedor'] ?? ''),
                'marca' => trim($_POST['marca'] ?? ''),
                'estado' => intval($_POST['estado'] ?? 1),
            ];

            $rol = $_SESSION['user']['rol'] ?? 'usuario';

            if ($rol === 'admin' && !empty($_POST['nodo_id'])) {
                $data['nodo_id'] = intval($_POST['nodo_id']);
            } else {
                $data['nodo_id'] = $_SESSION['user']['nodo_id'] ?? null;
            }

            if (empty($data['nodo_id'])) {
                echo json_encode(['success' => false, 'errors' => ['No se pudo determinar el nodo del usuario']]);
                exit;
            }

            $errores = $this->validarMaterial($data);

            if (empty($errores)) {
                $materialId = $materialModel->create($data);

                if ($materialId) {
                    if ($data['cantidad'] > 0) {
                        $movimientoData = [
                            'material_id' => $materialId,
                            'usuario_id' => $_SESSION['user']['id'],
                            'tipo_movimiento' => 'entrada',
                            'cantidad' => $data['cantidad'],
                            'descripcion' => 'Cantidad inicial al crear material',
                        ];
                        $materialModel->registrarMovimiento($movimientoData);
                    }

                    $this->registrarAuditoria('crear', 'materiales', $data, $materialId);
                    echo json_encode(['success' => true, 'message' => 'Material creado exitosamente', 'id' => $materialId]);
                    exit;
                } else {
                    $errores[] = 'Error al crear el material. Intenta de nuevo.';
                }
            }

            echo json_encode(['success' => false, 'errors' => $errores]);
            exit;
        }

        $this->view('materiales/crear', [
            'lineas' => $lineas,
            'errores' => $errores,
            'permisos' => $permissions,
            'pageStyles' => ['materiales', 'usuarios', 'materiales_form'],
            'pageScripts' => ['materiales'],
        ]);
    }

    /* =========================================================
       EDITAR MATERIAL
    ========================================================== */

    public function editar()
    {
        $this->requirePermission();

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(404);
            echo 'Material no encontrado.';
            exit;
        }

        $this->requireEditPermission($id);

        $materialModel = new Material();
        $material = $materialModel->getById($id);

        if (!$material) {
            http_response_code(404);
            echo 'Material no encontrado.';
            exit;
        }

        try {
            $permissions = new PermissionHelper();
        } catch (Exception $e) {
            http_response_code(403);
            echo 'Error: ' . $e->getMessage();
            exit;
        }

        $lineas = $permissions->getAccesibleLineas();
        $errores = [];

        $archivoModel = new MaterialArchivo();
        $archivos = $archivoModel->getByMaterial($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=utf-8');

            $data = [
                'codigo' => trim($_POST['codigo'] ?? ''),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'linea_id' => intval($_POST['linea_id'] ?? 0),
                'nodo_id' => null,
                'fecha_adquisicion' => trim($_POST['fecha_adquisicion'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'presentacion' => trim($_POST['presentacion'] ?? ''),
                'MEDIDA' => trim($_POST['MEDIDA'] ?? ''),
                'cantidad' => intval($_POST['cantidad'] ?? 0),
                'valor_compra' => trim($_POST['valor_compra'] ?? ''),
                'proveedor' => trim($_POST['proveedor'] ?? ''),
                'marca' => trim($_POST['marca'] ?? ''),
                'estado' => intval($_POST['estado'] ?? 1),
            ];

            $rol = $_SESSION['user']['rol'] ?? 'usuario';

            if ($rol === 'admin' && !empty($_POST['nodo_id'])) {
                $data['nodo_id'] = intval($_POST['nodo_id']);
            } else {
                $data['nodo_id'] = $material['nodo_id'];
            }

            $errores = $this->validarMaterial($data, $id);

            if (empty($errores)) {
                if ($materialModel->update($id, $data)) {
                    $cambios = [];
                    foreach (['codigo', 'nombre', 'descripcion', 'nodo_id', 'linea_id', 'fecha_adquisicion', 'categoria', 'presentacion', 'medida', 'cantidad', 'valor_compra', 'proveedor', 'marca', 'estado'] as $campo) {
                        $valorAnterior = $material[$campo] ?? null;
                        $valorNuevo = $data[$campo] ?? null;

                        if ($campo === 'nodo_id' || $campo === 'linea_id') {
                            if (intval($valorAnterior) !== intval($valorNuevo)) {
                                $cambios[$campo] = ['antes' => $valorAnterior, 'despues' => $valorNuevo];
                            }
                        } else {
                            if ($valorAnterior !== $valorNuevo) {
                                $cambios[$campo] = ['antes' => $valorAnterior, 'despues' => $valorNuevo];
                            }
                        }
                    }

                    $detallesAuditoria = !empty($cambios) ? $cambios : ['nota' => 'Sin cambios detectados'];
                    $this->registrarAuditoria('actualizar', 'materiales', $detallesAuditoria, $id);

                    echo json_encode(['success' => true, 'message' => 'Material actualizado exitosamente']);
                    exit;
                } else {
                    $errores[] = 'Error al actualizar el material. Intenta de nuevo.';
                }
            }

            echo json_encode(['success' => false, 'errors' => $errores]);
            exit;
        }

        $this->view('materiales/editar', [
            'material' => $material,
            'lineas' => $lineas,
            'archivos' => $archivos,
            'errores' => $errores,
            'permisos' => $permissions,
            'pageStyles' => ['materiales', 'usuarios', 'materiales_form'],
            'pageScripts' => ['materiales'],
        ]);
    }

    /* =========================================================
       ELIMINAR MATERIAL
    ========================================================== */

    public function eliminar()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $this->requireDeletePermission($id);

        $materialModel = new Material();
        $material = $materialModel->getById($id);

        if (!$material) {
            echo json_encode(['success' => false, 'message' => 'Material no encontrado']);
            exit;
        }

        try {
            $audit = new Audit();
            $audit->registrarCambio(
                $_SESSION['user']['id'],
                'materiales',
                $id,
                'eliminar',
                [
                    'id' => $material['id'],
                    'nombre' => $material['nombre'],
                    'codigo' => $material['codigo'],
                    'cantidad' => $material['cantidad'],
                    'nodo_id' => $material['nodo_id'],
                    'nodo_nombre' => $material['nodo_nombre'] ?? $this->getNodoNombre($material['nodo_id']),
                    'linea_id' => $material['linea_id'],
                    'linea_nombre' => $this->getLineaNombre($material['linea_id']),
                    'descripcion' => $material['descripcion'],
                    'usuario_nombre' => $_SESSION['user']['nombre'] ?? 'Sistema',
                    'usuario_id' => $_SESSION['user']['id'],
                ],
                $_SESSION['user']['id']
            );
        } catch (Exception $e) {
            // Silencioso
        }

        if ($materialModel->delete($id, $_SESSION['user']['id'] ?? 1)) {
            echo json_encode(['success' => true, 'message' => 'Material eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el material']);
        }
        exit;
    }

    /* =========================================================
       OBTENER DETALLES (AJAX)
    ========================================================== */

    public function obtenerDetalles()
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->requireAuth();

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $materialModel = new Material();
        $material = $materialModel->getById($id);

        if (!$material) {
            echo json_encode(['success' => false, 'message' => 'Material no encontrado']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'material' => $material,
        ]);
        exit;
    }

    /* =========================================================
       VER DETALLES DE MATERIAL
    ========================================================== */

    public function detalles()
    {
        $this->requireAuth();

        $materialId = intval($_GET['id'] ?? 0);

        if ($materialId <= 0) {
            http_response_code(404);
            echo 'Material no encontrado.';
            exit;
        }

        $materialModel = new Material();
        $material = $materialModel->getById($materialId);

        if (!$material) {
            http_response_code(404);
            echo 'Material no encontrado.';
            exit;
        }

        $archivoModel = new MaterialArchivo();
        $archivos = $archivoModel->getByMaterial($materialId);

        $this->view('materiales/detalles', [
            'material' => $material,
            'archivos' => $archivos,
            'pageStyles' => ['materiales'],
            'pageScripts' => [],
        ]);
    }

    /* =========================================================
       OBTENER LÍNEAS POR NODO (AJAX)
    ========================================================== */

    public function obtenerLineasPorNodo()
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->requireAuth();

        $nodo_id = intval($_GET['nodo_id'] ?? 0);

        if ($nodo_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Nodo inválido']);
            exit;
        }

        try {
            $rol = $_SESSION['user']['rol'] ?? 'usuario';

            if ($rol === 'admin') {
                $db = Database::getInstance();
                $stmt = $db->prepare('
                    SELECT DISTINCT l.id, l.nombre 
                    FROM lineas l
                    WHERE l.estado = 1 
                    ORDER BY l.nombre ASC
                ');
                $stmt->execute();
                $lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'lineas' => $lineas]);
                exit;
            }

            $db = Database::getInstance();
            $stmt = $db->prepare('
                SELECT DISTINCT l.id, l.nombre 
                FROM lineas l
                INNER JOIN linea_nodo ln ON ln.linea_id = l.id 
                WHERE ln.nodo_id = :nodo_id 
                AND ln.estado = 1 
                AND l.estado = 1 
                ORDER BY l.nombre ASC
            ');
            $stmt->execute([':nodo_id' => $nodo_id]);
            $lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'lineas' => $lineas]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}
