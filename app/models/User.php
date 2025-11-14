<?php
class User extends Model {

    public function findByCorreoOrUsername($login) {
        $stmt = $this->db->prepare("
            SELECT * FROM usuarios 
            WHERE correo = :login OR nombre_usuario = :login
            LIMIT 1
        ");
        $stmt->execute([':login' => $login]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existsByCorreo($correo) {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function existsByNombreUsuario($nombreUsuario) {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
        $stmt->execute([$nombreUsuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function existsByCelular($celular) {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE celular = ?");
        $stmt->execute([$celular]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

public function create($data) {
    $stmt = $this->db->prepare("
        INSERT INTO usuarios 
            (nombre, apellido, correo, nombre_usuario, celular, cargo, foto, password)
        VALUES 
            (:nombre, :apellido, :correo, :nombre_usuario, :celular, :cargo, :foto, :password)
    ");

    return $stmt->execute([
        ':nombre'         => $data['nombre'],
        ':apellido'       => $data['apellido'],
        ':correo'         => $data['correo'],
        // si no mandas nombre_usuario desde el controller, puedes usar el correo
        ':nombre_usuario' => $data['nombre_usuario'] ?? $data['correo'],
        ':celular'        => $data['celular']        ?? null,
        ':cargo'          => $data['cargo']          ?? null,
        ':foto'           => $data['foto']           ?? null,
        ':password'       => $data['password'],
    ]);
}
}
