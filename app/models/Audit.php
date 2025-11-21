<?php

class Audit extends Model
{
    /**
     * Registrar un cambio en la auditoría
     */
    public function registrarCambio($usuario_id, $tabla, $registro_id, $accion, $detalles = [], $admin_id = null)
    {
        // Validar que usuario_id existe en la tabla usuarios, si no usar NULL
        $usuarioValido = null;
        if ($usuario_id !== null) {
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $usuario_id]);
            if ($stmt->fetch()) {
                $usuarioValido = $usuario_id;
            }
        }

        // Validar que admin_id existe en la tabla usuarios, si no usar NULL
        $adminValido = null;
        if ($admin_id !== null) {
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $admin_id]);
            if ($stmt->fetch()) {
                $adminValido = $admin_id;
            }
        }

        $sql = "INSERT INTO auditoria (usuario_id, tabla, accion, detalles, admin_id) 
                VALUES (:usuario_id, :tabla, :accion, :detalles, :admin_id)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioValido,
            ':tabla' => $tabla,
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
     * Obtener historial completo de cambios
     */
    public function obtenerHistorialCompleto($limit = 100, $offset = 0, $filtro = [])
    {
        $sql = "SELECT 
                    a.id,
                    a.accion,
                    a.detalles,
                    a.fecha_cambio,
                    u.nombre as usuario_modificado,
                    u.id as usuario_id,
                    admin.nombre as admin_nombre
                FROM auditoria a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                LEFT JOIN usuarios admin ON a.admin_id = admin.id
                WHERE a.tabla = 'usuarios'";
        
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
    }

    /**
     * Contar registros de auditoría
     */
    public function contarHistorial($filtro = [])
    {
        $sql = "SELECT COUNT(*) as total FROM auditoria WHERE tabla = 'usuarios'";
        
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
}