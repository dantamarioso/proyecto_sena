<?php

/**
 * Clase PermissionHelper - Sistema de permisos basado en roles y nodos
 * 
 * Roles:
 * - admin: Acceso total a todos los nodos y líneas
 * - dinamizador: Acceso a su nodo (todas las líneas del nodo)
 * - usuario: Acceso limitado a su nodo y línea (solo ingreso/retiro)
 */
class PermissionHelper
{
    private $user;
    private $db;
    
    public function __construct($user = null, $db = null)
    {
        $this->user = $user ?? ($_SESSION['user'] ?? null);
        $this->db = $db ?? Database::getInstance();
        
        if (!$this->user) {
            throw new Exception("Usuario no autenticado");
        }
    }
    
    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin()
    {
        return ($this->user['rol'] ?? null) === 'admin';
    }
    
    /**
     * Verificar si el usuario es dinamizador
     */
    public function isDinamizador()
    {
        return ($this->user['rol'] ?? null) === 'dinamizador';
    }
    
    /**
     * Verificar si el usuario es un usuario normal
     */
    public function isUsuario()
    {
        return ($this->user['rol'] ?? null) === 'usuario';
    }
    
    /**
     * Obtener nodo del usuario
     */
    public function getUserNodo()
    {
        return $this->user['nodo_id'] ?? null;
    }
    
    /**
     * Obtener línea del usuario (si aplica)
     */
    public function getUserLinea()
    {
        return $this->user['linea_id'] ?? null;
    }
    
