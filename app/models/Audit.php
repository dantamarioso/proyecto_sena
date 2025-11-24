<?php

class Audit extends Model
{
    /**
     * Registrar un cambio en la auditoría
     * Usa tabla separada según el tipo (usuarios o materiales)
     */
    public function registrarCambio($usuario_id, $tabla, $registro_id, $accion, $detalles = [], $admin_id = null)
    {
        // Mapear tabla a tipo de auditoría
        $tabla_auditoria = 'auditoria'; // default
        $campo_id = 'usuario_id';
        $valor_id = $usuario_id;

        if ($tabla === 'usuarios') {
            $tabla_auditoria = 'auditoria_usuarios';
            $campo_id = 'usuario_id';
            $valor_id = $usuario_id;
        } elseif ($tabla === 'materiales') {
            $tabla_auditoria = 'auditoria_materiales';
            $campo_id = 'material_id';
            $valor_id = $registro_id;
        }

        // Validar que el ID existe en la tabla correspondiente
        $id_valido = null;
        if ($valor_id !== null) {
            $stmt = $this->db->prepare("SELECT id FROM $tabla WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $valor_id]);
            if ($stmt->fetch()) {
                $id_valido = $valor_id;
            }
        }

        // Validar que admin_id existe
        $adminValido = null;
        if ($admin_id !== null) {
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $admin_id]);
            if ($stmt->fetch()) {
                $adminValido = $admin_id;
            }
        }

