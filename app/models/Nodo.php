<?php

class Nodo extends Model
{
    /**
     * Obtener todos los nodos.
     */
    public function all()
    {
        $stmt = $this->db->prepare('SELECT * FROM nodos ORDER BY nombre ASC');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener nodo por ID.
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM nodos WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener nodos activos.
     */
    public function getActivos()
    {
        $stmt = $this->db->prepare('SELECT * FROM nodos WHERE estado = 1 ORDER BY nombre ASC');
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener nodos activos con sus líneas.
     */
    public function getActivosConLineas()
    {
        $stmt = $this->db->prepare('SELECT * FROM nodos WHERE estado = 1 ORDER BY nombre ASC');
        $stmt->execute();
        $nodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agregar líneas a cada nodo usando tabla linea_nodo
        foreach ($nodos as &$nodo) {
            $stmtLineas = $this->db->prepare('
                SELECT DISTINCT l.* 
                FROM lineas l
                INNER JOIN linea_nodo ln ON l.id = ln.linea_id
                WHERE ln.nodo_id = :nodo_id AND ln.estado = 1 AND l.estado = 1
                ORDER BY l.nombre ASC
            ');
            $stmtLineas->execute([':nodo_id' => $nodo['id']]);
            $nodo['lineas'] = $stmtLineas->fetchAll(PDO::FETCH_ASSOC);
        }

        return $nodos;
    }

    /**
     * Crear nuevo nodo.
     */
    public function create($data)
    {
        $stmt = $this->db->prepare('
            INSERT INTO nodos (nombre, ciudad, descripcion, estado)
            VALUES (:nombre, :ciudad, :descripcion, :estado)
        ');

        $success = $stmt->execute([
            ':nombre' => $data['nombre'],
            ':ciudad' => $data['ciudad'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':estado' => $data['estado'] ?? 1,
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Actualizar nodo.
     */
    public function update($id, $data)
    {
        $stmt = $this->db->prepare('
            UPDATE nodos SET
                nombre = :nombre,
                ciudad = :ciudad,
                descripcion = :descripcion,
                estado = :estado
            WHERE id = :id
        ');

        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':ciudad' => $data['ciudad'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':estado' => $data['estado'] ?? 1,
        ]);
    }

    /**
     * Obtener líneas de un nodo.
     */
    public function getLineas($nodo_id)
    {
        $stmt = $this->db->prepare('
            SELECT DISTINCT l.* FROM lineas l
            INNER JOIN linea_nodo ln ON l.id = ln.linea_id
            WHERE ln.nodo_id = :nodo_id AND ln.estado = 1 AND l.estado = 1
            ORDER BY l.nombre ASC
        ');
        $stmt->execute([':nodo_id' => $nodo_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener cantidad de líneas por nodo.
     */
    public function contarLineas($nodo_id)
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(DISTINCT l.id) as total FROM lineas l
            INNER JOIN linea_nodo ln ON l.id = ln.linea_id
            WHERE ln.nodo_id = :nodo_id AND ln.estado = 1 AND l.estado = 1
        ');
        $stmt->execute([':nodo_id' => $nodo_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    }

    /**
     * Obtener cantidad de usuarios por nodo.
     */
    public function contarUsuarios($nodo_id, $rol = null)
    {
        $sql = 'SELECT COUNT(*) as total FROM usuarios WHERE nodo_id = :nodo_id AND estado = 1';
        $params = [':nodo_id' => $nodo_id];

        if ($rol !== null) {
            $sql .= ' AND rol = :rol';
            $params[':rol'] = $rol;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    }

    /**
     * Obtener cantidad de materiales por nodo.
     */
    public function contarMateriales($nodo_id)
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) as total FROM materiales 
            WHERE nodo_id = :nodo_id AND estado = 1
        ');
        $stmt->execute([':nodo_id' => $nodo_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    }

    /**
     * Obtener información estadística de un nodo.
     */
    public function getEstadisticas($nodo_id)
    {
        return [
            'lineas' => $this->contarLineas($nodo_id),
            'usuarios_total' => $this->contarUsuarios($nodo_id),
            'usuarios_admin' => $this->contarUsuarios($nodo_id, 'admin'),
            'usuarios_dinamizador' => $this->contarUsuarios($nodo_id, 'dinamizador'),
            'usuarios_usuario' => $this->contarUsuarios($nodo_id, 'usuario'),
            'materiales' => $this->contarMateriales($nodo_id),
        ];
    }

    /**
     * Buscar nodos.
     */
    public function search($busqueda = '')
    {
        $stmt = $this->db->prepare('
            SELECT * FROM nodos 
            WHERE nombre LIKE :busqueda 
            OR ciudad LIKE :busqueda 
            OR descripcion LIKE :busqueda
            ORDER BY nombre ASC
        ');

        $stmt->execute([':busqueda' => "%$busqueda%"]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener nodos con información completa.
     */
    public function getAllComplete()
    {
        $stmt = $this->db->prepare('
            SELECT 
                n.*,
                COUNT(DISTINCT l.id) as cantidad_lineas,
                COUNT(DISTINCT u.id) as cantidad_usuarios,
                COUNT(DISTINCT m.id) as cantidad_materiales
            FROM nodos n
            LEFT JOIN linea_nodo ln ON ln.nodo_id = n.id AND ln.estado = 1
            LEFT JOIN lineas l ON l.id = ln.linea_id AND l.estado = 1
            LEFT JOIN usuarios u ON u.nodo_id = n.id AND u.estado = 1
            LEFT JOIN materiales m ON m.nodo_id = n.id AND m.estado = 1
            GROUP BY n.id
            ORDER BY n.nombre ASC
        ');

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