    /**
     * Verificar si puede ver un nodo específico
     * - Admin: puede ver todos
     * - Dinamizador/Usuario: solo su nodo
     */
    public function canViewNode($nodo_id)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        return $this->getUserNodo() == $nodo_id;
    }
    
    /**
     * Verificar si puede ver una línea específica
     * - Admin: puede ver todas
     * - Dinamizador: puede ver todas las de su nodo
     * - Usuario: solo su línea asignada
     */
    public function canViewLinea($linea_id)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        // Obtener información de la línea
        $stmt = $this->db->prepare("SELECT nodo_id FROM lineas WHERE id = ?");
        $stmt->execute([$linea_id]);
        $linea = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$linea) {
            return false;
        }
        
        // Verif que el nodo de la línea sea el del usuario
        if ($linea['nodo_id'] != $this->getUserNodo()) {
            return false;
        }
        
        // Si es usuario, solo puede ver su línea asignada
        if ($this->isUsuario()) {
            return $this->getUserLinea() == $linea_id;
        }
        
        return true;
    }
    
    /**
     * Verificar si puede ver todos los materiales
     * - Admin: sí, todos
     * - Dinamizador: solo de su nodo
     * - Usuario: solo de su nodo y línea
     */
    public function canViewAllMateriales()
    {
        return $this->isAdmin();
    }
    
    /**
     * Obtener consulta SQL filtrada según permisos
     * @param string $alias Alias de tabla (ej: 'm' para materiales)
     * @return string Cláusula WHERE para filtrar
     */
    public function getMaterialesWhereClause($alias = 'm')
    {
        if ($this->isAdmin()) {
            return "1=1"; // Sin filtros para admin
        }
        
        $nodo_id = $this->getUserNodo();
        
        if ($this->isDinamizador()) {
            // Dinamizador ve todos los materiales de su nodo
            return "{$alias}.nodo_id = {$nodo_id}";
        }
        
        if ($this->isUsuario()) {
            // Usuario solo ve materiales de su nodo y su línea
            $linea_id = $this->getUserLinea();
            return "{$alias}.nodo_id = {$nodo_id} AND {$alias}.linea_id = {$linea_id}";
        }
        
        return "1=0"; // Denegar acceso
    }
    
    /**
     * Verificar si puede crear materiales
     * - Admin: sí
     * - Dinamizador: solo en su nodo
     * - Usuario: no
     */
    public function canCreateMaterial($nodo_id = null)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        if ($this->isUsuario()) {
            return false; // Los usuarios no pueden crear
        }
        
        // Dinamizador solo en su nodo
        if ($this->isDinamizador()) {
            $target_nodo = $nodo_id ?? $this->getUserNodo();
            return $target_nodo == $this->getUserNodo();
        }
        
        return false;
    }
    
    /**
     * Verificar si puede editar un material
     * - Admin: sí
     * - Dinamizador: solo de su nodo
     * - Usuario: no
     */
    public function canEditMaterial($material_id)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        if ($this->isUsuario()) {
            return false; // Los usuarios no pueden editar
        }
        
        // Obtener nodo del material
        $material = $this->getMaterialInfo($material_id);
        if (!$material) {
            return false;
        }
        
        // Dinamizador solo de su nodo
        if ($this->isDinamizador()) {
            return $material['nodo_id'] == $this->getUserNodo();
        }
        
        return false;
    }
    
    /**
     * Verificar si puede eliminar un material
     * - Admin: sí
     * - Dinamizador: solo de su nodo
     * - Usuario: no
     */
    public function canDeleteMaterial($material_id)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        if ($this->isUsuario()) {
            return false; // Los usuarios no pueden eliminar
        }
        
        // Obtener nodo del material
        $material = $this->getMaterialInfo($material_id);
        if (!$material) {
            return false;
        }
        
        // Dinamizador solo de su nodo
        if ($this->isDinamizador()) {
            return $material['nodo_id'] == $this->getUserNodo();
        }
        
        return false;
    }
    
    /**
     * Verificar si puede ingresar materiales
     * - Admin: sí
     * - Dinamizador: solo de su nodo
     * - Usuario: solo de su nodo y línea
     */
    public function canEnterMaterial($material_id)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        $material = $this->getMaterialInfo($material_id);
        if (!$material) {
            return false;
        }
        
        // Verificar que sea del nodo del usuario
        if ($material['nodo_id'] != $this->getUserNodo()) {
            return false;
        }
        
        // Si es usuario, solo de su línea
        if ($this->isUsuario()) {
            return $material['linea_id'] == $this->getUserLinea();
        }
        
        return true;
    }
    
    /**
     * Verificar si puede retirar materiales
     * - Admin: sí
     * - Dinamizador: solo de su nodo
     * - Usuario: solo de su nodo y línea
     */
    public function canRetireMaterial($material_id)
    {
        return $this->canEnterMaterial($material_id);
    }
    
    /**
     * Verificar si puede gestionar usuarios (crear, editar, cambiar rol)
     * - Admin: sí
     * - Dinamizador/Usuario: no
     */
    public function canManageUsers()
    {
        return $this->isAdmin();
    }
    
    /**
     * Verificar si puede cambiar el rol de un usuario
     * - Admin: sí
     * - Dinamizador/Usuario: no
     */
    public function canChangeUserRole()
    {
        return $this->isAdmin();
    }
    
    /**
     * Verificar si puede asignar nodo a un usuario
     * - Admin: a cualquier nodo
     * - Dinamizador: solo a su nodo (NO)
     * - Usuario: no
     */
    public function canAssignNode($nodo_id = null)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Verificar si puede ver el historial de auditoría
     * - Admin: todo
     * - Dinamizador: de su nodo
     * - Usuario: solo el suyo
     */
    public function canViewAudit()
    {
        return $this->isAdmin() || $this->isDinamizador();
    }
    
    /**
     * Obtener información de un material
     */
    private function getMaterialInfo($material_id)
    {
        $stmt = $this->db->prepare("SELECT id, nodo_id, linea_id FROM materiales WHERE id = ?");
        $stmt->execute([$material_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener lista de nodos que el usuario puede ver
     */
    public function getAccesibleNodos()
    {
        if ($this->isAdmin()) {
            // Admin ve todos
            $stmt = $this->db->prepare("SELECT * FROM nodos WHERE estado = 1 ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Dinamizador y usuario solo su nodo
        $stmt = $this->db->prepare("SELECT * FROM nodos WHERE id = ? AND estado = 1");
        $stmt->execute([$this->getUserNodo()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener lista de líneas que el usuario puede ver
     */
    public function getAccesibleLineas()
    {
        if ($this->isAdmin()) {
            // Admin ve todas
            $stmt = $this->db->prepare("SELECT * FROM lineas WHERE estado = 1 ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($this->isDinamizador()) {
            // Dinamizador ve todas las de su nodo
            $stmt = $this->db->prepare("SELECT * FROM lineas WHERE nodo_id = ? AND estado = 1 ORDER BY nombre");
            $stmt->execute([$this->getUserNodo()]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($this->isUsuario()) {
            // Usuario solo su línea
            $stmt = $this->db->prepare("SELECT * FROM lineas WHERE id = ? AND estado = 1");
            $stmt->execute([$this->getUserLinea()]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [];
    }
    
    /**
     * Obtener descripción del rol en español
     */
    public static function getRolDescripcion($rol)
    {
        $descripciones = [
            'admin' => 'Administrador - Acceso total',
            'dinamizador' => 'Dinamizador - Gestión de nodo',
            'usuario' => 'Usuario - Acceso limitado',
        ];
        
        return $descripciones[$rol] ?? $rol;
    }
    
    /**
     * Obtener lista de permisos del usuario en formato legible
     */
    public function getPermisos()
    {
        $permisos = [
            'rol' => $this->user['rol'],
            'nodo' => $this->user['nodo_id'] ?? null,
            'linea' => $this->user['linea_id'] ?? null,
            'puede_ver_materiales' => !$this->isAdmin() ? 'Solo su nodo' : 'Todos',
            'puede_crear_materiales' => $this->canCreateMaterial(),
            'puede_editar_materiales' => $this->isDinamizador() ? 'Su nodo' : ($this->isAdmin() ? 'Todos' : false),
            'puede_eliminar_materiales' => $this->isDinamizador() ? 'Su nodo' : ($this->isAdmin() ? 'Todos' : false),
            'puede_ingresar_retirar' => $this->isUsuario() ? 'Su nodo/línea' : ($this->isDinamizador() ? 'Su nodo' : 'Todos'),
            'puede_gestionar_usuarios' => $this->isAdmin(),
            'puede_ver_auditoria' => $this->canViewAudit(),
        ];
        
        return $permisos;
    }
}
