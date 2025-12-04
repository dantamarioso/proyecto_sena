<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../models/MaterialArchivo.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Nodo.php';
require_once __DIR__ . '/../models/Linea.php';
require_once __DIR__ . '/../helpers/PermissionHelper.php';
require_once __DIR__ . '/../helpers/ExcelHelper.php';
require_once __DIR__ . '/../helpers/PdfHelper.php';

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
                if ($linea['id'] == $data['linea_id']) {
                    // Si tiene nodo_id (usuario/dinamizador), comparar directo
                    if (isset($linea['nodo_id']) && $linea['nodo_id'] == $data['nodo_id']) {
                        $linea_ok = true;
                        break;
                    }
                    // Si tiene nodo_ids (admin), buscar en la lista
                    elseif (isset($linea['nodo_ids'])) {
                        $nodos = explode(',', $linea['nodo_ids']);
                        if (in_array($data['nodo_id'], $nodos)) {
                            $linea_ok = true;
                            break;
                        }
                    }
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
                    $this->registrarAuditoria('crear', 'materiales', $data['nombre'], $data, $materialId);
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
                'nodo_id'       => null, // Se establece a continuación
            ];

            // Determinar nodo_id según el rol
            $rol = $_SESSION['user']['rol'] ?? 'usuario';
            
            if ($rol === 'admin' && !empty($_POST['nodo_id'])) {
                // Admin puede especificar el nodo
                $data['nodo_id'] = intval($_POST['nodo_id']);
            } else {
                // Usuario/Dinamizador: mantener el nodo actual
                $data['nodo_id'] = $material['nodo_id'];
            }

            // Validar que la línea sea accesible y pertenezca al nodo
            $linea_ok = false;
            foreach ($lineas as $linea) {
                if ($linea['id'] == $data['linea_id']) {
                    // Si tiene nodo_id (usuario/dinamizador), comparar directo
                    if (isset($linea['nodo_id']) && $linea['nodo_id'] == $data['nodo_id']) {
                        $linea_ok = true;
                        break;
                    }
                    // Si tiene nodo_ids (admin), buscar en la lista
                    elseif (isset($linea['nodo_ids'])) {
                        $nodos = explode(',', $linea['nodo_ids']);
                        if (in_array($data['nodo_id'], $nodos)) {
                            $linea_ok = true;
                            break;
                        }
                    }
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
                echo json_encode(['success' => false, 'errors' => ['Línea no accesible o no pertenece al nodo']]);
                exit;
            }

            $errores = $this->validarMaterial($data, $id);

            if (empty($errores)) {
                if ($materialModel->update($id, $data)) {
                    // Registrar en auditoría - comparar cambios
                    $cambios = [];
                    foreach (['codigo', 'nombre', 'descripcion', 'nodo_id', 'linea_id', 'cantidad', 'estado'] as $campo) {
                        $valorAnterior = $material[$campo] ?? null;
                        $valorNuevo = $data[$campo] ?? null;
                        
                        // Comparar valores (conversión de tipos para nodo_id y linea_id)
                        if ($campo === 'nodo_id' || $campo === 'linea_id') {
                            $valAnt = intval($valorAnterior);
                            $valNuevo = intval($valorNuevo);
                            if ($valAnt !== $valNuevo) {
                                $cambios[$campo] = ['antes' => $valorAnterior, 'despues' => $valorNuevo];
                            }
                        } else {
                            // Comparación estricta para otros campos
                            if ($valorAnterior !== $valorNuevo) {
                                $cambios[$campo] = ['antes' => $valorAnterior, 'despues' => $valorNuevo];
                            }
                        }
                    }
                    
                    // Registrar auditoría siempre, incluso si no hay cambios detectados
                    // (para mayor trazabilidad)
                    $detallesAuditoria = !empty($cambios) ? $cambios : ['nota' => 'Sin cambios detectados'];
                    $this->registrarAuditoria('actualizar', 'materiales', $data['nombre'], $detallesAuditoria, $id);
                    
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
                    'usuario_id' => $_SESSION['user']['id']
                ],
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
            $this->registrarAuditoria('actualizar', 'materiales', $material['nombre'], $cambioInfo, $id);

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
        if ($filtros['tipo_movimiento'] !== 'eliminado' && $filtros['tipo_movimiento'] !== 'cambio') {
            $historial = $materialModel->getHistorialMovimientos($material_id, $filtros);
        }
        
        // Obtener eliminaciones de materiales
        $eliminaciones = [];
        if (empty($filtros['tipo_movimiento']) || $filtros['tipo_movimiento'] === 'eliminado') {
            $filtros['material_id'] = $material_id;
            $eliminaciones = $materialModel->getEliminacionesMateriales($filtros);
        }
        
        // Obtener cambios de auditoría (UPDATE de propiedades)
        $cambios = [];
        if (empty($filtros['tipo_movimiento']) || $filtros['tipo_movimiento'] === 'cambio') {
            $cambios = $materialModel->getHistorialCambios($material_id, $filtros);
        }
        
        // Combinar movimientos, eliminaciones y cambios, y ordenar por fecha
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

    private function getNodoNombre($nodoId)
    {
        if (!$nodoId) {
            return 'Sin nodo';
        }
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT nombre FROM nodos WHERE id = :id");
            $stmt->execute([':id' => $nodoId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['nombre'] ?? 'Sin nodo';
        } catch (Exception $e) {
            return 'Sin nodo';
        }
    }

    /* =========================================================
       AUDITORÍA
    ========================================================== */

    private function registrarAuditoria($accion, $tabla, $descripcion, $detalles, $registro_id = null)
    {
        try {
            require_once __DIR__ . '/../models/Audit.php';
            $audit = new Audit();
            $audit->registrarCambio(
                $_SESSION['user']['id'] ?? null,
                $tabla,
                $registro_id,  // registro_id (material_id en este caso)
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
            
            if ($result) {
                // Registrar en auditoría (no debe fallar la carga si esto falla)
                try {
                    require_once __DIR__ . '/../models/Audit.php';
                    $audit = new Audit();
                    $audit->registrarCambio(
                        $_SESSION['user']['id'],
                        'materiales',
                        $materialId,
                        'actualizar',
                        [
                            'material_id' => $materialId,
                            'nombre_original' => $nombreOriginal,
                            'nombre_archivo' => $nombreArchivo,
                            'tamaño' => strlen($archivoContenido)
                        ],
                        $_SESSION['user']['id']
                    );
                } catch (Exception $e) {
                    // Log silencioso - la auditoría no debe fallar la carga
                }
                
                ob_end_clean();
                echo json_encode(['success' => true, 'message' => 'Archivo subido exitosamente']);
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

    /* =========================================================
       IMPORTAR MATERIALES DESDE ARCHIVO PLANO (CSV/TXT)
    ========================================================== */
    public function importarMateriales()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAuth();

        // Solo admin y dinamizador pueden importar
        $rol = $_SESSION['user']['rol'] ?? 'usuario';
        if (!in_array($rol, ['admin', 'dinamizador'])) {
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para importar materiales.']);
            http_response_code(403);
            exit;
        }

        // Validar que se envió archivo
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $error = $_FILES['archivo']['error'] ?? 'desconocido';
            echo json_encode(['success' => false, 'message' => "Error al subir archivo (código: $error)"]);
            exit;
        }

        $archivo = $_FILES['archivo'];
        $nombreArchivo = strtolower(pathinfo($archivo['name'], PATHINFO_FILENAME));
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

<<<<<<< HEAD
        // Validar extensión
        if (!in_array($extension, ['csv', 'txt'])) {
            echo json_encode(['success' => false, 'message' => 'Solo se aceptan archivos CSV o TXT.']);
=======
        // Validar extensión - Ahora acepta CSV, TXT y XLSX
        if (!in_array($extension, ['csv', 'txt', 'xlsx', 'xls'])) {
            echo json_encode(['success' => false, 'message' => 'Solo se aceptan archivos CSV, TXT, XLS o XLSX.']);
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
            exit;
        }

        // Validar tamaño (máximo 5MB)
        if ($archivo['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'El archivo no debe exceder 5MB.']);
            exit;
        }

        try {
<<<<<<< HEAD
            // Leer contenido del archivo
            $contenido = file_get_contents($archivo['tmp_name']);
            if ($contenido === false) {
                throw new Exception('Error al leer el archivo.');
            }

            // Convertir a UTF-8 si es necesario
            $contenido = mb_convert_encoding($contenido, 'UTF-8', mb_detect_encoding($contenido, 'UTF-8, ISO-8859-1', true));

            // Detectar delimitador (coma, punto y coma, tabulación)
            $lineas = array_filter(explode("\n", trim($contenido)), function($line) {
                return trim($line) !== '';
            });

=======
            $lineas = [];
            $delimitador = ';'; // Por defecto punto y coma
            
            // Procesar según tipo de archivo
            if (in_array($extension, ['xlsx', 'xls'])) {
                // Procesar archivo Excel con PhpSpreadsheet
                require_once __DIR__ . '/../../vendor/autoload.php';
                
                try {
                    // Verificar que el archivo sea realmente un Excel válido
                    $mimeType = mime_content_type($archivo['tmp_name']);
                    
                    $validMimeTypes = [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                        'application/vnd.ms-excel', // .xls
                        'application/zip' // A veces .xlsx se detecta como zip
                    ];
                    
                    if (!in_array($mimeType, $validMimeTypes)) {
                        echo json_encode([
                            'success' => false, 
                            'message' => 'El archivo no es un Excel válido. Por favor, exporta el archivo como CSV desde Excel (Guardar como → CSV).',
                            'mime_detected' => $mimeType
                        ]);
                        exit;
                    }
                    
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo['tmp_name']);
                    $worksheet = $spreadsheet->getActiveSheet();
                    
                    // Verificar si la hoja está protegida
                    if ($worksheet->getProtection()->getSheet()) {
                        echo json_encode([
                            'success' => false, 
                            'message' => 'El archivo Excel está protegido. Por favor, remueve la protección o exporta como CSV sin protección.'
                        ]);
                        exit;
                    }
                    
                    // Obtener todas las filas sin parámetros especiales
                    $rows = $worksheet->toArray();
                    
                    if (empty($rows)) {
                        echo json_encode([
                            'success' => false, 
                            'message' => 'El archivo Excel está vacío.'
                        ]);
                        exit;
                    }
                    
                    // Convertir filas a formato de líneas de texto
                    foreach ($rows as $row) {
                        // Saltar filas completamente vacías
                        $hayDatos = false;
                        foreach ($row as $cell) {
                            if (!is_null($cell) && trim((string)$cell) !== '') {
                                $hayDatos = true;
                                break;
                            }
                        }
                        
                        if (!$hayDatos) {
                            continue;
                        }
                        
                        // Convertir fila a texto CSV limpio
                        $lineaLimpia = [];
                        foreach ($row as $cell) {
                            $valorLimpio = str_replace([';', "\n", "\r", "\t"], [',', ' ', ' ', ' '], (string)$cell);
                            $lineaLimpia[] = trim($valorLimpio);
                        }
                        
                        $lineas[] = implode(';', $lineaLimpia);
                    }
                    
                    if (empty($lineas)) {
                        echo json_encode([
                            'success' => false, 
                            'message' => 'El archivo Excel no contiene datos válidos.'
                        ]);
                        exit;
                    }
                    
                } catch (\Exception $e) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Error al procesar el archivo Excel. Recomendación: Abre el archivo en Excel y guárdalo como CSV (Archivo → Guardar como → CSV).',
                        'error_detalle' => $e->getMessage()
                    ]);
                    exit;
                }
                
            } else {
                // Procesar archivo CSV/TXT
                // Leer contenido del archivo
                $contenido = file_get_contents($archivo['tmp_name']);
                if ($contenido === false) {
                    throw new Exception('Error al leer el archivo.');
                }

                // Convertir a UTF-8 si es necesario
                $contenido = mb_convert_encoding($contenido, 'UTF-8', mb_detect_encoding($contenido, 'UTF-8, ISO-8859-1', true));

                // Detectar delimitador (coma, punto y coma, tabulación)
                $lineas = array_filter(explode("\n", trim($contenido)), function($line) {
                    return trim($line) !== '';
                });
                
                if (empty($lineas)) {
                    echo json_encode(['success' => false, 'message' => 'El archivo está vacío.']);
                    exit;
                }

                // Detectar delimitador automáticamente
                $delimitador = $this->detectarDelimitador($lineas[0]);
            }
            
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
            if (empty($lineas)) {
                echo json_encode(['success' => false, 'message' => 'El archivo está vacío.']);
                exit;
            }

<<<<<<< HEAD
            // Detectar delimitador automáticamente
            $primeraLinea = $lineas[0];
            $delimitador = $this->detectarDelimitador($primeraLinea);

            // Procesar encabezados
            $encabezados = str_getcsv($primeraLinea, $delimitador);
            $encabezadosLimpios = array_map(function($h) {
                return strtolower(trim(preg_replace('/\s+/', '_', $h)));
            }, $encabezados);

            // Validar encabezados requeridos (flexibilidad en nombres)
            $camposRequeridos = ['codigo', 'nombre', 'linea'];
            $camposEncontrados = [];
=======
            // Obtener primera línea
            $primeraLinea = $lineas[0];
            
            // Procesar encabezados
            $encabezados = str_getcsv($primeraLinea, $delimitador);
            
            if (empty($encabezados) || count($encabezados) < 2) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al procesar encabezados del archivo.'
                ]);
                exit;
            }
            
            $encabezadosLimpios = array_map(function($h) {
                // Limpiar espacios
                $h = trim($h);
                // Eliminar BOM si existe
                $h = str_replace("\xEF\xBB\xBF", '', $h);
                // Eliminar acentos - IMPORTANTE: hacer esto ANTES de convertir a minúsculas
                $h = str_replace(
                    ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Ü', 'á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
                    ['A', 'E', 'I', 'O', 'U', 'N', 'U', 'a', 'e', 'i', 'o', 'u', 'n', 'u'],
                    $h
                );
                // Ahora convertir a minúsculas
                $h = strtolower($h);
                // Eliminar espacios y caracteres especiales
                $h = preg_replace('/[^a-z0-9_]/', '', $h);
                return $h;
            }, $encabezados);

            // Validar encabezados requeridos (flexibilidad en nombres)
            $camposRequeridos = ['codigo', 'nombre'];
            $requiereLinea = false; // Se validará que tenga linea O linea_id
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
            $mapeoEncabezados = [];

            foreach ($encabezados as $idx => $encabezado) {
                $limpio = $encabezadosLimpios[$idx];
                
<<<<<<< HEAD
                if (in_array($limpio, ['codigo', 'code', 'product_code', 'codigo_producto', 'product_code_', 'cod'])) {
                    $mapeoEncabezados['codigo'] = $idx;
                    $camposEncontrados[] = 'codigo';
                }
                elseif (in_array($limpio, ['nombre', 'name', 'producto', 'product_name', 'product', 'descripción_corta'])) {
                    $mapeoEncabezados['nombre'] = $idx;
                    $camposEncontrados[] = 'nombre';
                }
                elseif (in_array($limpio, ['linea', 'line', 'linea_id', 'line_id', 'línea'])) {
                    $mapeoEncabezados['linea'] = $idx;
                    $camposEncontrados[] = 'linea';
                }
                elseif (in_array($limpio, ['descripcion', 'description', 'desc', 'descrip', 'descripción'])) {
=======
                if (in_array($limpio, ['codigo', 'code', 'product_code', 'codigo_producto', 'product_code_', 'cod', 'sku'])) {
                    $mapeoEncabezados['codigo'] = $idx;
                }
                elseif (in_array($limpio, ['nombre', 'name', 'producto', 'product_name', 'product', 'descripcion_corta'])) {
                    $mapeoEncabezados['nombre'] = $idx;
                }
                elseif (in_array($limpio, ['linea_id', 'line_id', 'id_linea'])) {
                    $mapeoEncabezados['linea_id'] = $idx;
                }
                elseif (in_array($limpio, ['linea', 'line'])) {
                    $mapeoEncabezados['linea'] = $idx;
                }
                elseif (in_array($limpio, ['nodo_id', 'node_id', 'id_nodo'])) {
                    $mapeoEncabezados['nodo_id'] = $idx;
                }
                elseif (in_array($limpio, ['nodo', 'node'])) {
                    $mapeoEncabezados['nodo'] = $idx;
                }
                elseif (in_array($limpio, ['descripcion', 'description', 'desc', 'descrip'])) {
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                    $mapeoEncabezados['descripcion'] = $idx;
                }
                elseif (in_array($limpio, ['cantidad', 'quantity', 'stock', 'qty', 'count', 'cantidad_inicial'])) {
                    $mapeoEncabezados['cantidad'] = $idx;
                }
                elseif (in_array($limpio, ['estado', 'status', 'state', 'active', 'activo'])) {
                    $mapeoEncabezados['estado'] = $idx;
                }
<<<<<<< HEAD
                elseif (in_array($limpio, ['nodo', 'node', 'nodo_id', 'node_id'])) {
                    $mapeoEncabezados['nodo'] = $idx;
                }
            }

            // Verificar si faltan campos requeridos
            $camposFaltantes = array_diff($camposRequeridos, $camposEncontrados);
            if (!empty($camposFaltantes)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan campos requeridos: ' . implode(', ', $camposFaltantes),
                    'encabezados_encontrados' => $camposEncontrados,
                    'encabezados_esperados' => ['código', 'nombre', 'línea', 'descripción (opcional)', 'cantidad (opcional)', 'estado (opcional)']
=======
            }

            // Verificar si faltan campos requeridos usando el mapeo
            $camposFaltantes = [];
            foreach ($camposRequeridos as $campo) {
                if (!isset($mapeoEncabezados[$campo])) {
                    $camposFaltantes[] = $campo;
                }
            }
            
            // Verificar que tenga linea O linea_id
            if (!isset($mapeoEncabezados['linea']) && !isset($mapeoEncabezados['linea_id'])) {
                $camposFaltantes[] = 'linea o linea_id';
            }
            
            if (!empty($camposFaltantes)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Faltan campos requeridos: ' . implode(', ', $camposFaltantes) . '. Encabezados esperados: código, nombre, línea o linea_id, nodo_id (opcional), descripción (opcional), cantidad (opcional).'
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                ]);
                exit;
            }

            // Procesar datos
            $materialModel = new Material();
            $erroresImportacion = [];
            $materialesCreados = 0;
<<<<<<< HEAD
=======
            $materialesOmitidos = 0;
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
            $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
            $linea_user = $_SESSION['user']['linea_id'] ?? null;

            // Obtener permisos
            $permissions = new PermissionHelper();
            $lineasAccesibles = $permissions->getAccesibleLineas();
            $lineasAccesiblesIds = array_column($lineasAccesibles, 'id');

            // Construir mapa de líneas por nombre
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id, nombre FROM lineas WHERE estado = 1");
            $stmt->execute();
            $lineasBD = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $mapa_lineas = [];
            foreach ($lineasBD as $linea) {
                $nombreLimpio = strtolower(preg_replace('/\s+/', ' ', trim($linea['nombre'])));
                $mapa_lineas[$nombreLimpio] = $linea['id'];
            }

            for ($i = 1; $i < count($lineas); $i++) {
                $datosLinea = str_getcsv($lineas[$i], $delimitador);
                
                // Saltar líneas vacías
                if (empty(trim(implode('', $datosLinea)))) {
                    continue;
                }

                try {
                    // Limpiar y extraer datos con función mejorada
                    $codigo = isset($mapeoEncabezados['codigo']) 
                        ? $this->limpiarCodigo($datosLinea[$mapeoEncabezados['codigo']] ?? '')
                        : '';
                    
                    $nombre = isset($mapeoEncabezados['nombre'])
                        ? $this->limpiarNombre($datosLinea[$mapeoEncabezados['nombre']] ?? '')
                        : '';
                    
<<<<<<< HEAD
                    $nombreLinea = isset($mapeoEncabezados['linea'])
                        ? $this->limpiarTexto($datosLinea[$mapeoEncabezados['linea']] ?? '')
                        : '';
                    
=======
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                    $descripcion = isset($mapeoEncabezados['descripcion'])
                        ? $this->limpiarDescripcion($datosLinea[$mapeoEncabezados['descripcion']] ?? '')
                        : '';
                    
                    $cantidad = isset($mapeoEncabezados['cantidad'])
                        ? $this->limpiarCantidad($datosLinea[$mapeoEncabezados['cantidad']] ?? '0')
                        : 0;
                    
                    $estado = isset($mapeoEncabezados['estado'])
                        ? $this->limpiarEstado($datosLinea[$mapeoEncabezados['estado']] ?? '1')
                        : 1;

<<<<<<< HEAD
                    // Validaciones
                    if (empty($codigo) || empty($nombre) || empty($nombreLinea)) {
                        $erroresImportacion[$i] = 'Falta código, nombre o línea';
                        continue;
                    }

                    // Mapear nombre de línea a ID
                    $linea_id = $mapa_lineas[$nombreLinea] ?? null;
                    if (!$linea_id) {
                        $erroresImportacion[$i] = "Línea '$nombreLinea' no encontrada";
=======
                    // Validaciones básicas
                    if (empty($codigo) || empty($nombre)) {
                        $erroresImportacion[$i] = 'Falta código o nombre';
                        continue;
                    }

                    // Determinar linea_id: puede venir como ID directo o como nombre
                    $linea_id = null;
                    if (isset($mapeoEncabezados['linea_id'])) {
                        // Si viene linea_id, usarlo directamente
                        $linea_id = intval(trim($datosLinea[$mapeoEncabezados['linea_id']] ?? ''));
                        if ($linea_id <= 0) {
                            $erroresImportacion[$i] = 'linea_id inválido';
                            continue;
                        }
                    } elseif (isset($mapeoEncabezados['linea'])) {
                        // Si viene nombre de línea, buscar el ID
                        $nombreLinea = $this->limpiarTexto($datosLinea[$mapeoEncabezados['linea']] ?? '');
                        if (empty($nombreLinea)) {
                            $erroresImportacion[$i] = 'Línea vacía';
                            continue;
                        }
                        $linea_id = $mapa_lineas[$nombreLinea] ?? null;
                        if (!$linea_id) {
                            $erroresImportacion[$i] = "Línea '$nombreLinea' no encontrada";
                            continue;
                        }
                    } else {
                        $erroresImportacion[$i] = 'Falta línea o linea_id';
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                        continue;
                    }

                    // Verificar acceso a la línea
                    if (!in_array($linea_id, $lineasAccesiblesIds)) {
                        $erroresImportacion[$i] = "No tiene acceso a la línea '$nombreLinea'";
                        continue;
                    }

<<<<<<< HEAD
                    // Validar código único
                    if ($materialModel->codigoExiste($codigo)) {
                        $erroresImportacion[$i] = "El código '$codigo' ya existe";
                        continue;
=======
                    // Validar código único - OMITIR si ya existe en lugar de error
                    if ($materialModel->codigoExiste($codigo)) {
                        $materialesOmitidos++;
                        continue; // Omitir silenciosamente materiales duplicados
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                    }

                    // Determinar nodo
                    $nodo_id = $nodo_user; // Por defecto, el nodo del usuario
<<<<<<< HEAD
                    if ($rol === 'admin' && isset($mapeoEncabezados['nodo'])) {
                        // Admin puede especificar nodo
                        $nodoNombre = $this->limpiarTexto($datosLinea[$mapeoEncabezados['nodo']] ?? '');
                        if (!empty($nodoNombre)) {
                            $stmt = $db->prepare("SELECT id FROM nodos WHERE LOWER(REPLACE(nombre, ' ', '')) = LOWER(REPLACE(:nombre, ' ', '')) AND estado = 1");
                            $stmt->execute([':nombre' => $nodoNombre]);
                            $nodoResult = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($nodoResult) {
                                $nodo_id = $nodoResult['id'];
=======
                    if ($rol === 'admin') {
                        // Admin puede especificar nodo por ID o nombre
                        if (isset($mapeoEncabezados['nodo_id'])) {
                            // Si viene nodo_id, usarlo directamente
                            $nodo_id_csv = intval(trim($datosLinea[$mapeoEncabezados['nodo_id']] ?? ''));
                            if ($nodo_id_csv > 0) {
                                $nodo_id = $nodo_id_csv;
                            }
                        } elseif (isset($mapeoEncabezados['nodo'])) {
                            // Si viene nombre de nodo, buscar el ID
                            $nodoNombre = $this->limpiarTexto($datosLinea[$mapeoEncabezados['nodo']] ?? '');
                            if (!empty($nodoNombre)) {
                                $stmt = $db->prepare("SELECT id FROM nodos WHERE LOWER(REPLACE(nombre, ' ', '')) = LOWER(REPLACE(:nombre, ' ', '')) AND estado = 1");
                                $stmt->execute([':nombre' => $nodoNombre]);
                                $nodoResult = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($nodoResult) {
                                    $nodo_id = $nodoResult['id'];
                                }
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                            }
                        }
                    }

                    if (!$nodo_id) {
                        $erroresImportacion[$i] = 'No se pudo determinar el nodo';
                        continue;
                    }

                    // Crear material
                    $dataMaterial = [
                        'codigo' => $codigo,
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'linea_id' => $linea_id,
                        'cantidad' => max(0, $cantidad),
                        'estado' => $estado,
                        'nodo_id' => $nodo_id,
                    ];

                    // Validar
                    $erroresValidacion = $this->validarMaterial($dataMaterial);
                    if (!empty($erroresValidacion)) {
                        $erroresImportacion[$i] = implode('; ', $erroresValidacion);
                        continue;
                    }

                    // Insertar
                    $materialId = $materialModel->create($dataMaterial);
                    if ($materialId) {
                        // Registrar movimiento inicial si hay cantidad
                        if ($dataMaterial['cantidad'] > 0) {
                            $materialModel->registrarMovimiento([
                                'material_id' => $materialId,
                                'usuario_id' => $_SESSION['user']['id'],
                                'tipo_movimiento' => 'entrada',
                                'cantidad' => $dataMaterial['cantidad'],
                                'descripcion' => 'Cantidad inicial por importación',
                            ]);
                        }

                        // Registrar en auditoría
                        try {
                            $this->registrarAuditoria('crear', 'materiales', $nombre, $dataMaterial, $materialId);
                        } catch (Exception $e) {
                            // Log silencioso
                        }

                        $materialesCreados++;
                    } else {
                        $erroresImportacion[$i] = 'Error al crear el material en la base de datos';
                    }

                } catch (Exception $e) {
                    $erroresImportacion[$i] = 'Error: ' . $e->getMessage();
                }
            }

            // Respuesta
<<<<<<< HEAD
            $respuesta = [
                'success' => true,
                'message' => "Importación completada. Se crearon $materialesCreados materiales.",
                'materiales_creados' => $materialesCreados,
=======
            $mensaje = "Importación completada. Se crearon $materialesCreados materiales.";
            if ($materialesOmitidos > 0) {
                $mensaje .= " Se omitieron $materialesOmitidos materiales duplicados.";
            }
            
            $respuesta = [
                'success' => true,
                'message' => $mensaje,
                'materiales_creados' => $materialesCreados,
                'materiales_omitidos' => $materialesOmitidos,
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                'total_procesados' => count($lineas) - 1,
            ];

            if (!empty($erroresImportacion)) {
                $respuesta['advertencias'] = true;
                $respuesta['errores_por_linea'] = $erroresImportacion;
<<<<<<< HEAD
                $respuesta['message'] = "Se crearon $materialesCreados materiales, pero hubo " . count($erroresImportacion) . " errores.";
=======
                $respuesta['message'] = "Se crearon $materialesCreados materiales";
                if ($materialesOmitidos > 0) {
                    $respuesta['message'] .= ", se omitieron $materialesOmitidos duplicados";
                }
                $respuesta['message'] .= ", pero hubo " . count($erroresImportacion) . " errores.";
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
            }

            echo json_encode($respuesta);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al procesar archivo: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Limpiar código - MAYÚSCULAS, sin espacios extras, caracteres especiales permitidos
     */
    private function limpiarCodigo($valor)
    {
        $valor = trim($valor);
        // Convertir a mayúsculas
        $valor = strtoupper($valor);
        // Eliminar espacios múltiples
        $valor = preg_replace('/\s+/', ' ', $valor);
        // Eliminar espacios al inicio y final
        $valor = trim($valor);
        return $valor;
    }

    /**
     * Limpiar nombre - Capitalizado (primera letra mayúscula, resto minúsculas)
     */
    private function limpiarNombre($valor)
    {
        $valor = trim($valor);
        // Convertir a minúsculas primero
        $valor = strtolower($valor);
        // Capitalizar cada palabra
        $valor = ucwords($valor);
        // Eliminar espacios múltiples
        $valor = preg_replace('/\s+/', ' ', $valor);
        // Eliminar espacios al inicio y final
        $valor = trim($valor);
        return $valor;
    }

    /**
     * Limpiar descripción - Primera mayúscula, resto minúsculas
     */
    private function limpiarDescripcion($valor)
    {
        $valor = trim($valor);
        // Convertir a minúsculas
        $valor = strtolower($valor);
        // Primera letra mayúscula
        if (strlen($valor) > 0) {
            $valor = strtoupper($valor[0]) . substr($valor, 1);
        }
        // Eliminar espacios múltiples
        $valor = preg_replace('/\s+/', ' ', $valor);
        // Eliminar espacios al inicio y final
        $valor = trim($valor);
        return $valor;
    }

    /**
     * Limpiar texto genérico - minúsculas, sin espacios extras
     */
    private function limpiarTexto($valor)
    {
        $valor = trim($valor);
        // Convertir a minúsculas
        $valor = strtolower($valor);
        // Eliminar espacios múltiples
        $valor = preg_replace('/\s+/', ' ', $valor);
        // Eliminar espacios al inicio y final
        $valor = trim($valor);
        return $valor;
    }

    /**
     * Limpiar cantidad - Solo números, positivos
     */
    private function limpiarCantidad($valor)
    {
        $valor = trim($valor);
        // Extraer solo números
        $valor = preg_replace('/[^0-9]/', '', $valor);
        // Convertir a entero
        $cantidad = intval($valor);
        return max(0, $cantidad);
    }

    /**
     * Limpiar estado - Convertir a binario (1 o 0)
     */
    private function limpiarEstado($valor)
    {
        $valor = trim($valor);
        $valor = strtolower($valor);
        // Valores verdaderos
        $verdadero = ['1', 'activo', 'active', 'si', 'yes', 'true', 'sí', 'v', 's', 'y'];
        return in_array($valor, $verdadero) ? 1 : 0;
    }

    /**
     * Detectar delimitador del archivo
     */
    private function detectarDelimitador($linea)
    {
        $delimitadores = [',', ';', "\t", '|'];
        $maxComas = 0;
        $delimitadorDetectado = ',';

        foreach ($delimitadores as $delim) {
            $cantidad = substr_count($linea, $delim);
            if ($cantidad > $maxComas) {
                $maxComas = $cantidad;
                $delimitadorDetectado = $delim;
            }
        }

        return $delimitadorDetectado;
    }

    /* =========================================================
       EXPORTAR MATERIALES A EXCEL
    ========================================================== */
    public function exportarMateriales()
    {
        $this->requireAuth();

        try {
            $materialModel = new Material();
            $nodoModel = new Nodo();
            $lineaModel = new Linea();
            
            // Determinar qué materiales puede ver según su rol
            $rol = $_SESSION['user']['rol'] ?? 'usuario';
            $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
            $linea_user = $_SESSION['user']['linea_id'] ?? null;

            $todosLosMateriales = $materialModel->all();
            $materiales = [];

            if ($rol === 'admin') {
                // Admin: todos los materiales
                $materiales = $todosLosMateriales;
            } elseif ($rol === 'dinamizador') {
                // Dinamizador: solo su nodo
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user) {
                        $materiales[] = $mat;
                    }
                }
            } elseif ($rol === 'usuario') {
                // Usuario: solo su nodo y línea
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user && $mat['linea_id'] == $linea_user) {
                        $materiales[] = $mat;
                    }
                }
            }

            if (empty($materiales)) {
                echo json_encode(['success' => false, 'message' => 'No hay materiales para exportar']);
                exit;
            }

            // Obtener listas de nodos y líneas según rol
            $todosNodos = $nodoModel->all();
            $todasLineas = $lineaModel->all();

            // Crear Excel con soporte de múltiples sheets
            $excel = new ExcelHelper();
            
            // ============================================================
            // SHEET 1: MATERIALES (sin ID)
            // ============================================================
            $excel->createSheet('Materiales');
            
<<<<<<< HEAD
            // Definir encabezados según rol (SIN ID)
            if ($rol === 'admin') {
                $encabezados = ['CÓDIGO', 'NOMBRE', 'DESCRIPCIÓN', 'NODO', 'LÍNEA', 'CANTIDAD', 'ESTADO', 'FECHA CREACIÓN'];
            } elseif ($rol === 'dinamizador') {
                $encabezados = ['CÓDIGO', 'NOMBRE', 'DESCRIPCIÓN', 'LÍNEA', 'CANTIDAD', 'ESTADO', 'FECHA CREACIÓN'];
            } else { // usuario
                $encabezados = ['CÓDIGO', 'NOMBRE', 'DESCRIPCIÓN', 'CANTIDAD', 'ESTADO', 'FECHA CREACIÓN'];
=======
            // Definir encabezados según rol - SOLO IDs
            if ($rol === 'admin') {
                $encabezados = ['CÓDIGO', 'NOMBRE', 'DESCRIPCIÓN', 'NODO_ID', 'LINEA_ID', 'CANTIDAD'];
            } elseif ($rol === 'dinamizador') {
                $encabezados = ['CÓDIGO', 'NOMBRE', 'DESCRIPCIÓN', 'LINEA_ID', 'CANTIDAD'];
            } else { // usuario
                $encabezados = ['CÓDIGO', 'NOMBRE', 'DESCRIPCIÓN', 'CANTIDAD'];
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
            }
            
            $excel->setHeaders($encabezados);

            // Ordenar materiales
            usort($materiales, function($a, $b) {
                return $b['id'] - $a['id'];
            });

<<<<<<< HEAD
            // Agregar datos según rol (SIN ID)
            if ($rol === 'admin') {
                // Admin ve: CÓDIGO, NOMBRE, DESCRIPCIÓN, NODO, LÍNEA, CANTIDAD, ESTADO, FECHA
                $nodoNames = array_map(function($n) { return $n['nombre']; }, $todosNodos);
                $lineaNames = array_map(function($l) { return $l['nombre']; }, $todasLineas);

=======
            // Agregar datos según rol - SOLO IDs
            if ($rol === 'admin') {
                // Admin ve: CÓDIGO, NOMBRE, DESCRIPCIÓN, NODO_ID, LINEA_ID, CANTIDAD
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                foreach ($materiales as $mat) {
                    $excel->addRow([
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
<<<<<<< HEAD
                        $mat['nodo_nombre'] ?? 'Sin nodo',
                        $mat['linea_nombre'] ?? 'Sin línea',
                        $mat['cantidad'],
                        $mat['estado'] == 1 ? 'Activo' : 'Inactivo',
                        $mat['fecha_creacion'] ? date('Y-m-d', strtotime($mat['fecha_creacion'])) : 'N/A',
                    ]);
                }

                // Agregar validaciones (dropdowns) para admin: columnas D (nodo) y E (línea)
                $excel->addValidation('D', $nodoNames);
                $excel->addValidation('E', $lineaNames);

            } elseif ($rol === 'dinamizador') {
                // Dinamizador ve: CÓDIGO, NOMBRE, DESCRIPCIÓN, LÍNEA, CANTIDAD, ESTADO, FECHA
                $lineaNames = array_map(function($l) { return $l['nombre']; }, $todasLineas);

=======
                        $mat['nodo_id'] ?? '',
                        $mat['linea_id'] ?? '',
                        $mat['cantidad'],
                    ]);
                }

                // Sin validaciones ya que no hay columnas de nombres

            } elseif ($rol === 'dinamizador') {
                // Dinamizador ve: CÓDIGO, NOMBRE, DESCRIPCIÓN, LINEA_ID, CANTIDAD
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                foreach ($materiales as $mat) {
                    $excel->addRow([
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
<<<<<<< HEAD
                        $mat['linea_nombre'] ?? 'Sin línea',
                        $mat['cantidad'],
                        $mat['estado'] == 1 ? 'Activo' : 'Inactivo',
                        $mat['fecha_creacion'] ? date('Y-m-d', strtotime($mat['fecha_creacion'])) : 'N/A',
                    ]);
                }

                // Agregar validaciones (dropdowns) para dinamizador: columna D (línea)
                $excel->addValidation('D', $lineaNames);

            } else { // usuario
                // Usuario ve: CÓDIGO, NOMBRE, DESCRIPCIÓN, CANTIDAD, ESTADO, FECHA
=======
                        $mat['linea_id'] ?? '',
                        $mat['cantidad'],
                    ]);
                }

                // Sin validaciones ya que no hay columna de nombre

            } else { // usuario
                // Usuario ve: CÓDIGO, NOMBRE, DESCRIPCIÓN, CANTIDAD
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                foreach ($materiales as $mat) {
                    $excel->addRow([
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['cantidad'],
<<<<<<< HEAD
                        $mat['estado'] == 1 ? 'Activo' : 'Inactivo',
                        $mat['fecha_creacion'] ? date('Y-m-d', strtotime($mat['fecha_creacion'])) : 'N/A',
=======
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
                    ]);
                }
                // Usuario no tiene validaciones
            }

            // ============================================================
<<<<<<< HEAD
            // SHEET 2: LÍNEAS
            // ============================================================
            $excel->createSheet('Lineas');
            $excel->setHeaders(['NOMBRE', 'DESCRIPCIÓN', 'ESTADO', 'CANTIDAD DE MATERIALES']);
=======
            // SHEET 2: LÍNEAS CON ID
            // ============================================================
            $excel->createSheet('Lineas');
            $excel->setHeaders(['ID', 'NOMBRE', 'DESCRIPCIÓN']);
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
            
            // Obtener líneas según el rol del usuario
            $lineasExportar = [];
            if ($rol === 'admin') {
                // Admin: todas las líneas
                $lineasExportar = $todasLineas;
            } else {
                // Dinamizador y usuario: solo líneas de su nodo/accesibles
                $lineasAccesibles = [];
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT DISTINCT l.* FROM lineas l
                    INNER JOIN linea_nodo ln ON l.id = ln.linea_id
                    WHERE ln.nodo_id = :nodo_id AND ln.estado = 1 AND l.estado = 1
                    ORDER BY l.nombre ASC
                ");
                $stmt->execute([':nodo_id' => $nodo_user]);
                $lineasExportar = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
<<<<<<< HEAD
            // Contar materiales por línea
            foreach ($lineasExportar as $linea) {
                $countMateriales = 0;
                foreach ($materiales as $mat) {
                    if ($mat['linea_id'] == $linea['id']) {
                        $countMateriales++;
                    }
                }
                
                $excel->addRow([
                    $linea['nombre'],
                    $linea['descripcion'] ?? '',
                    $linea['estado'] == 1 ? 'Activo' : 'Inactivo',
                    $countMateriales,
                ]);
            }

            // Generar archivo Excel
            $excelContent = $excel->generate();

            // Headers para descarga - Usar XML si hay múltiples sheets
            $extension = ($excel->getSheetCount() > 1) ? 'xml' : 'csv';
            $contentType = ($extension === 'xml') ? 'application/vnd.ms-excel' : 'text/csv; charset=UTF-8';
=======
            // Agregar líneas con ID
            foreach ($lineasExportar as $linea) {
                $excel->addRow([
                    $linea['id'],
                    $linea['nombre'],
                    $linea['descripcion'] ?? '',
                ]);
            }

            // ============================================================
            // SHEET 3: NODOS CON ID
            // ============================================================
            $excel->createSheet('Nodos');
            $excel->setHeaders(['ID', 'NOMBRE', 'DESCRIPCIÓN']);
            
            // Obtener nodos según el rol del usuario
            $nodosExportar = [];
            if ($rol === 'admin') {
                // Admin: todos los nodos
                $nodosExportar = $todosNodos;
            } else {
                // Dinamizador y usuario: solo su nodo
                if ($nodo_user) {
                    $nodoModel = new Nodo();
                    $nodoUsuario = $nodoModel->getById($nodo_user);
                    if ($nodoUsuario) {
                        $nodosExportar[] = $nodoUsuario;
                    }
                }
            }
            
            // Agregar nodos con ID
            foreach ($nodosExportar as $nodo) {
                $excel->addRow([
                    $nodo['id'],
                    $nodo['nombre'],
                    $nodo['descripcion'] ?? '',
                ]);
            }

            // Generar archivo Excel en formato XLSX con PhpSpreadsheet
            $excelContent = $excel->generate();

            // Headers para descarga - XLSX
            $extension = 'xlsx';
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
            $filename = 'materiales_' . date('Y-m-d_H-i-s') . '.' . $extension;
            
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($excelContent));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $excelContent;

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Error al exportar: ' . $e->getMessage()]);
        }
        exit;
    }

    /* =========================================================
<<<<<<< HEAD
=======
       EXPORTAR MATERIALES A CSV
    ========================================================== */
    public function exportarMaterialesCSV()
    {
        $this->requireAuth();

        try {
            $materialModel = new Material();
            
            // Determinar qué materiales puede ver según su rol
            $rol = $_SESSION['user']['rol'] ?? 'usuario';
            $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
            $linea_user = $_SESSION['user']['linea_id'] ?? null;

            $todosLosMateriales = $materialModel->all();
            $materiales = [];

            if ($rol === 'admin') {
                $materiales = $todosLosMateriales;
            } elseif ($rol === 'dinamizador') {
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user) {
                        $materiales[] = $mat;
                    }
                }
            } elseif ($rol === 'usuario') {
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user && $mat['linea_id'] == $linea_user) {
                        $materiales[] = $mat;
                    }
                }
            }

            if (empty($materiales)) {
                echo json_encode(['success' => false, 'message' => 'No hay materiales para exportar']);
                exit;
            }

            // Definir encabezados según rol - SOLO IDs
            if ($rol === 'admin') {
                $encabezados = ['codigo', 'nombre', 'descripcion', 'nodo_id', 'linea_id', 'cantidad'];
            } elseif ($rol === 'dinamizador') {
                $encabezados = ['codigo', 'nombre', 'descripcion', 'linea_id', 'cantidad'];
            } else { // usuario
                $encabezados = ['codigo', 'nombre', 'descripcion', 'cantidad'];
            }

            // Ordenar materiales
            usort($materiales, function($a, $b) {
                return $b['id'] - $a['id'];
            });

            // Crear archivo CSV
            $output = fopen('php://memory', 'r+');
            
            // Escribir BOM para UTF-8
            fwrite($output, "\xEF\xBB\xBF");
            
            // Escribir encabezados
            fputcsv($output, $encabezados, ';');

            // Agregar datos según rol - SOLO IDs
            if ($rol === 'admin') {
                foreach ($materiales as $mat) {
                    fputcsv($output, [
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['nodo_id'] ?? '',
                        $mat['linea_id'] ?? '',
                        $mat['cantidad'],
                    ], ';');
                }
            } elseif ($rol === 'dinamizador') {
                foreach ($materiales as $mat) {
                    fputcsv($output, [
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['linea_id'] ?? '',
                        $mat['cantidad'],
                    ], ';');
                }
            } else { // usuario
                foreach ($materiales as $mat) {
                    fputcsv($output, [
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['cantidad'],
                    ], ';');
                }
            }

            // Obtener contenido
            rewind($output);
            $csvContent = stream_get_contents($output);
            fclose($output);

            // Headers para descarga
            $filename = 'materiales_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($csvContent));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $csvContent;

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Error al exportar: ' . $e->getMessage()]);
        }
        exit;
    }

    /* =========================================================
       EXPORTAR MATERIALES A CSV EN ZIP (3 ARCHIVOS)
    ========================================================== */
    public function exportarMaterialesCSVZip()
    {
        $this->requireAuth();

        try {
            $materialModel = new Material();
            $nodoModel = new Nodo();
            $lineaModel = new Linea();
            
            // Determinar qué materiales puede ver según su rol
            $rol = $_SESSION['user']['rol'] ?? 'usuario';
            $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
            $linea_user = $_SESSION['user']['linea_id'] ?? null;

            $todosLosMateriales = $materialModel->all();
            $materiales = [];

            if ($rol === 'admin') {
                $materiales = $todosLosMateriales;
            } elseif ($rol === 'dinamizador') {
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user) {
                        $materiales[] = $mat;
                    }
                }
            } elseif ($rol === 'usuario') {
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user && $mat['linea_id'] == $linea_user) {
                        $materiales[] = $mat;
                    }
                }
            }

            if (empty($materiales)) {
                echo json_encode(['success' => false, 'message' => 'No hay materiales para exportar']);
                exit;
            }

            // Crear archivo ZIP en memoria
            $zipFile = tempnam(sys_get_temp_dir(), 'materiales_') . '.zip';
            $zip = new ZipArchive();
            
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception('No se pudo crear el archivo ZIP');
            }

            // ===== ARCHIVO 1: MATERIALES =====
            $csvMateriales = fopen('php://memory', 'r+');
            fwrite($csvMateriales, "\xEF\xBB\xBF");
            
            if ($rol === 'admin') {
                fputcsv($csvMateriales, ['codigo', 'nombre', 'descripcion', 'nodo_id', 'linea_id', 'cantidad'], ';');
                foreach ($materiales as $mat) {
                    fputcsv($csvMateriales, [
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['nodo_id'] ?? '',
                        $mat['linea_id'] ?? '',
                        $mat['cantidad'],
                    ], ';');
                }
            } elseif ($rol === 'dinamizador') {
                fputcsv($csvMateriales, ['codigo', 'nombre', 'descripcion', 'linea_id', 'cantidad'], ';');
                foreach ($materiales as $mat) {
                    fputcsv($csvMateriales, [
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['linea_id'] ?? '',
                        $mat['cantidad'],
                    ], ';');
                }
            } else {
                fputcsv($csvMateriales, ['codigo', 'nombre', 'descripcion', 'cantidad'], ';');
                foreach ($materiales as $mat) {
                    fputcsv($csvMateriales, [
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['cantidad'],
                    ], ';');
                }
            }
            
            rewind($csvMateriales);
            $zip->addFromString('materiales_export/materiales.csv', stream_get_contents($csvMateriales));
            fclose($csvMateriales);

            // ===== ARCHIVO 2: LÍNEAS =====
            $csvLineas = fopen('php://memory', 'r+');
            fwrite($csvLineas, "\xEF\xBB\xBF");
            fputcsv($csvLineas, ['id', 'nombre', 'descripcion'], ';');
            
            // Obtener líneas según el rol del usuario
            $todasLineas = $lineaModel->all();
            $lineasExportar = [];
            
            if ($rol === 'admin') {
                $lineasExportar = $todasLineas;
            } else {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    SELECT DISTINCT l.* FROM lineas l
                    INNER JOIN linea_nodo ln ON l.id = ln.linea_id
                    WHERE ln.nodo_id = :nodo_id AND ln.estado = 1 AND l.estado = 1
                    ORDER BY l.nombre ASC
                ");
                $stmt->execute([':nodo_id' => $nodo_user]);
                $lineasExportar = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            foreach ($lineasExportar as $linea) {
                fputcsv($csvLineas, [
                    $linea['id'],
                    $linea['nombre'],
                    $linea['descripcion'] ?? '',
                ], ';');
            }
            
            rewind($csvLineas);
            $zip->addFromString('materiales_export/lineas.csv', stream_get_contents($csvLineas));
            fclose($csvLineas);

            // ===== ARCHIVO 3: NODOS =====
            $csvNodos = fopen('php://memory', 'r+');
            fwrite($csvNodos, "\xEF\xBB\xBF");
            fputcsv($csvNodos, ['id', 'nombre', 'descripcion'], ';');
            
            // Obtener nodos según el rol del usuario
            $todosNodos = $nodoModel->all();
            $nodosExportar = [];
            
            if ($rol === 'admin') {
                $nodosExportar = $todosNodos;
            } else {
                if ($nodo_user) {
                    $nodoUsuario = $nodoModel->getById($nodo_user);
                    if ($nodoUsuario) {
                        $nodosExportar[] = $nodoUsuario;
                    }
                }
            }
            
            foreach ($nodosExportar as $nodo) {
                fputcsv($csvNodos, [
                    $nodo['id'],
                    $nodo['nombre'],
                    $nodo['descripcion'] ?? '',
                ], ';');
            }
            
            rewind($csvNodos);
            $zip->addFromString('materiales_export/nodos.csv', stream_get_contents($csvNodos));
            fclose($csvNodos);

            // Cerrar ZIP
            $zip->close();

            // Leer el archivo ZIP
            $zipContent = file_get_contents($zipFile);
            unlink($zipFile);

            // Headers para descarga
            $filename = 'materiales_completo_' . date('Y-m-d_H-i-s') . '.zip';
            
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($zipContent));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $zipContent;

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Error al exportar: ' . $e->getMessage()]);
        }
        exit;
    }

    /* =========================================================
       EXPORTAR MATERIALES A TXT
    ========================================================== */
    public function exportarMaterialesTXT()
    {
        $this->requireAuth();

        try {
            $materialModel = new Material();
            
            // Determinar qué materiales puede ver según su rol
            $rol = $_SESSION['user']['rol'] ?? 'usuario';
            $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
            $linea_user = $_SESSION['user']['linea_id'] ?? null;

            $todosLosMateriales = $materialModel->all();
            $materiales = [];

            if ($rol === 'admin') {
                $materiales = $todosLosMateriales;
            } elseif ($rol === 'dinamizador') {
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user) {
                        $materiales[] = $mat;
                    }
                }
            } elseif ($rol === 'usuario') {
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user && $mat['linea_id'] == $linea_user) {
                        $materiales[] = $mat;
                    }
                }
            }

            if (empty($materiales)) {
                echo json_encode(['success' => false, 'message' => 'No hay materiales para exportar']);
                exit;
            }

            // Ordenar materiales
            usort($materiales, function($a, $b) {
                return $b['id'] - $a['id'];
            });

            // Crear contenido TXT con tabulaciones
            $txtContent = "\xEF\xBB\xBF"; // BOM UTF-8
            
            // Encabezados según rol
            if ($rol === 'admin') {
                $txtContent .= "CODIGO\tNOMBRE\tDESCRIPCION\tNODO_ID\tLINEA_ID\tCANTIDAD\n";
                foreach ($materiales as $mat) {
                    $txtContent .= sprintf(
                        "%s\t%s\t%s\t%s\t%s\t%s\n",
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['nodo_id'] ?? '',
                        $mat['linea_id'] ?? '',
                        $mat['cantidad']
                    );
                }
            } elseif ($rol === 'dinamizador') {
                $txtContent .= "CODIGO\tNOMBRE\tDESCRIPCION\tLINEA_ID\tCANTIDAD\n";
                foreach ($materiales as $mat) {
                    $txtContent .= sprintf(
                        "%s\t%s\t%s\t%s\t%s\n",
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['linea_id'] ?? '',
                        $mat['cantidad']
                    );
                }
            } else { // usuario
                $txtContent .= "CODIGO\tNOMBRE\tDESCRIPCION\tCANTIDAD\n";
                foreach ($materiales as $mat) {
                    $txtContent .= sprintf(
                        "%s\t%s\t%s\t%s\n",
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['descripcion'] ?? '',
                        $mat['cantidad']
                    );
                }
            }

            // Headers para descarga
            $filename = 'materiales_' . date('Y-m-d_H-i-s') . '.txt';
            
            header('Content-Type: text/plain; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($txtContent));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $txtContent;

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Error al exportar: ' . $e->getMessage()]);
        }
        exit;
    }

    /* =========================================================
>>>>>>> d453d91ce6f42fa6fce17a1a7f1a14e75be1b343
       EXPORTAR MATERIALES A PDF
    ========================================================== */
    public function exportarMaterialesPDF()
    {
        $this->requireAuth();

        try {
            $materialModel = new Material();
            
            // Determinar qué materiales puede ver según su rol
            $rol = $_SESSION['user']['rol'] ?? 'usuario';
            $nodo_user = $_SESSION['user']['nodo_id'] ?? null;
            $linea_user = $_SESSION['user']['linea_id'] ?? null;

            $todosLosMateriales = $materialModel->all();
            $materiales = [];

            if ($rol === 'admin') {
                $materiales = $todosLosMateriales;
            } elseif ($rol === 'dinamizador') {
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user) {
                        $materiales[] = $mat;
                    }
                }
            } elseif ($rol === 'usuario') {
                foreach ($todosLosMateriales as $mat) {
                    if ($mat['nodo_id'] == $nodo_user && $mat['linea_id'] == $linea_user) {
                        $materiales[] = $mat;
                    }
                }
            }

            if (empty($materiales)) {
                echo json_encode(['success' => false, 'message' => 'No hay materiales para exportar']);
                exit;
            }

            // Definir encabezados según rol
            if ($rol === 'admin') {
                $encabezados = ['ID', 'Código', 'Nombre', 'Nodo', 'Línea', 'Cantidad', 'Estado'];
            } elseif ($rol === 'dinamizador') {
                $encabezados = ['ID', 'Código', 'Nombre', 'Línea', 'Cantidad', 'Estado'];
            } else {
                $encabezados = ['ID', 'Código', 'Nombre', 'Cantidad', 'Estado'];
            }

            // Crear PDF
            $pdf = new PdfHelper('Reporte de Materiales');
            $pdf->setHeaders($encabezados);

            // Ordenar materiales
            usort($materiales, function($a, $b) {
                return $b['id'] - $a['id'];
            });

            // Agregar datos según rol
            $datos = [];
            if ($rol === 'admin') {
                foreach ($materiales as $mat) {
                    $datos[] = [
                        $mat['id'],
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['nodo_nombre'] ?? 'Sin nodo',
                        $mat['linea_nombre'] ?? 'Sin línea',
                        $mat['cantidad'],
                        $mat['estado'] == 1 ? 'Activo' : 'Inactivo',
                    ];
                }
            } elseif ($rol === 'dinamizador') {
                foreach ($materiales as $mat) {
                    $datos[] = [
                        $mat['id'],
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['linea_nombre'] ?? 'Sin línea',
                        $mat['cantidad'],
                        $mat['estado'] == 1 ? 'Activo' : 'Inactivo',
                    ];
                }
            } else {
                foreach ($materiales as $mat) {
                    $datos[] = [
                        $mat['id'],
                        $mat['codigo'],
                        $mat['nombre'],
                        $mat['cantidad'],
                        $mat['estado'] == 1 ? 'Activo' : 'Inactivo',
                    ];
                }
            }

            $pdf->setData($datos);

            // Generar y descargar
            $filename = 'materiales_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->output($filename);

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Error al exportar: ' . $e->getMessage()]);
        }
        exit;
    }
}
