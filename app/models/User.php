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

        if (!empty($data['foto'])) {
            $sql .= ", foto = :foto";
        }

        if (!empty($data['password'])) {
            $sql .= ", password = :password";
        }

        if (!empty($data['rol'])) {
            $sql .= ", rol = :rol";
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);

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

        if (!empty($data['rol'])) {
            $params[':rol'] = $data['rol'];
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

    public function searchUsers($q, $estado, $rol, $limit, $offset)
    {
        $currentId = $_SESSION['user']['id'] ?? 0;

        $sql = "SELECT * FROM usuarios WHERE id <> :currentId";
        $params = [
            ':currentId' => $currentId
        ];

        if ($q !== '') {
            $sql .= " AND (nombre LIKE :q OR correo LIKE :q OR nombre_usuario LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        if ($estado === '0' || $estado === '1') {
            $sql .= " AND estado = :estado";
            $params[':estado'] = (int)$estado;
        }

        if (in_array($rol, ['admin', 'usuario', 'invitado'], true)) {
            $sql .= " AND rol = :rol";
            $params[':rol'] = $rol;
        }

        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->bindValue(':limit',  (int)$limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function countUsersFiltered($q, $estado, $rol)
    {
        $currentId = $_SESSION['user']['id'] ?? 0;

        $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE id <> :currentId";
        $params = [
            ':currentId' => $currentId
        ];

        if ($q !== '') {
            $sql .= " AND (nombre LIKE :q OR correo LIKE :q OR nombre_usuario LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        if ($estado === '0' || $estado === '1') {
            $sql .= " AND estado = :estado";
            $params[':estado'] = (int)$estado;
        }

        if (in_array($rol, ['admin', 'usuario', 'invitado'], true)) {
            $sql .= " AND rol = :rol";
            $params[':rol'] = $rol;
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
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
            (nombre, correo, nombre_usuario, celular, cargo, foto, password, estado, rol)
        VALUES 
            (:nombre, :correo, :nombre_usuario, :celular, :cargo, :foto, :password, :estado, :rol)
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
            ':rol'            => $data['rol']            ?? 'usuario',
        ]);
    }
}
