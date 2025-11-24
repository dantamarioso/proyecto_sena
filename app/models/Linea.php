<?php

class Linea extends Model
{
    /**
     * Obtener todas las líneas
     */
    public function all()
    {
        $stmt = $this->db->prepare("SELECT * FROM lineas ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener línea por ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM lineas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener línea con sus nodos asociados
     */
    public function getByIdConNodos($id)
    {
        $linea = $this->getById($id);
        if (!$linea) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT n.* FROM nodos n
            JOIN linea_nodo ln ON n.id = ln.nodo_id
            WHERE ln.linea_id = :linea_id AND ln.estado = 1
            ORDER BY n.nombre ASC
        ");
        $stmt->execute([':linea_id' => $id]);
        $linea['nodos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $linea;
    }

    /**
     * Obtener líneas por nodo
     */
    public function getByNodo($nodo_id)
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT l.* FROM lineas l
            JOIN linea_nodo ln ON l.id = ln.linea_id
            WHERE ln.nodo_id = :nodo_id AND ln.estado = 1 AND l.estado = 1
            ORDER BY l.nombre ASC
        ");
        $stmt->execute([':nodo_id' => $nodo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener líneas activas por nodo
     */
    public function getActivosByNodo($nodo_id)
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT l.* FROM lineas l
            JOIN linea_nodo ln ON l.id = ln.linea_id
            WHERE ln.nodo_id = :nodo_id AND ln.estado = 1 AND l.estado = 1
            ORDER BY l.nombre ASC
        ");
        $stmt->execute([':nodo_id' => $nodo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener nodos de una línea
     */
    public function getNodos($linea_id)
    {
        $stmt = $this->db->prepare("
            SELECT n.* FROM nodos n
            JOIN linea_nodo ln ON n.id = ln.nodo_id
            WHERE ln.linea_id = :linea_id AND ln.estado = 1
            ORDER BY n.nombre ASC
        ");
        $stmt->execute([':linea_id' => $linea_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener IDs de nodos para una línea
     */
    public function getNodoIds($linea_id)
    {
        $stmt = $this->db->prepare("
            SELECT nodo_id FROM linea_nodo
            WHERE linea_id = :linea_id AND estado = 1
            ORDER BY nodo_id ASC
        ");
        $stmt->execute([':linea_id' => $linea_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($result, 'nodo_id');
    }

    /**
     * Verificar si una línea existe en un nodo específico
     */
    public function existeEnNodo($linea_id, $nodo_id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM linea_nodo
            WHERE linea_id = :linea_id AND nodo_id = :nodo_id
        ");
        $stmt->execute([':linea_id' => $linea_id, ':nodo_id' => $nodo_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Verificar si el nombre de línea es único (independiente de nodos)
     */
    public function nombreExiste($nombre, $exceptoId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM lineas WHERE nombre = :nombre";
        $params = [':nombre' => $nombre];

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
     * Crear nueva línea
     */
    public function create($data)
    {
        // Validar que el nombre sea único
        if ($this->nombreExiste($data['nombre'])) {
            throw new Exception("Ya existe una línea con este nombre.");
        }

        $stmt = $this->db->prepare("
            INSERT INTO lineas (nombre, descripcion, estado)
            VALUES (:nombre, :descripcion, :estado)
        ");
        
        $success = $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':estado' => $data['estado'] ?? 1,
        ]);
        
        if ($success) {
            $lineaId = $this->db->lastInsertId();
            
            // Asignar a nodos si se proporcionan (soporta 'nodos' y 'nodo_ids')
            $nodosArray = $data['nodos'] ?? $data['nodo_ids'] ?? [];
            if (!empty($nodosArray) && is_array($nodosArray)) {
                $this->asignarNodos($lineaId, $nodosArray);
            }
            
            return $lineaId;
        }
        
        return false;
    }

    /**
     * Actualizar línea
     */
    public function update($id, $data)
    {
        // Validar que el nombre sea único
        if ($this->nombreExiste($data['nombre'], $id)) {
            throw new Exception("Ya existe una línea con este nombre.");
        }

        $stmt = $this->db->prepare("
            UPDATE lineas SET
                nombre = :nombre,
                descripcion = :descripcion,
                estado = :estado
            WHERE id = :id
        ");
        
        $success = $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':estado' => $data['estado'] ?? 1,
        ]);

        // Actualizar asignación de nodos si se proporcionan
        if ($success && isset($data['nodos']) && is_array($data['nodos'])) {
            $this->asignarNodos($id, $data['nodos']);
        }
        
        return $success;
    }

    /**
     * Asignar una línea a un nodo (una sola asignación)
     */
    public function asignarNodo($linea_id, $nodo_id)
    {
        // Verificar si ya existe la relación
        if ($this->existeEnNodo($linea_id, $nodo_id)) {
            return true; // Ya existe, no hay error
        }

        $stmt = $this->db->prepare("
            INSERT INTO linea_nodo (linea_id, nodo_id, estado)
            VALUES (:linea_id, :nodo_id, 1)
        ");
        
        return $stmt->execute([
            ':linea_id' => $linea_id,
            ':nodo_id' => $nodo_id,
        ]);
    }

    /**
     * Asignar una línea a varios nodos (reemplaza asignaciones previas)
     */
    public function asignarNodos($linea_id, $nodo_ids)
    {
        // Asegurar que es un array
        if (!is_array($nodo_ids)) {
            $nodo_ids = [$nodo_ids];
        }

        // Eliminar asignaciones previas
        $stmt = $this->db->prepare("DELETE FROM linea_nodo WHERE linea_id = :linea_id");
        $stmt->execute([':linea_id' => $linea_id]);

        // Insertar nuevas asignaciones
        $stmtInsert = $this->db->prepare("
            INSERT INTO linea_nodo (linea_id, nodo_id, estado)
            VALUES (:linea_id, :nodo_id, 1)
        ");

        foreach ($nodo_ids as $nodo_id) {
            $stmtInsert->execute([
                ':linea_id' => $linea_id,
                ':nodo_id' => intval($nodo_id),
            ]);
        }

        return true;
    }

    /**
     * Desasignar una línea de un nodo
     */
    public function desasignarNodo($linea_id, $nodo_id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM linea_nodo 
            WHERE linea_id = :linea_id AND nodo_id = :nodo_id
        ");
        
        return $stmt->execute([
            ':linea_id' => $linea_id,
            ':nodo_id' => $nodo_id,
        ]);
    }

    /**
     * Eliminar línea
     */
    public function delete($id)
    {
        // Las asignaciones se eliminarán automáticamente por CASCADE
        $stmt = $this->db->prepare("DELETE FROM lineas WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Obtener líneas activas
     */
    public function getActivos()
    {
        $stmt = $this->db->prepare("SELECT * FROM lineas WHERE estado = 1 ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar líneas
     */
    public function search($busqueda = '')
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT l.*,
                GROUP_CONCAT(n.nombre SEPARATOR ', ') as nodos
            FROM lineas l
            LEFT JOIN linea_nodo ln ON l.id = ln.linea_id
            LEFT JOIN nodos n ON ln.nodo_id = n.id AND ln.estado = 1
            WHERE l.nombre LIKE :busqueda 
            OR l.descripcion LIKE :busqueda 
            OR n.nombre LIKE :busqueda
            GROUP BY l.id
            ORDER BY l.nombre ASC
        ");
        
        $stmt->execute([':busqueda' => "%$busqueda%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar líneas por nodo
     */
    public function contarPorNodo($nodo_id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT l.id) as total FROM lineas l
            JOIN linea_nodo ln ON l.id = ln.linea_id
            WHERE ln.nodo_id = :nodo_id AND ln.estado = 1 AND l.estado = 1
        ");
        $stmt->execute([':nodo_id' => $nodo_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Obtener todas las líneas con información de sus nodos
     */
    public function getAllConNodos()
    {
        $stmt = $this->db->prepare("
            SELECT l.id, l.nombre, l.descripcion, l.estado,
                GROUP_CONCAT(n.nombre SEPARATOR ', ') as nodos,
                COUNT(DISTINCT n.id) as cantidad_nodos
            FROM lineas l
            LEFT JOIN linea_nodo ln ON l.id = ln.linea_id AND ln.estado = 1
            LEFT JOIN nodos n ON ln.nodo_id = n.id
            GROUP BY l.id
            ORDER BY l.nombre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

