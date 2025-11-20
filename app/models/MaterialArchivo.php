<?php

class MaterialArchivo extends Model
{
    /**
     * Obtiene todos los archivos subidos por un usuario
     */
    public function getByUsuario($usuarioId)
    {
        $stmt = $this->db->prepare("SELECT * FROM material_archivos WHERE usuario_id = :usuario_id ORDER BY fecha_creacion DESC");
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Obtener todos los archivos de un material
     */
    public function getByMaterial($materialId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM material_archivos
            WHERE material_id = :material_id
            ORDER BY fecha_creacion DESC
        ");
        $stmt->execute([':material_id' => $materialId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener archivo por ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM material_archivos
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nuevo archivo
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO material_archivos 
            (material_id, nombre_original, nombre_archivo, tipo_archivo, tamaño, usuario_id, fecha_creacion)
            VALUES (:material_id, :nombre_original, :nombre_archivo, :tipo_archivo, :tamaño, :usuario_id, NOW())
        ");

        return $stmt->execute([
            ':material_id' => $data['material_id'],
            ':nombre_original' => $data['nombre_original'],
            ':nombre_archivo' => $data['nombre_archivo'],
            ':tipo_archivo' => $data['tipo_archivo'],
            ':tamaño' => $data['tamaño'],
            ':usuario_id' => $data['usuario_id']
        ]);
    }

    /**
     * Eliminar archivo
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM material_archivos
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Contar archivos de un material
     */
    public function countByMaterial($materialId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM material_archivos
            WHERE material_id = :material_id
        ");
        $stmt->execute([':material_id' => $materialId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Contar archivos subidos por un usuario
     */
    public function countByUsuario($usuarioId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM material_archivos
            WHERE usuario_id = :usuario_id
        ");
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
