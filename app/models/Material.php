<?php

class Material extends Model
{
    /**
     * Obtener todos los materiales con información de línea
     */
    public function all()
    {
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   l.nombre as linea_nombre,
                   n.nombre as nodo_nombre
            FROM materiales m 
            LEFT JOIN lineas l ON m.linea_id = l.id 
            LEFT JOIN nodos n ON m.nodo_id = n.id
            ORDER BY m.fecha_actualizacion DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener material por ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   l.nombre as linea_nombre,
                   n.nombre as nodo_nombre
            FROM materiales m 
            LEFT JOIN lineas l ON m.linea_id = l.id 
            LEFT JOIN nodos n ON m.nodo_id = n.id
            WHERE m.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener materiales por línea
     */
    public function getByLinea($linea_id)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   l.nombre as linea_nombre,
                   n.nombre as nodo_nombre
            FROM materiales m 
            LEFT JOIN lineas l ON m.linea_id = l.id 
            LEFT JOIN nodos n ON m.nodo_id = n.id
            WHERE m.linea_id = :linea_id 
            ORDER BY m.nombre ASC
        ");
        $stmt->execute([':linea_id' => $linea_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar materiales con múltiples filtros
     */
    public function search($busqueda = '', $linea_id = null, $estado = null)
    {
        $sql = "
            SELECT m.*, 
                   l.nombre as linea_nombre,
                   n.nombre as nodo_nombre
            FROM materiales m 
            LEFT JOIN lineas l ON m.linea_id = l.id 
            LEFT JOIN nodos n ON m.nodo_id = n.id
            WHERE 1=1
        ";
        $params = [];

        if ($busqueda !== '') {
            $sql .= " AND (m.nombre LIKE :busqueda 
                      OR m.codigo LIKE :busqueda 
                      OR m.descripcion LIKE :busqueda)";
            $params[':busqueda'] = "%$busqueda%";
        }

        if ($linea_id !== null) {
            $sql .= " AND m.linea_id = :linea_id";
            $params[':linea_id'] = $linea_id;
        }

        if ($estado !== null) {
            $sql .= " AND m.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY m.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nuevo material
     * Retorna el ID del material creado o false si hay error
     */
    public function create($data)
    {
        // Establecer variable de sesión para el trigger
        $userId = $_SESSION['user']['id'] ?? 1;
        $this->db->prepare("SET @usuario_id = :usuario_id")->execute([':usuario_id' => $userId]);
        
        $stmt = $this->db->prepare("
            INSERT INTO materiales 
            (codigo, nombre, descripcion, linea_id, cantidad, estado, nodo_id) 
            VALUES (:codigo, :nombre, :descripcion, :linea_id, :cantidad, :estado, :nodo_id)
        ");

        if ($stmt->execute([
            ':codigo'         => $data['codigo'],
            ':nombre'         => $data['nombre'],
            ':descripcion'    => $data['descripcion'],
            ':linea_id'       => $data['linea_id'],
            ':cantidad'       => intval($data['cantidad'] ?? 0),
            ':estado'         => $data['estado'] ?? 1,
            ':nodo_id'        => intval($data['nodo_id'] ?? 0),
        ])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Actualizar material
     */
    public function update($id, $data)
    {
        // Establecer variable de sesión para el trigger
        $userId = $_SESSION['user']['id'] ?? 1;
        $this->db->prepare("SET @usuario_id = :usuario_id")->execute([':usuario_id' => $userId]);
        
        $stmt = $this->db->prepare("
            UPDATE materiales SET
                codigo = :codigo,
                nombre = :nombre,
                descripcion = :descripcion,
                linea_id = :linea_id,
                nodo_id = :nodo_id,
                cantidad = :cantidad,
                estado = :estado,
                fecha_actualizacion = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id'             => $id,
            ':codigo'         => $data['codigo'],
            ':nombre'         => $data['nombre'],
            ':descripcion'    => $data['descripcion'],
            ':linea_id'       => $data['linea_id'],
            ':nodo_id'        => intval($data['nodo_id'] ?? 0),
            ':cantidad'       => intval($data['cantidad']),
            ':estado'         => $data['estado'],
        ]);
    }

    /**
     * Eliminar material
     */
    public function delete($id, $userId = null)
    {
        // Usar ID del usuario actual o el pasado como parámetro
        if ($userId === null) {
            $userId = $_SESSION['user']['id'] ?? 1;
        }
        
        // Establecer variable de sesión para el trigger
        $this->db->prepare("SET @usuario_id = :usuario_id")->execute([':usuario_id' => $userId]);
        
        $stmt = $this->db->prepare("DELETE FROM materiales WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Registrar movimiento de inventario (entrada/salida)
     */
    public function registrarMovimiento($data)
    {
        // Establecer variable de sesión para el trigger
        $userId = $_SESSION['user']['id'] ?? $data['usuario_id'] ?? 1;
        $this->db->prepare("SET @usuario_id = :usuario_id")->execute([':usuario_id' => $userId]);
        
        $stmt = $this->db->prepare("
            INSERT INTO movimientos_inventario 
            (material_id, usuario_id, tipo_movimiento, cantidad, descripcion, fecha_movimiento) 
            VALUES (:material_id, :usuario_id, :tipo_movimiento, :cantidad, :descripcion, NOW())
        ");

        return $stmt->execute([
            ':material_id'      => $data['material_id'],
            ':usuario_id'       => $data['usuario_id'],
            ':tipo_movimiento'  => $data['tipo_movimiento'], // 'entrada' o 'salida'
            ':cantidad'         => $data['cantidad'],
            ':descripcion'      => $data['descripcion'],
        ]);
    }

    /**
     * Obtener historial de movimientos
     */
    public function getHistorialMovimientos($material_id = null, $filtros = [])
    {
        $sql = "
            SELECT m.*, mat.nombre as material_nombre, mat.linea_id, mat.nodo_id, l.nombre as linea_nombre, n.nombre as nodo_nombre, u.nombre as usuario_nombre, u.foto as usuario_foto
            FROM movimientos_inventario m
            LEFT JOIN materiales mat ON m.material_id = mat.id
            LEFT JOIN lineas l ON mat.linea_id = l.id
            LEFT JOIN nodos n ON mat.nodo_id = n.id
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($material_id !== null) {
            $sql .= " AND m.material_id = :material_id";
            $params[':material_id'] = $material_id;
        }

        if (!empty($filtros['tipo_movimiento'])) {
            $sql .= " AND m.tipo_movimiento = :tipo";
            $params[':tipo'] = $filtros['tipo_movimiento'];
        }

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND m.fecha_movimiento >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'] . ' 00:00:00';
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND m.fecha_movimiento <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'] . ' 23:59:59';
        }

        $sql .= " ORDER BY m.fecha_movimiento DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener movimiento por ID con información completa
     */
    public function getMovimientoById($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                m.*,
                mat.nombre as material_nombre,
                mat.codigo as material_codigo,
                mat.cantidad as cantidad_actual,
                mat.linea_id,
                mat.nodo_id,
                l.nombre as linea_nombre,
                n.nombre as nodo_nombre,
                u.nombre as usuario_nombre,
                u.foto as usuario_foto
            FROM movimientos_inventario m
            LEFT JOIN materiales mat ON m.material_id = mat.id
            LEFT JOIN lineas l ON mat.linea_id = l.id
            LEFT JOIN nodos n ON mat.nodo_id = n.id
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar cantidad en material después de movimiento
     */
    public function actualizarCantidad($id, $cantidad)
    {
        $stmt = $this->db->prepare("
            UPDATE materiales 
            SET cantidad = :cantidad,
                fecha_actualizacion = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id'       => $id,
            ':cantidad' => intval($cantidad),
        ]);
    }

    /**
     * Verificar si código de producto ya existe
     */
    public function codigoExiste($codigo, $exceptoId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM materiales WHERE codigo = :codigo";
        $params = [':codigo' => $codigo];

        if ($exceptoId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $exceptoId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Obtener todas las líneas
     */
    public function getLineas()
    {
        $stmt = $this->db->prepare("SELECT * FROM lineas ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener línea por ID
     */
    public function getLineaById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM lineas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Contar materiales por línea
     */
    public function contarPorLinea()
    {
        $stmt = $this->db->prepare("
            SELECT l.id, l.nombre, COUNT(m.id) as total
            FROM lineas l
            LEFT JOIN materiales m ON l.id = m.linea_id
            GROUP BY l.id, l.nombre
            ORDER BY l.nombre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener eliminaciones de materiales desde la tabla de auditoría
     */
    public function getEliminacionesMateriales($filtros = [])
    {
        $sql = "
            SELECT 
                a.id,
                a.accion,
                a.detalles,
                a.fecha_cambio,
                a.fecha_cambio as fecha_creacion,
                u.nombre as usuario_nombre,
                u.foto as usuario_foto,
                JSON_UNQUOTE(JSON_EXTRACT(a.detalles, '$.nombre')) as material_nombre,
                JSON_UNQUOTE(JSON_EXTRACT(a.detalles, '$.codigo')) as material_codigo,
                CAST(JSON_UNQUOTE(JSON_EXTRACT(a.detalles, '$.id')) AS UNSIGNED) as material_id,
                CAST(JSON_UNQUOTE(JSON_EXTRACT(a.detalles, '$.nodo_id')) AS UNSIGNED) as nodo_id,
                JSON_UNQUOTE(JSON_EXTRACT(a.detalles, '$.nodo_nombre')) as nodo_nombre,
                CAST(JSON_UNQUOTE(JSON_EXTRACT(a.detalles, '$.linea_id')) AS UNSIGNED) as linea_id,
                JSON_UNQUOTE(JSON_EXTRACT(a.detalles, '$.linea_nombre')) as linea_nombre
            FROM auditoria_materiales a
            LEFT JOIN usuarios u ON a.admin_id = u.id
            WHERE a.accion = 'eliminar'
        ";
        $params = [];

        if (!empty($filtros['material_id'])) {
            $sql .= " AND a.material_id = :material_id";
            $params[':material_id'] = (int)$filtros['material_id'];
        }

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND a.fecha_cambio >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'] . ' 00:00:00';
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND a.fecha_cambio <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'] . ' 23:59:59';
        }

        $sql .= " ORDER BY a.fecha_cambio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener cambios (UPDATE) de propiedades del material desde auditoría
     */
    public function getHistorialCambios($material_id = null, $filtros = [])
    {
        $sql = "
            SELECT 
                a.id,
                a.material_id,
                a.accion,
                a.detalles,
                a.fecha_cambio,
                u.nombre as usuario_nombre,
                u.foto as usuario_foto,
                m.nombre as material_nombre,
                m.codigo as material_codigo,
                m.nodo_id,
                m.linea_id,
                n.nombre as nodo_nombre,
                l.nombre as linea_nombre
            FROM auditoria_materiales a
            LEFT JOIN usuarios u ON a.admin_id = u.id
            LEFT JOIN materiales m ON a.material_id = m.id
            LEFT JOIN nodos n ON m.nodo_id = n.id
            LEFT JOIN lineas l ON m.linea_id = l.id
            WHERE a.accion = 'actualizar'
        ";
        $params = [];

        if ($material_id !== null) {
            $sql .= " AND a.material_id = :material_id";
            $params[':material_id'] = (int)$material_id;
        }

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND a.fecha_cambio >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'] . ' 00:00:00';
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND a.fecha_cambio <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'] . ' 23:59:59';
        }

        $sql .= " ORDER BY a.fecha_cambio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
