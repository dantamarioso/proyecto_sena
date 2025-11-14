<?php

class User extends Model
{

    public function allExceptId($id)
    {
        $stmt = $this->db->prepare("
        SELECT * 
        FROM usuarios 
        WHERE id <> :id
        ORDER BY id DESC
    ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function updateFull($id, $data)
    {
        $sql = "
        UPDATE usuarios SET
            nombre = :nombre,
            correo = :correo,
            nombre_usuario = :nombre_usuario,
            celular = :celular,
            cargo = :cargo,
            estado = :estado
    ";

        // Si hay nueva foto
        if (!empty($data['foto'])) {
            $sql .= ", foto = :foto";
        }

        // Si hay nueva contraseña
        if (!empty($data['password'])) {
            $sql .= ", password = :password";
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        // Parámetros obligatorios
        $params = [
            ':nombre'         => $data['nombre'],
            ':correo'         => $data['correo'],
            ':nombre_usuario' => $data['nombre_usuario'],
            ':celular'        => $data['celular'],
            ':cargo'          => $data['cargo'],
            ':estado'         => $data['estado'],
            ':id'             => $id
        ];

        if (!empty($data['foto'])) {
            $params[':foto'] = $data['foto'];
        }

        if (!empty($data['password'])) {
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $stmt->execute($params);
    }


    /* =========================================
       LISTAR TODOS LOS USUARIOS
    ========================================= */
    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM usuarios ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================================
       BUSCAR POR ID
    ========================================= */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================================
       ACTUALIZAR DATOS BÁSICOS (EDITAR)
       - usado en UsuariosController->editar()
    ========================================= */
    public function updateBasic($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios SET
                nombre         = :nombre,
                correo         = :correo,
                nombre_usuario = :nombre_usuario,
                estado         = :estado
            WHERE id = :id
        ");

        return $stmt->execute([
            ':nombre'         => $data['nombre'],
            ':correo'         => $data['correo'],
            ':nombre_usuario' => $data['nombre_usuario'],
            ':estado'         => $data['estado'],
            ':id'             => $id
        ]);
    }

    /* =========================================
       (OPCIONAL) Mantener updateById si ya lo usas
       Si NO lo usas en ninguna parte, puedes borrarlo.
    ========================================= */
    public function updateById($id, $data)
    {
        return $this->updateBasic($id, $data);
    }

    /* =========================================
       CAMBIAR SOLO EL ESTADO (bloquear / desbloquear)
       - usado en UsuariosController->bloquear/desbloquear()
    ========================================= */
    public function updateEstado($id, $estado)
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios 
            SET estado = :estado
            WHERE id = :id
        ");

        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }

    /* =========================================
       ELIMINAR USUARIO POR ID
       - usado en UsuariosController->eliminar()
    ========================================= */
    public function deleteById($id)
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /* =========================================
       LOGIN: buscar por correo o usuario
    ========================================= */
    public function findByCorreoOrUsername($login)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM usuarios 
            WHERE correo = :login OR nombre_usuario = :login
            LIMIT 1
        ");
        $stmt->execute([':login' => $login]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================================
       Helpers de existencia
    ========================================= */
    public function existsByCorreo($correo)
    {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function existsByNombreUsuario($nombreUsuario)
    {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
        $stmt->execute([$nombreUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function existsByCelular($celular)
    {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE celular = ?");
        $stmt->execute([$celular]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /* =========================================
       CREAR USUARIO (registro o desde dashboard)
    ========================================= */
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios 
                (nombre, correo, nombre_usuario, celular, cargo, foto, password, estado)
            VALUES 
                (:nombre, :correo, :nombre_usuario, :celular, :cargo, :foto, :password, :estado)
        ");

        return $stmt->execute([
            ':nombre'         => $data['nombre'],
            ':correo'         => $data['correo'],
            ':nombre_usuario' => $data['nombre_usuario'] ?? $data['correo'],
            ':celular'        => $data['celular']        ?? null,
            ':cargo'          => $data['cargo']          ?? null,
            ':foto'           => $data['foto']           ?? null,
            ':password'       => $data['password'],
            ':estado'         => $data['estado']         ?? 1,
        ]);
    }
}