        // Insertar en la tabla correcta
        $sql = "INSERT INTO $tabla_auditoria ($campo_id, accion, detalles, admin_id) 
                VALUES (:id_valor, :accion, :detalles, :admin_id)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_valor' => $id_valido,
            ':accion' => $accion,
            ':detalles' => json_encode($detalles),
            ':admin_id' => $adminValido
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Obtener historial de cambios de un usuario
     */
    public function obtenerHistorialUsuario($usuario_id, $limit = 50, $offset = 0)
    {
        $sql = "SELECT 
                    a.id,
                    a.accion,
                    a.detalles,
                    a.fecha_cambio,
                    u.nombre as usuario_modificado,
                    admin.nombre as admin_nombre
                FROM auditoria a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                LEFT JOIN usuarios admin ON a.admin_id = admin.id
                WHERE a.usuario_id = :usuario_id AND a.tabla = 'usuarios'
                ORDER BY a.fecha_cambio DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener historial completo de cambios de USUARIOS
     */
    public function obtenerHistorialCompleto($limit = 100, $offset = 0, $filtro = [])
    {
        try {
            // Primero intentar con auditoria_usuarios, si no existe, usar auditoria
            $tablesExist = $this->db->query("SHOW TABLES LIKE 'auditoria_usuarios'")->rowCount() > 0;
            $tabla = $tablesExist ? 'auditoria_usuarios' : 'auditoria';
            
            $sql = "SELECT 
                        a.id,
                        'usuarios' as tabla,
                        a.accion,
                        a.detalles,
                        a.fecha_cambio,
                        u.nombre as usuario_modificado,
                        u.id as usuario_id,
                        admin.nombre as admin_nombre
                    FROM $tabla a
                    LEFT JOIN usuarios u ON a.usuario_id = u.id
                    LEFT JOIN usuarios admin ON a.admin_id = admin.id
                    WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtro['usuario_id'])) {
            $sql .= " AND a.usuario_id = :usuario_id";
            $params[':usuario_id'] = $filtro['usuario_id'];
        }
        
        if (!empty($filtro['accion'])) {
            $sql .= " AND a.accion = :accion";
            $params[':accion'] = $filtro['accion'];
        }
        
        if (!empty($filtro['fecha_inicio'])) {
            $sql .= " AND DATE(a.fecha_cambio) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtro['fecha_inicio'];
        }
        
        if (!empty($filtro['fecha_fin'])) {
            $sql .= " AND DATE(a.fecha_cambio) <= :fecha_fin";
            $params[':fecha_fin'] = $filtro['fecha_fin'];
        }
        
        $sql .= " ORDER BY a.fecha_cambio DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar resultados para mostrar nombre de usuario eliminado
        foreach ($resultados as &$cambio) {
            // Si el usuario fue eliminado, extraer nombre de los detalles
            if (empty($cambio['usuario_modificado'])) {
                $detalles = json_decode($cambio['detalles'], true) ?? [];
                if (!empty($detalles['nombre']['anterior'])) {
                    $cambio['usuario_modificado'] = $detalles['nombre']['anterior'] . ' (Eliminado)';
                }
            }
        }
        
            return $resultados;
        } catch (Exception $e) {
            // Si hay error, retornar array vacío
            error_log("Error en obtenerHistorialCompleto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar registros de auditoría de USUARIOS
     */
    public function contarHistorial($filtro = [])
    {
        try {
            // Primero intentar con auditoria_usuarios, si no existe, usar auditoria
            $tablesExist = $this->db->query("SHOW TABLES LIKE 'auditoria_usuarios'")->rowCount() > 0;
            $tabla = $tablesExist ? 'auditoria_usuarios' : 'auditoria';
            
            $sql = "SELECT COUNT(*) as total FROM $tabla WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtro['usuario_id'])) {
                $sql .= " AND usuario_id = :usuario_id";
                $params[':usuario_id'] = $filtro['usuario_id'];
            }
            
            if (!empty($filtro['accion'])) {
                $sql .= " AND accion = :accion";
                $params[':accion'] = $filtro['accion'];
            }
            
            if (!empty($filtro['fecha_inicio'])) {
                $sql .= " AND DATE(fecha_cambio) >= :fecha_inicio";
                $params[':fecha_inicio'] = $filtro['fecha_inicio'];
            }
            
            if (!empty($filtro['fecha_fin'])) {
                $sql .= " AND DATE(fecha_cambio) <= :fecha_fin";
                $params[':fecha_fin'] = $filtro['fecha_fin'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error en contarHistorial: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener usuarios que fueron eliminados pero aparecen en auditoría
     */
    public function obtenerUsuariosEliminados()
    {
        $sql = "SELECT DISTINCT 
                    a.usuario_id as id,
                    a.detalles
                FROM auditoria a
                WHERE a.tabla = 'usuarios'
                AND a.accion = 'DELETE'
                AND a.usuario_id NOT IN (SELECT id FROM usuarios)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar en PHP para extraer nombres de los detalles
        $usuarios = [];
        foreach ($results as $row) {
            $detalles = json_decode($row['detalles'], true) ?? [];
            $nombre = $detalles['nombre'] ?? 'Usuario Eliminado';
            
            $usuarios[] = [
                'id' => $row['id'],
                'nombre' => $nombre . ' (Eliminado)'
            ];
        }
        
        return $usuarios;
    }

    /**
     * Obtener todas las acciones disponibles en auditoría
     */
    public function obtenerAccionesDisponibles()
    {
        try {
            // Verificar qué tabla existe
            $tablesExist = $this->db->query("SHOW TABLES LIKE 'auditoria_usuarios'")->rowCount() > 0;
            $tabla = $tablesExist ? 'auditoria_usuarios' : 'auditoria';
            
            $sql = "SELECT DISTINCT accion FROM $tabla WHERE accion IS NOT NULL ORDER BY accion";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $acciones = array_map(function($row) { return $row['accion']; }, $results);
            
            return !empty($acciones) ? $acciones : $this->getAccionesDefault();
        } catch (Exception $e) {
            // Si hay error, retornar acciones por defecto
            return $this->getAccionesDefault();
        }
    }

    /**
     * Obtener acciones estándar como fallback
     */
    private function getAccionesDefault()
    {
        return [
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
    }
}