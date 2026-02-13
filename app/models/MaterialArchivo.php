<?php

class MaterialArchivo extends Model
{
    /**
     * Obtiene todos los archivos subidos por un usuario.
     */
    public function getByUsuario($usuarioId)
    {
        $stmt = $this->db->prepare('SELECT * FROM material_archivos WHERE usuario_id = :usuario_id ORDER BY fecha_creacion DESC');
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todos los archivos de un material con info del usuario.
     */
    public function getByMaterial($materialId)
    {
        $stmt = $this->db->prepare('
            SELECT 
                ma.*,
                u.nombre as usuario_nombre,
                u.correo as usuario_correo
            FROM material_archivos ma
            LEFT JOIN usuarios u ON ma.usuario_id = u.id
            WHERE ma.material_id = :material_id
            ORDER BY ma.fecha_creacion DESC
        ');
        $stmt->execute([':material_id' => $materialId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener archivo por ID.
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare('
            SELECT * FROM material_archivos
            WHERE id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nuevo archivo.
     */
    public function create($data)
    {
        $logFile = __DIR__ . '/../../upload_debug.log';
        $log = function ($msg) use ($logFile) {
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($logFile, "[$timestamp] MaterialArchivo::create() - $msg" . PHP_EOL, FILE_APPEND);
        };

        try {
            $log('INICIANDO');

            // Validar que el usuario_id existe
            $userId = $data['usuario_id'] ?? 1;
            $log("Usuario ID: $userId");

            $stmt = $this->db->prepare('SELECT id FROM usuarios WHERE id = :id');
            $stmt->execute([':id' => $userId]);
            $usuarioExists = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuarioExists) {
                $userId = 1;
                $log('Usuario no existe, usando ID 1');
            }

            // Establecer variable para trigger
            $this->db->prepare('SET @usuario_id = :usuario_id')->execute([':usuario_id' => $userId]);

            $materialId = intval($data['material_id']);
            $nombreOrig = $data['nombre_original'];
            $nombreArch = $data['nombre_archivo'];
            $tipoArch = $data['tipo_archivo'] ?? 'application/octet-stream';
            $tamano = intval($data['tamano'] ?? $data['tamaño'] ?? 0);

            $log("SQL: INSERT material_id=$materialId, usuario_id=$userId, archivo=$nombreOrig");

            $stmt = $this->db->prepare('
                INSERT INTO material_archivos 
                (material_id, nombre_original, nombre_archivo, tipo_archivo, tamano, usuario_id)
                VALUES (:material_id, :nombre_original, :nombre_archivo, :tipo_archivo, :tamano, :usuario_id)
            ');

            $result = $stmt->execute([
                ':material_id' => $materialId,
                ':nombre_original' => $nombreOrig,
                ':nombre_archivo' => $nombreArch,
                ':tipo_archivo' => $tipoArch,
                ':tamano' => $tamano,
                ':usuario_id' => $userId,
            ]);

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                $log('ERROR SQL: ' . json_encode($errorInfo));

                return false;
            }

             $insertId = (int)$this->db->lastInsertId();
             $log("✅ ÉXITO: Insertado con ID $insertId");

             return $insertId;
         } catch (Exception $e) {
             $log('EXCEPTION: ' . $e->getMessage());

             return false;
         }
    }

    /**
     * Eliminar archivo.
     */
    public function delete($id)
    {
        // Establecer variable de sesión para el trigger
        $userId = $_SESSION['user']['id'] ?? 1;
        $this->db->prepare('SET @usuario_id = :usuario_id')->execute([':usuario_id' => $userId]);

        $stmt = $this->db->prepare('
            DELETE FROM material_archivos
            WHERE id = :id
        ');

        return $stmt->execute([':id' => $id]);
    }

    /**
     * Contar archivos de un material.
     */
    public function countByMaterial($materialId)
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) as total FROM material_archivos
            WHERE material_id = :material_id
        ');
        $stmt->execute([':material_id' => $materialId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    }

    /**
     * Contar archivos subidos por un usuario.
     */
    public function countByUsuario($usuarioId)
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) as total FROM material_archivos
            WHERE usuario_id = :usuario_id
        ');
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['total'] ?? 0;
    }

    /**
     * Buscar archivo por ID.
     * Alias de getById() para consistencia con MaterialFileService.
     */
    public function findById($id)
    {
        return $this->getById($id);
    }

    /**
     * Buscar archivos por ID de material.
     * Alias de getByMaterial() para consistencia con MaterialFileService.
     */
    public function findByMaterialId($materialId)
    {
        return $this->getByMaterial($materialId);
    }

    /**
     * Contar archivos por ID de material.
     * Alias de countByMaterial() para consistencia con MaterialFileService.
     */
    public function countByMaterialId($materialId)
    {
        return $this->countByMaterial($materialId);
    }
}
