<?php

class AuditController extends Controller
{
    private function requireAdmin()
    {
        if (!isset($_SESSION['user']) || ($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
            http_response_code(403);
            echo "Acceso denegado. Solo administradores pueden acceder al historial.";
            exit;
        }
    }

    /**
     * Ver historial de cambios
     */
    public function historial()
    {
        $this->requireAdmin();

        $auditModel = new Audit();
        $userModel = new User();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Filtros
        $filtro = [
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'accion' => $_GET['accion'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? ''
        ];

        try {
            $cambios = $auditModel->obtenerHistorialCompleto($perPage, $offset, $filtro);
            $total = $auditModel->contarHistorial($filtro);
            $totalPages = max(1, ceil($total / $perPage));
        } catch (Exception $e) {
            error_log("Error en historial: " . $e->getMessage());
            $cambios = [];
            $total = 0;
            $totalPages = 1;
        }

        // Obtener lista de todos los usuarios para el filtro (incluyendo el propio)
        $usuarios = $userModel->all();
        
        // Obtener también usuarios que aparecen en auditoría pero fueron eliminados
        $usuariosEliminados = $auditModel->obtenerUsuariosEliminados();
        $usuarios = array_merge($usuarios, $usuariosEliminados);
        
        // Eliminar duplicados por ID y ordenar por nombre
        $usuariosUnicos = [];
        foreach ($usuarios as $u) {
            $usuariosUnicos[$u['id']] = $u;
        }
        $usuarios = array_values($usuariosUnicos);
        usort($usuarios, function($a, $b) {
            return strcmp($a['nombre'], $b['nombre']);
        });

        // Obtener todas las acciones disponibles en la BD (con fallback directo)
        $accionesDisponibles = [
            'actualizar',
            'actualizar_estado',
            'actualizar_rol',
            'asignar_nodo',
            'crear',
            'desactivar',
            'desactivar/activar',
            'eliminar',
            'ver'
        ];
        
        // Intentar obtener de la BD si es posible
        try {
            $acciones_bd = $auditModel->obtenerAccionesDisponibles();
            if (!empty($acciones_bd)) {
                $accionesDisponibles = $acciones_bd;
            }
        } catch (Exception $e) {
            // Usar fallback
        }
        
        sort($accionesDisponibles);

        $this->view('audit/historial', [
            'cambios' => $cambios,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'usuarios' => $usuarios,
            'filtro' => $filtro,
            'accionesDisponibles' => $accionesDisponibles,
            'pageStyles' => ['audit', 'audit_mejorado'],
            'pageScripts' => ['audit', 'historial_mejorado']
        ]);
    }

    /**
     * API: Obtener historial en JSON
     */
    public function buscar()
    {
        $this->requireAdmin();

        header('Content-Type: application/json; charset=utf-8');

        $auditModel = new Audit();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $filtro = [
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'accion' => $_GET['accion'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? ''
        ];

        $cambios = $auditModel->obtenerHistorialCompleto($perPage, $offset, $filtro);
        $total = $auditModel->contarHistorial($filtro);

        echo json_encode([
            'data' => $cambios,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => max(1, ceil($total / $perPage))
        ]);
        exit;
    }
}
