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
            ':nombre'         => $data['nombre'] ?? null,
            ':correo'         => $data['correo'] ?? null,
            ':nombre_usuario' => $data['nombre_usuario'] ?? null,
            ':celular'        => $data['celular'] ?? null,
            ':cargo'          => $data['cargo'] ?? null,
            ':estado'         => $data['estado'] ?? null,
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
        // Establecer variable de sesión para el trigger
        $userId = $_SESSION['user']['id'] ?? 1;
        $this->db->prepare("SET @usuario_id = :usuario_id")->execute([':usuario_id' => $userId]);
        
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
        $result = $stmt->execute([':id' => $id]);
        
        // Reiniciar AUTO_INCREMENT después de eliminar
        if ($result) {
            $maxId = $this->db->query("SELECT MAX(id) as max_id FROM usuarios")->fetch(PDO::FETCH_ASSOC);
            $nextId = ($maxId['max_id'] ?? 0) + 1;
            
            // Reiniciar el contador AUTO_INCREMENT
            $this->db->exec("ALTER TABLE usuarios AUTO_INCREMENT = " . $nextId);
        }
        
        return $result;
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


    public function saveRecoveryCode($id, $code)
    {
        $stmt = $this->db->prepare("
        UPDATE usuarios SET 
            recovery_code = :code,
            recovery_expire = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
        WHERE id = :id
    ");
        return $stmt->execute([':code' => $code, ':id' => $id]);
    }

    public function clearRecoveryCode($id)
    {
        $stmt = $this->db->prepare("
        UPDATE usuarios SET 
            recovery_code = NULL,
            recovery_expire = NULL
        WHERE id = :id
    ");
        return $stmt->execute([':id' => $id]);
    }

    public function findByCorreo($correo)
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE correo = :c LIMIT 1");
        $stmt->execute([':c' => $correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyCode($correo, $code)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM usuarios 
        WHERE correo = :correo AND recovery_code = :code 
              AND recovery_expire > NOW()
        LIMIT 1
    ");
        $stmt->execute([':correo' => $correo, ':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function setNewPassword($id, $pass)
    {
        $stmt = $this->db->prepare("
        UPDATE usuarios SET password = :p WHERE id = :id
    ");
        return $stmt->execute([
            ':p' => password_hash($pass, PASSWORD_DEFAULT),
            ':id' => $id
        ]);
    }

    /* =========================================
       VERIFICACIÓN DE EMAIL EN REGISTRO
    ========================================= */
    public function saveVerificationCode($id, $code)
    {
        $stmt = $this->db->prepare("
        UPDATE usuarios SET 
            verification_code = :code,
            verification_expire = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
        WHERE id = :id
    ");
        return $stmt->execute([':code' => $code, ':id' => $id]);
    }

    public function verifyEmailCode($correo, $code)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM usuarios 
        WHERE correo = :correo AND verification_code = :code 
              AND verification_expire > NOW()
        LIMIT 1
    ");
        $stmt->execute([':correo' => $correo, ':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyEmailCodeById($userId, $code)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM usuarios 
        WHERE id = :id AND verification_code = :code 
              AND verification_expire > NOW()
        LIMIT 1
    ");
        $stmt->execute([':id' => $userId, ':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clearVerificationCode($id)
    {
        $stmt = $this->db->prepare("
        UPDATE usuarios SET 
            verification_code = NULL,
            verification_expire = NULL
        WHERE id = :id
    ");
        return $stmt->execute([':id' => $id]);
    }

    public function markEmailAsVerified($id)
    {
        $stmt = $this->db->prepare("
        UPDATE usuarios SET 
            email_verified = 1,
            verification_code = NULL,
            verification_expire = NULL
        WHERE id = :id
    ");
        return $stmt->execute([':id' => $id]);
    }

    public function canResendVerificationCode($id)
    {
        $stmt = $this->db->prepare("
        SELECT verification_expire FROM usuarios WHERE id = :id LIMIT 1
    ");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !$user['verification_expire']) {
            return true;
        }

        // Permite reenvío si han pasado más de 90 segundos desde el último envío
        // verification_expire es cuando vence el código (10 minutos desde que se creó)
        // Así que restamos 600 - 90 = 510 segundos (8.5 minutos) para permitir reenvío después de 90 segundos
        $expireTime = strtotime($user['verification_expire']) - 510;
        return time() > $expireTime;
    }

    public function getRemainingCooldownTime($id)
    {
        $stmt = $this->db->prepare("
        SELECT verification_expire FROM usuarios WHERE id = :id LIMIT 1
    ");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !$user['verification_expire']) {
            return 0;
        }

        $expireTime = strtotime($user['verification_expire']) - 510;
        $remaining = $expireTime - time();
        
        return max(0, $remaining);
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

        $success = $stmt->execute([
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

        if ($success) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }
}
