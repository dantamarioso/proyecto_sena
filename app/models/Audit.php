<?php

class Audit extends Model
{
    /**
     * Registrar un cambio en la auditoría
     * Usa tabla separada según el tipo (usuarios o materiales)
     */
    public function registrarCambio($usuario_id, $tabla, $registro_id, $accion, $detalles = [], $admin_id = null)
    {
        // Validar parámetros obligatorios
        if (empty($accion)) {
            $accion = 'actualizar'; // Default a 'actualizar' en lugar de 'sin_accion'
        }

        // Determinar tabla de auditoría según tipo
        if ($tabla === 'usuarios') {
            $tabla_auditoria = 'auditoria_usuarios';
            $campo_id = 'usuario_id';
            $valor_id = $usuario_id ?? $registro_id; // Usar cualquiera que sea válido
        } elseif ($tabla === 'materiales') {
            $tabla_auditoria = 'auditoria_materiales';
            $campo_id = 'material_id';
            $valor_id = $registro_id ?? $usuario_id; // Usar cualquiera que sea válido
        } else {
            return false; // Tabla no soportada
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

        // Si no hay ID válido, no registrar (evitar registros incompletos)
        if ($id_valido === null) {
            return false;
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

        // Obtener IP del usuario
        $ipAddress = $this->getClientIP();

        // Insertar en la tabla correcta
        $sql = "INSERT INTO $tabla_auditoria ($campo_id, accion, detalles, admin_id, ip_address) 
                VALUES (:id_valor, :accion, :detalles, :admin_id, :ip_address)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_valor' => $id_valido,
            ':accion' => $accion,
            ':detalles' => json_encode($detalles),
            ':admin_id' => $adminValido,
            ':ip_address' => $ipAddress
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Obtener IP del cliente
     */
    private function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        }
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
                    admin.nombre as admin_nombre,
                    admin.foto as admin_foto
                FROM auditoria_usuarios a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                LEFT JOIN usuarios admin ON a.admin_id = admin.id
                WHERE a.usuario_id = :usuario_id
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
            $sql = "SELECT 
                        a.id,
                        'usuarios' as tabla,
                        a.accion,
                        a.detalles,
                        a.fecha_cambio,
                        u.nombre as usuario_modificado,
                        u.id as usuario_id,
                        admin.nombre as admin_nombre,
                        admin.foto as admin_foto
                    FROM auditoria_usuarios a
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
            return [];
        }
    }

    /**
     * Contar registros de auditoría de USUARIOS
     */
    public function contarHistorial($filtro = [])
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM auditoria_usuarios WHERE 1=1";
            
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
            return 0;
        }
    }

    /**
     * Obtener usuarios que fueron eliminados
     */
    public function obtenerUsuariosEliminados()
    {
        $sql = "SELECT DISTINCT 
                    a.usuario_id as id,
                    a.detalles
                FROM auditoria_usuarios a
                WHERE a.accion = 'DELETE'
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
     * Obtener historial de cambios de MATERIALES
     */
    public function obtenerHistorialMateriales($limit = 100, $offset = 0, $filtro = [])
    {
        try {
            $sql = "SELECT 
                        a.id,
                        'materiales' as tabla,
                        a.material_id,
                        a.accion,
                        a.detalles,
                        a.fecha_cambio,
                        m.nombre as material_nombre,
                        m.codigo as material_codigo,
                        admin.nombre as admin_nombre,
                        admin.foto as admin_foto
                    FROM auditoria_materiales a
                    LEFT JOIN materiales m ON a.material_id = m.id
                    LEFT JOIN usuarios admin ON a.admin_id = admin.id
                    WHERE 1=1";
        
            $params = [];
        
            if (!empty($filtro['material_id'])) {
                $sql .= " AND a.material_id = :material_id";
                $params[':material_id'] = $filtro['material_id'];
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
        
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Contar registros de auditoría de MATERIALES
     */
    public function contarHistorialMateriales($filtro = [])
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM auditoria_materiales WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtro['material_id'])) {
                $sql .= " AND material_id = :material_id";
                $params[':material_id'] = $filtro['material_id'];
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
            return 0;
        }
    }

    /**
     * Obtener todas las acciones disponibles en la auditoría
     */
    public function obtenerAccionesDisponibles()
    {
        $sql = "SELECT DISTINCT accion FROM auditoria_usuarios 
                UNION 
                SELECT DISTINCT accion FROM auditoria_materiales 
                ORDER BY accion";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $acciones = [];
        foreach ($results as $row) {
            $acciones[] = $row['accion'];
        }
        return $acciones;
    }
}
