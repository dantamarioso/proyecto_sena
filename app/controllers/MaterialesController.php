<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../models/MaterialArchivo.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Nodo.php';
require_once __DIR__ . '/../models/Linea.php';
require_once __DIR__ . '/../helpers/PermissionHelper.php';

class MaterialesController extends Controller
{
    /* =========================================================
       HELPERS / VALIDACIÓN
    ========================================================== */

    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            exit;
        }
    }

    /**
     * Verificar permisos - Solo admin, dinamizador y usuario pueden ver
     */
    private function requirePermission()
    {
        $this->requireAuth();
        $rol = $_SESSION['user']['rol'] ?? 'invitado';
        
        if (!in_array($rol, ['admin', 'dinamizador', 'usuario'])) {
            http_response_code(403);
            echo "Acceso denegado.";
            exit;
        }
    }

    /**
     * Verificar si puede editar/eliminar materiales
     */
    private function requireEditPermission($material_id)
    {
        $this->requireAuth();
        
        try {
            $permissions = new PermissionHelper();
            
            if (!$permissions->canEditMaterial($material_id)) {
                http_response_code(403);
                echo "No tiene permiso para editar este material.";
                exit;
            }
        } catch (Exception $e) {
            http_response_code(403);
            echo "Error al verificar permisos: " . $e->getMessage();
            exit;
        }
    }

    /**
     * Verificar si puede eliminar materiales
     */
    private function requireDeletePermission($material_id)
    {
        $this->requireAuth();
        
        try {
            $permissions = new PermissionHelper();
            
            if (!$permissions->canDeleteMaterial($material_id)) {
                http_response_code(403);
                echo "No tiene permiso para eliminar este material.";
                exit;
            }
        } catch (Exception $e) {
            http_response_code(403);
            echo "Error al verificar permisos: " . $e->getMessage();
            exit;
        }
    }

    private function requireAdmin()
    {
        $this->requireAuth();
        if (($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
            http_response_code(403);
            echo "Acceso denegado. Solo administradores pueden hacer cambios en inventario.";
            exit;
        }
    }

    private function validarMaterial($data, $exceptoId = null)
    {
        $errores = [];

        // Validación de campos obligatorios
        if (empty($data['codigo'])) {
            $errores[] = "El código del producto es obligatorio.";
        } else if (strlen($data['codigo']) > 50) {
            $errores[] = "El código no debe exceder 50 caracteres.";
        }

        if (empty($data['nombre'])) {
            $errores[] = "El nombre del material es obligatorio.";
        } else if (strlen($data['nombre']) > 100) {
            $errores[] = "El nombre no debe exceder 100 caracteres.";
        }

        if (empty($data['linea_id'])) {
            $errores[] = "Debe seleccionar una línea.";
        }

        if (!isset($data['cantidad']) || $data['cantidad'] === '') {
            $errores[] = "La cantidad es obligatoria.";
        } else if (!is_numeric($data['cantidad'])) {
            $errores[] = "La cantidad debe ser un número entero.";
        } else if (intval($data['cantidad']) < 0) {
            $errores[] = "La cantidad no puede ser negativa.";
        }

        // Validar código único
        $materialModel = new Material();
        if ($materialModel->codigoExiste($data['codigo'], $exceptoId)) {
            $errores[] = "El código del producto ya existe en el sistema.";
        }

        return $errores;
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
            echo "Error: " . $e->getMessage();
            exit;
        }

        // Obtener parámetros de búsqueda y filtros
        $busqueda = $_GET['busqueda'] ?? '';
        $linea_id = !empty($_GET['linea_id']) ? intval($_GET['linea_id']) : null;
        $estado = isset($_GET['estado']) && $_GET['estado'] !== '' ? intval($_GET['estado']) : null;

        // Buscar materiales (filtrado por permisos)
        if ($busqueda || $linea_id || $estado !== null) {
            $materiales = $materialModel->search($busqueda, $linea_id, $estado);
        } else {
            $materiales = $materialModel->all();
        }
        
        // Filtrar materiales según permisos del usuario
        $where_clause = $permissions->getMaterialesWhereClause('m');
        if ($where_clause !== '1=1') {
            // Aplicar filtro de permisos
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

        // Obtener líneas accesibles
        $lineas = $permissions->getAccesibleLineas();
        
        // Contar por línea (solo accesibles)
        $estadoLineas = [];
        if ($permissions->isAdmin()) {
            $estadoLineas = $materialModel->contarPorLinea();
        } else {
            // Contar solo materiales del nodo del usuario
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
            'materiales'      => $materiales,
            'lineas'          => $lineas,
            'estadoLineas'    => $estadoLineas,
            'busqueda'        => $busqueda,
            'linea_id'        => $linea_id,
            'estado'          => $estado,
            'permisos'        => $permissions,
            'pageStyles'      => ['materiales', 'usuarios'],
            'pageScripts'     => ['materiales'],
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
                echo "No tiene permiso para crear materiales.";
                exit;
            }
        } catch (Exception $e) {
            http_response_code(403);
            echo "Error: " . $e->getMessage();
            exit;
        }

        $materialModel = new Material();
        
        // Obtener solo las líneas accesibles
        $lineas = $permissions->getAccesibleLineas();
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=utf-8');
            $data = [
                'codigo'        => trim($_POST['codigo'] ?? ''),
                'nombre'        => trim($_POST['nombre'] ?? ''),
                'descripcion'   => trim($_POST['descripcion'] ?? ''),
                'linea_id'      => intval($_POST['linea_id'] ?? 0),
                'cantidad'      => intval($_POST['cantidad'] ?? 0),
                'estado'        => intval($_POST['estado'] ?? 1),
                'nodo_id'       => null, // Se establece a continuación
            ];

            // Determinar nodo_id según el rol
            $rol = $_SESSION['user']['rol'] ?? 'usuario';
            
            if ($rol === 'admin' && !empty($_POST['nodo_id'])) {
                // Admin puede especificar el nodo
                $data['nodo_id'] = intval($_POST['nodo_id']);
            } else {
                // Usuario/Dinamizador: obtener de la sesión
                $data['nodo_id'] = $_SESSION['user']['nodo_id'] ?? null;
            }
            
            if (empty($data['nodo_id'])) {
                echo json_encode(['success' => false, 'errors' => ['No se pudo determinar el nodo del usuario']]);
                exit;
            }

            // Validar que la línea sea accesible y pertenezca al nodo
            $linea_ok = false;
            foreach ($lineas as $linea) {
                if ($linea['id'] == $data['linea_id'] && $linea['nodo_id'] == $data['nodo_id']) {
                    $linea_ok = true;
                    break;
                }
            }
            
            // Si no encontró la línea en el array accesible, obtener de la BD usando linea_nodo
            if (!$linea_ok && !empty($data['linea_id'])) {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT DISTINCT 1 
                    FROM linea_nodo ln
                    WHERE ln.linea_id = :linea_id 
                    AND ln.nodo_id = :nodo_id 
                    AND ln.estado = 1 
                    LIMIT 1
                ");
                $stmt->execute([
                    ':linea_id' => $data['linea_id'],
                    ':nodo_id' => $data['nodo_id']
                ]);
                
                if ($stmt->fetch()) {
                    $linea_ok = true;
                }
            }
            
            if (!$linea_ok) {
                echo json_encode(['success' => false, 'errors' => ['Línea no accesible o no pertenece a tu nodo']]);
                exit;
            }

            $errores = $this->validarMaterial($data);

            if (empty($errores)) {
                $materialId = $materialModel->create($data);
                
                if ($materialId) {
                    // Si la cantidad inicial es mayor a 0, registrar como entrada
                    if ($data['cantidad'] > 0) {
                        $movimientoData = [
                            'material_id'      => $materialId,
                            'usuario_id'       => $_SESSION['user']['id'],
                            'tipo_movimiento'  => 'entrada',
                            'cantidad'         => $data['cantidad'],
                            'descripcion'      => 'Cantidad inicial al crear material',
                        ];
                        $materialModel->registrarMovimiento($movimientoData);
                    }

                    // Registrar en auditoría
                    $this->registrarAuditoria('CREATE', 'materiales', $data['nombre'], $data);
                    echo json_encode(['success' => true, 'message' => 'Material creado exitosamente', 'id' => $materialId]);
                    exit;
                } else {
                    $errores[] = "Error al crear el material. Intenta de nuevo.";
                }
            }

            echo json_encode(['success' => false, 'errors' => $errores]);
            exit;
        }

        $this->view('materiales/crear', [
            'lineas'      => $lineas,
            'errores'     => $errores,
            'permisos'    => $permissions,
            'pageStyles'  => ['materiales', 'usuarios', 'materiales_form'],
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
            echo "Material no encontrado.";
            exit;
        }

        $this->requireEditPermission($id);

        $materialModel = new Material();
        $material = $materialModel->getById($id);

        if (!$material) {
            http_response_code(404);
            echo "Material no encontrado.";
            exit;
        }

        try {
            $permissions = new PermissionHelper();
        } catch (Exception $e) {
            http_response_code(403);
            echo "Error: " . $e->getMessage();
            exit;
        }

        $lineas = $permissions->getAccesibleLineas();
        $errores = [];

        // Obtener archivos del material
        $archivoModel = new MaterialArchivo();
        $archivos = $archivoModel->getByMaterial($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json; charset=utf-8');
            $data = [
                'codigo'        => trim($_POST['codigo'] ?? ''),
                'nombre'        => trim($_POST['nombre'] ?? ''),
                'descripcion'   => trim($_POST['descripcion'] ?? ''),
                'linea_id'      => intval($_POST['linea_id'] ?? 0),
                'cantidad'      => intval($_POST['cantidad'] ?? 0),
                'estado'        => intval($_POST['estado'] ?? 1),
            ];

            // Validar que la línea sea accesible
            $linea_ok = false;
            foreach ($lineas as $linea) {
                if ($linea['id'] == $data['linea_id']) {
                    $linea_ok = true;
                    break;
                }
            }
            
            if (!$linea_ok) {
                echo json_encode(['success' => false, 'errors' => ['Línea no accesible']]);
                exit;
            }

            $errores = $this->validarMaterial($data, $id);

            if (empty($errores)) {
                if ($materialModel->update($id, $data)) {
                    // Registrar en auditoría
                    $cambios = [];
                    foreach (['codigo', 'nombre', 'descripcion', 'linea_id', 'cantidad', 'estado'] as $campo) {
                        if ($material[$campo] != $data[$campo]) {
                            $cambios[$campo] = ['antes' => $material[$campo], 'despues' => $data[$campo]];
                        }
                    }
                    if (!empty($cambios)) {
                        $this->registrarAuditoria('UPDATE', 'materiales', $data['nombre'], $cambios);
                    }
                    echo json_encode(['success' => true, 'message' => 'Material actualizado exitosamente']);
                    exit;
                } else {
                    $errores[] = "Error al actualizar el material. Intenta de nuevo.";
                }
            }

            echo json_encode(['success' => false, 'errors' => $errores]);
            exit;
        }

        $this->view('materiales/editar', [
            'material'    => $material,
            'lineas'      => $lineas,
            'archivos'    => $archivos,
            'errores'     => $errores,
            'permisos'    => $permissions,
            'pageStyles'  => ['materiales', 'usuarios', 'materiales_form'],
            'pageScripts' => ['materiales'],
        ]);
    }

    /* =========================================================
       ELIMINAR MATERIAL
    ========================================================== */

    public function eliminar()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        // Validación inline sin echoing HTML
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        try {
            $permissions = new PermissionHelper();
            if (!$permissions->canDeleteMaterial($id)) {
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }

        $materialModel = new Material();
        $material = $materialModel->getById($id);

        if (!$material) {
            echo json_encode(['success' => false, 'message' => 'Material no encontrado']);
            exit;
        }

        // Registrar auditoría ANTES de eliminar con toda la información
        try {
            require_once __DIR__ . '/../models/Audit.php';
            $audit = new Audit();
            $audit->registrarCambio(
                $_SESSION['user']['id'],
                'materiales',
                'eliminar',
                json_encode([
                    'id' => $material['id'],
                    'nombre' => $material['nombre'],
                    'codigo' => $material['codigo'],
                    'cantidad' => $material['cantidad'],
                    'nodo_id' => $material['nodo_id'],
                    'linea_id' => $material['linea_id'],
                    'linea_nombre' => $this->getLineaNombre($material['linea_id']),
                    'descripcion' => $material['descripcion']
                ]),
                $_SESSION['user']['id']
            );
        } catch (Exception $e) {
            // Log silencioso si falla auditoría
        }

        if ($materialModel->delete($id, $_SESSION['user']['id'] ?? 1)) {
            echo json_encode(['success' => true, 'message' => 'Material eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el material']);
        }
        exit;
    }

    /* =========================================================
       MOVIMIENTOS DE INVENTARIO (Entrada/Salida)
    ========================================================== */

    public function registrarMovimiento()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAuth();

        $id = intval($_POST['id'] ?? 0);
        $tipo = trim($_POST['tipo'] ?? ''); // 'entrada' o 'salida'
        $cantidad = intval($_POST['cantidad'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');

        $errores = [];

        if ($id <= 0) {
            $errores[] = "Material inválido.";
        }

        if (!in_array($tipo, ['entrada', 'salida'])) {
            $errores[] = "Tipo de movimiento inválido.";
        }

        if ($cantidad <= 0) {
            $errores[] = "La cantidad debe ser mayor a 0.";
        }

        if (!empty($errores)) {
            echo json_encode(['success' => false, 'errors' => $errores]);
            exit;
        }

        $materialModel = new Material();
        $material = $materialModel->getById($id);

        if (!$material) {
            echo json_encode(['success' => false, 'errors' => ['Material no encontrado']]);
            exit;
        }

        // Verificar permisos para este movimiento
        try {
            $permissions = new PermissionHelper();
            
            // Verificar si puede hacer entrada o salida del material
            if (!$permissions->canEnterMaterial($id)) {
                echo json_encode(['success' => false, 'errors' => ['No tiene permiso para hacer movimientos en este material.']]);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'errors' => ['Error al verificar permisos: ' . $e->getMessage()]]);
            exit;
        }

        // Validar que en salidas no se retire más de lo disponible
        if ($tipo === 'salida' && $cantidad > $material['cantidad']) {
            echo json_encode([
                'success' => false,
                'errors' => [
                    "No hay suficiente stock. Disponible: {$material['cantidad']}, Solicitado: {$cantidad}"
                ]
            ]);
            exit;
        }

        // Registrar movimiento
        $movimientoData = [
            'material_id'      => $id,
            'usuario_id'       => $_SESSION['user']['id'],
            'tipo_movimiento'  => $tipo,
            'cantidad'         => $cantidad,
            'descripcion'      => $descripcion ?: ucfirst($tipo) . ' de inventario',
        ];

        if ($materialModel->registrarMovimiento($movimientoData)) {
            // Actualizar cantidad del material
            $nuevaCantidad = $tipo === 'entrada'
                ? $material['cantidad'] + $cantidad
                : $material['cantidad'] - $cantidad;

            $materialModel->actualizarCantidad($id, $nuevaCantidad);

            // Registrar en auditoría
            $cambioInfo = [
                'tipo_movimiento' => $tipo,
                'cantidad'        => $cantidad,
                'cantidad_anterior' => $material['cantidad'],
                'cantidad_nueva'  => $nuevaCantidad,
            ];
            $this->registrarAuditoria(strtoupper($tipo), 'materiales', $material['nombre'], $cambioInfo);

            echo json_encode(['success' => true, 'message' => ucfirst($tipo) . ' registrada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'errors' => ['Error al registrar el movimiento']]);
        }
        exit;
    }

    /* =========================================================
       HISTORIAL DE MOVIMIENTOS DE INVENTARIO
    ========================================================== */

    public function historialInventario()
    {
        $this->requireAuth();

        $materialModel = new Material();
        
        try {
            $permissions = new PermissionHelper();
        } catch (Exception $e) {
            http_response_code(403);
            echo "Error: " . $e->getMessage();
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
        if ($filtros['tipo_movimiento'] !== 'eliminado') {
            $historial = $materialModel->getHistorialMovimientos($material_id, $filtros);
        }
        
        // Obtener eliminaciones de materiales
        $eliminaciones = [];
        if (empty($filtros['tipo_movimiento']) || $filtros['tipo_movimiento'] === 'eliminado') {
            $filtros['material_id'] = $material_id;
            $eliminaciones = $materialModel->getEliminacionesMateriales($filtros);
        }
        
        // Combinar movimientos con eliminaciones y ordenar por fecha
        $historialCompleto = [];
        foreach ($historial as $mov) {
            $mov['tipo_registro'] = 'movimiento';
            $historialCompleto[] = $mov;
        }
        foreach ($eliminaciones as $elim) {
            $elim['tipo_registro'] = 'eliminacion';
            $historialCompleto[] = $elim;
        }
        
        // Filtrar historial según permisos del usuario
        $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
        $linea_user = $_SESSION['user']['linea_id'] ?? null;
        $rol = $_SESSION['user']['rol'];
        
        $historialFiltrado = [];
        foreach ($historialCompleto as $registro) {
            if ($rol === 'admin') {
                // Admin ve todo
                $historialFiltrado[] = $registro;
            } elseif ($rol === 'dinamizador') {
                // Dinamizador ve solo su nodo
                if (($registro['nodo_id'] ?? null) == $nodo_user) {
                    $historialFiltrado[] = $registro;
                }
            } elseif ($rol === 'usuario') {
                // Usuario ve solo su nodo y línea
                if (($registro['nodo_id'] ?? null) == $nodo_user && ($registro['linea_id'] ?? null) == $linea_user) {
                    $historialFiltrado[] = $registro;
                }
            }
        }
        $historialCompleto = $historialFiltrado;
        
        // Ordenar por fecha descendente
        usort($historialCompleto, function($a, $b) {
            $fechaA = strtotime($a['fecha_movimiento'] ?? $a['fecha_cambio'] ?? $a['fecha_creacion'] ?? 'now');
            $fechaB = strtotime($b['fecha_movimiento'] ?? $b['fecha_cambio'] ?? $b['fecha_creacion'] ?? 'now');
            return $fechaB - $fechaA;
        });
        
        // Obtener solo líneas accesibles
        $lineas = $permissions->getAccesibleLineas();
        
        // Obtener solo materiales accesibles
        $todosLosMateriales = $materialModel->all();
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

        $this->view('materiales/historial_inventario', [
            'historial'       => $historialCompleto,
            'lineas'          => $lineas,
            'materiales'      => $materiales,
            'filtros'         => $filtros,
            'permisos'        => $permissions,
            'pageStyles'      => ['materiales', 'usuarios'],
            'pageScripts'     => ['materiales'],
        ]);
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
            'material' => $material
        ]);
        exit;
    }

    /* =========================================================
       OBTENER DETALLES DEL MOVIMIENTO (AJAX)
    ========================================================== */

    public function obtenerDetallesMovimiento()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAuth();

        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $materialModel = new Material();
        $movimiento = $materialModel->getMovimientoById($id);

        if (!$movimiento) {
            echo json_encode(['success' => false, 'message' => 'Movimiento no encontrado']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'movimiento' => $movimiento
        ]);
        exit;
    }

    /* =========================================================
       UTILIDADES
    ========================================================== */

    private function getLineaNombre($lineaId)
    {
        if (!$lineaId) {
            return 'Sin línea';
        }
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT nombre FROM lineas WHERE id = :id");
            $stmt->execute([':id' => $lineaId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['nombre'] ?? 'Sin línea';
        } catch (Exception $e) {
            return 'Sin línea';
        }
    }

    /* =========================================================
       AUDITORÍA
    ========================================================== */

    private function registrarAuditoria($accion, $tabla, $descripcion, $detalles)
    {
        try {
            require_once __DIR__ . '/../models/Audit.php';
            $audit = new Audit();
            $audit->registrarCambio(
                $_SESSION['user']['id'] ?? null,
                $tabla,
                null,  // registro_id (no usado en este contexto)
                $accion,
                $detalles,
                $_SESSION['user']['id'] ?? null  // admin_id
            );
        } catch (Exception $e) {
            // Log silencioso si falla auditoría
            DebugHelper::error("Error al registrar auditoria: " . $e->getMessage());
        }
    }

    /* =========================================================
       ARCHIVOS DE MATERIAL
    ========================================================== */

    public function subirArchivo()
    {
        ob_start(); // Capturar cualquier output inadvertido
        header('Content-Type: application/json; charset=utf-8');
        DebugHelper::start('subirArchivo');
        DebugHelper::log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
        DebugHelper::log('CONTENT_TYPE: ' . ($_SERVER['CONTENT_TYPE'] ?? 'NOT SET'));
        
        // Leer input
        $input = file_get_contents('php://input');
        DebugHelper::log('Input length: ' . strlen($input) . ' bytes');
        if (strlen($input) > 0) {
            DebugHelper::log('Input first 100 chars: ' . substr($input, 0, 100));
        }
        
        try {
            // Validar sesión
            if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
                DebugHelper::warning('Sesión no válida');
                throw new Exception('No autorizado');
            }
            
            // Validar rol - admin y dinamizador pueden subir
            $rol = $_SESSION['user']['rol'] ?? 'usuario';
            if (!in_array($rol, ['admin', 'dinamizador'])) {
                DebugHelper::warning('Rol no permitido: ' . $rol);
                throw new Exception('Solo administradores y dinamizadores pueden subir archivos');
            }

            // Recibir datos JSON (no multipart)
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                DebugHelper::error('JSON decode error: ' . json_last_error_msg());
                DebugHelper::error('Raw input: ' . $input);
                throw new Exception('JSON inválido: ' . json_last_error_msg());
            }
            
            DebugHelper::log('JSON decodificado OK: ' . json_encode(array_keys($data ?? [])));
            
            if (!$data) {
                DebugHelper::warning('Data es null o vacío');
                throw new Exception('Datos inválidos');
            }
            
            $materialId = intval($data['material_id'] ?? 0);
            DebugHelper::log('Material ID recibido: ' . $materialId);
            
            if ($materialId <= 0) {
                DebugHelper::warning('Material ID inválido: ' . $materialId);
                throw new Exception('Material inválido');
            }

            if (empty($data['archivo_data'])) {
                DebugHelper::warning('No se envió archivo_data');
                throw new Exception('No se envió archivo');
            }

            DebugHelper::log('Validaciones iniciales OK');
            ob_end_clean(); // Limpiar antes de outputs importantes
            ob_start(); // Reiniciar para capturar de nuevo
            
            $materialModel = new Material();
            DebugHelper::log('Material model creado');
            
            $material = $materialModel->getById($materialId);
            DebugHelper::log('Material obtenido: ' . ($material ? 'OK' : 'NULL'));
            
            if (!$material) {
                throw new Exception('Material no encontrado');
            }

            // Validar permisos - el usuario debe poder editar este material
            try {
                $permissions = new PermissionHelper();
                if (!$permissions->canEditMaterial($materialId)) {
                    DebugHelper::warning('Permisos insuficientes para editar material: ' . $materialId);
                    throw new Exception('No tiene permisos para subir archivos a este material');
                }
                DebugHelper::log('Permisos verificados: OK');
            } catch (Exception $e) {
                DebugHelper::error('Error al verificar permisos: ' . $e->getMessage());
                throw $e;
            }

            // Decodificar archivo de base64
            $nombreOriginal = $data['archivo_nombre'] ?? 'archivo_sin_nombre';
            $tipoArchivo = $data['archivo_tipo'] ?? 'application/octet-stream';
            $tamanioArchivo = $data['archivo_tamaño'] ?? 0;
            $archivoBase64 = $data['archivo_data'] ?? '';
            
            DebugHelper::log('Archivo: ' . $nombreOriginal . ', tipo: ' . $tipoArchivo . ', tamaño: ' . $tamanioArchivo);
            
            // Validar extensión
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $extensionesPermitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];
            if (!in_array($ext, $extensionesPermitidas)) {
                throw new Exception('Tipo de archivo no permitido. Formatos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV');
            }

            // Validar tamaño (máximo 10MB)
            if ($tamanioArchivo > 10 * 1024 * 1024) {
                throw new Exception('El archivo supera el tamaño máximo de 10MB');
            }

            // Decodificar base64
            $archivoContenido = base64_decode($archivoBase64, true);
            if ($archivoContenido === false) {
                DebugHelper::warning('Error al decodificar base64');
                throw new Exception('Error al decodificar archivo');
            }

            DebugHelper::log('Archivo decodificado: ' . strlen($archivoContenido) . ' bytes');

            // Preparar ruta del archivo
            $nombreArchivo = "uploads/materiales/" . date('YmdHis_') . preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);
            $rutaSistema = __DIR__ . "/../../public/" . $nombreArchivo;
            $uploadDir = __DIR__ . "/../../public/uploads/materiales/";
            DebugHelper::log('Ruta sistema: ' . $rutaSistema);

            // Crear directorio si no existe
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0755, true)) {
                    throw new Exception('Error al crear directorio de uploads');
                }
                DebugHelper::log('Directorio creado');
            }

            // Guardar archivo
            if (file_put_contents($rutaSistema, $archivoContenido) === false) {
                throw new Exception('Error al guardar archivo en el sistema');
            }
            DebugHelper::log('Archivo guardado OK');

            // Guardar en BD
            $userModel = new User();
            $userId = $_SESSION['user']['id'];
            $usuario = $userModel->findById($userId);
            
            if (!$usuario) {
                DebugHelper::warning('Usuario no encontrado, usando ID=1');
                $userId = 1;
            }
            
            DebugHelper::log('Creando MaterialArchivo con material_id=' . $materialId . ', usuario_id=' . $userId);
            $archivoModel = new MaterialArchivo();
            $result = $archivoModel->create([
                'material_id' => $materialId,
                'nombre_original' => $nombreOriginal,
                'nombre_archivo' => $nombreArchivo,
                'tipo_archivo' => $tipoArchivo,
                'tamano' => strlen($archivoContenido),
                'usuario_id' => $userId
            ]);
            
            DebugHelper::log('Resultado create: ' . ($result ? 'true' : 'false'));

            if ($result) {
                // Registrar en auditoría (no debe fallar la carga si esto falla)
                try {
                    require_once __DIR__ . '/../models/Audit.php';
                    $audit = new Audit();
                    $audit->registrarCambio(
                        $_SESSION['user']['id'],
                        'material_archivos',
                        $materialId,
                        'subir_archivo',
                        [
                            'material_id' => $materialId,
                            'nombre_original' => $nombreOriginal,
                            'nombre_archivo' => $nombreArchivo,
                            'tamaño' => strlen($archivoContenido)
                        ],
                        $_SESSION['user']['id']
                    );
                    DebugHelper::log('Auditoría registrada OK');
                } catch (Exception $e) {
                    // Log silencioso - la auditoría no debe fallar la carga
                    DebugHelper::log('Error en auditoría al subir archivo: ' . $e->getMessage());
                }
                
                ob_end_clean();
                echo json_encode(['success' => true, 'message' => 'Archivo subido exitosamente']);
                DebugHelper::log('=== FIN subirArchivo SUCCESS ===');
            } else {
                // Eliminar archivo si la BD falla
                @unlink($rutaSistema);
                ob_end_clean();
                echo json_encode(['success' => false, 'message' => 'Error al guardar archivo en base de datos']);
                DebugHelper::log('=== FIN subirArchivo FAIL (BD) ===');
            }
            
        } catch (Exception $e) {
            ob_end_clean();
            DebugHelper::log('EXCEPCIÓN en subirArchivo: ' . $e->getMessage() . ' | Archivo: ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            DebugHelper::log('=== FIN subirArchivo EXCEPTION ===');
        }
        
        exit;
    }

    public function eliminarArchivo()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAuth();
        
        // Permitir solo admin y dinamizador
        $rol = $_SESSION['user']['rol'] ?? 'usuario';
        if (!in_array($rol, ['admin', 'dinamizador'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado para eliminar archivos']);
            http_response_code(403);
            exit;
        }

        $archivoId = intval($_POST['id'] ?? 0);

        if ($archivoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $archivoModel = new MaterialArchivo();
        $archivo = $archivoModel->getById($archivoId);

        if (!$archivo) {
            echo json_encode(['success' => false, 'message' => 'Archivo no encontrado']);
            exit;
        }

        // Para dinamizador, verificar que sea del mismo material y nodo
        if ($rol === 'dinamizador') {
            try {
                $permissions = new PermissionHelper();
                $material = (new Material())->getById($archivo['material_id']);
                if (!$material || !$permissions->canEditMaterial($material['id'])) {
                    echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar este archivo']);
                    http_response_code(403);
                    exit;
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error al verificar permisos']);
                http_response_code(403);
                exit;
            }
        }

        // Eliminar archivo del sistema
        $rutaArchivo = __DIR__ . "/../../public/" . $archivo['nombre_archivo'];
        if (file_exists($rutaArchivo)) {
            @unlink($rutaArchivo);
        }

        // Eliminar registro de BD
        $archivoModel->delete($archivoId);

        echo json_encode(['success' => true, 'message' => 'Archivo eliminado exitosamente']);
        exit;
    }

    public function obtenerArchivos()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAuth();

        $materialId = intval($_GET['material_id'] ?? 0);

        if ($materialId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Material inválido']);
            exit;
        }

        $archivoModel = new MaterialArchivo();
        $archivos = $archivoModel->getByMaterial($materialId);

        echo json_encode([
            'success' => true,
            'archivos' => $archivos
        ]);
        exit;
    }

    /* =========================================================
       CONTAR DOCUMENTOS POR MATERIAL (AJAX)
    ========================================================== */
    public function contarDocumentos()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAuth();

        $materialId = intval($_GET['material_id'] ?? 0);

        if ($materialId <= 0) {
            echo json_encode(['count' => 0, 'error' => 'Material inválido']);
            exit;
        }

        $archivoModel = new MaterialArchivo();
        $count = $archivoModel->countByMaterial($materialId);

        echo json_encode(['count' => $count]);
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
            echo "Material no encontrado.";
            exit;
        }

        $materialModel = new Material();
        $material = $materialModel->getById($materialId);

        if (!$material) {
            http_response_code(404);
            echo "Material no encontrado.";
            exit;
        }

        // Obtener archivos del material
        $archivoModel = new MaterialArchivo();
        $archivos = $archivoModel->getByMaterial($materialId);

        $this->view('materiales/detalles', [
            'material'    => $material,
            'archivos'    => $archivos,
            'pageStyles'  => ['materiales'],
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
            // Para admin, permitir ver todas las líneas sin importar el nodo
            $rol = $_SESSION['user']['rol'] ?? 'usuario';
            
            if ($rol === 'admin') {
                // Admin ve todas las líneas
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT DISTINCT l.id, l.nombre 
                    FROM lineas l
                    WHERE l.estado = 1 
                    ORDER BY l.nombre ASC
                ");
                $stmt->execute();
                $lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Otros roles solo ven líneas de su nodo
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT DISTINCT l.id, l.nombre 
                    FROM lineas l
                    INNER JOIN linea_nodo ln ON l.id = ln.linea_id
                    WHERE ln.nodo_id = :nodo_id AND ln.estado = 1 AND l.estado = 1 
                    ORDER BY l.nombre ASC
                ");
                $stmt->execute([':nodo_id' => $nodo_id]);
                $lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            echo json_encode(['success' => true, 'lineas' => $lineas]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}
