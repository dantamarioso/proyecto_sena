# Sistema de Gestión de Usuarios e Inventario

**Versión:** 1.1.0  
**Fecha:** Diciembre 2025  
**Desarrollador:** SENA - Proyecto Educativo

---

## 📋 Descripción General

Sistema web completo de **gestión de usuarios** e **inventario** desarrollado con **PHP puro** (sin frameworks), implementando un patrón **MVC minimalista**. 

Incluye autenticación robusta, recuperación de contraseña con códigos de 6 dígitos, registro con verificación de email, gestión de roles, administración de usuarios, gestión integral de materiales e inventario, con auditoría completa de cambios y reportes.

**Características principales:**
- ✅ Autenticación segura con sesiones y hash de contraseñas
- ✅ Recuperación de contraseña con códigos de verificación (10 min)
- ✅ Registro con verificación de email obligatoria
- ✅ Gestión de usuarios (CRUD completo) - solo administradores
- ✅ Sistema de roles (admin, usuario, invitado)
- ✅ Auditoría de cambios con historial completo
- ✅ Perfil de usuario editable con foto
- ✅ Cambio de foto de perfil con modal AJAX
- ✅ Búsqueda y filtrado de usuarios
- ✅ **Gestión completa de materiales e inventario**
- ✅ **Historial de movimientos de inventario**
- ✅ **Líneas y nodos de almacenamiento**
- ✅ **Archivos adjuntos a materiales**
- ✅ **Reportes en Excel y PDF**
- ✅ Control de acceso basado en roles

---

## 📁 Estructura de Directorios

```
proyecto_sena/
├── public/                          # Punto de entrada y assets
│   ├── index.php                   # Router principal (single entry point)
│   ├── css/                        # Hojas de estilo
│   │   ├── layout.css              # Header, footer, layout general
│   │   ├── sidebar.css             # Navegación lateral
│   │   ├── style.css               # Estilos globales
│   │   ├── login.css               # Estilos login
│   │   ├── register.css            # Estilos registro
│   │   ├── recovery.css            # Recuperación de contraseña
│   │   ├── perfil.css              # Perfil de usuario
│   │   ├── perfil_mejorado.css     # Perfil mejorado
│   │   ├── usuarios.css            # Gestión de usuarios
│   │   ├── usuarios_form.css       # Formularios de usuarios
│   │   ├── usuarios_responsive.css # Responsive usuarios
│   │   ├── usuarios_gestion.css    # Tabla de gestión de usuarios
│   │   ├── materiales.css          # Gestión de materiales
│   │   ├── materiales_form.css     # Formularios de materiales
│   │   ├── modales.css             # Estilos modales
│   │   ├── tablas.css              # Estilos tablas
│   │   ├── utilidades.css          # Clases utilitarias
│   │   ├── audit.css               # Auditoría
│   │   ├── audit_mejorado.css      # Auditoría mejorada
│   │   ├── login.css               # Login mejorado
│   │   ├── auth_mejorado.css       # Auth mejorado
│   │   ├── sidebar-toggle.css      # Toggle sidebar
│   │   └── recovery.css            # Recuperación de contraseña
│   ├── js/                         # JavaScript
│   │   ├── app.js                  # App principal
│   │   ├── login.js                # Login interactivo
│   │   ├── register.js             # Validación registro
│   │   ├── recovery.js             # Recuperación contraseña
│   │   ├── password_toggle.js      # Mostrar/ocultar contraseña
│   │   ├── sidebar.js              # Sidebar responsive
│   │   ├── perfil.js               # Cambio de foto AJAX
│   │   ├── usuarios.js             # Búsqueda, filtrado, paginación
│   │   ├── materiales.js           # Gestión materiales (búsqueda, filtrado)
│   │   ├── audit.js                # Auditoría
│   │   ├── historial_mejorado.js   # Historial mejorado
│   │   └── utilidades.js           # Funciones utilitarias
│   ├── uploads/                    # Archivos subidos
│   │   ├── fotos/                  # Fotos de perfil
│   │   └── materiales/             # Archivos de materiales
│   └── img/                        # Imágenes estáticas
│
├── app/                            # Lógica de la aplicación
│   ├── core/                       # Clases base
│   │   ├── Database.php            # Conexión a BD (Singleton)
│   │   ├── Model.php               # Clase base para modelos
│   │   └── Controller.php          # Clase base para controladores
│   ├── controllers/                # Controladores
│   │   ├── AuthController.php      # Autenticación y recuperación
│   │   ├── HomeController.php      # Dashboard principal
│   │   ├── UsuariosController.php  # Gestión de usuarios
│   │   ├── PerfilController.php    # Perfil de usuario
│   │   ├── MaterialesController.php # Gestión de materiales e inventario
│   │   └── AuditController.php     # Auditoría y historial
│   ├── models/                     # Modelos de datos
│   │   ├── User.php                # Modelo Usuario (60+ métodos)
│   │   ├── Audit.php               # Modelo Auditoría
│   │   ├── Material.php            # Modelo Materiales
│   │   ├── MaterialArchivo.php     # Archivos adjuntos de materiales
│   │   ├── Linea.php               # Líneas de inventario
│   │   └── Nodo.php                # Nodos de inventario
│   ├── helpers/                    # Funciones auxiliares
│   │   ├── MailHelper.php          # Envío de emails con PHPMailer
│   │   ├── ValidationHelper.php    # Validaciones comunes
│   │   ├── DebugHelper.php         # Ayudas para debugging
│   │   ├── PermissionHelper.php    # Control de permisos
│   │   ├── ViewHelpers.php         # Helpers para vistas
│   │   ├── EnvHelper.php           # Manejo de variables de entorno
│   │   ├── ExcelHelper.php         # Generación de reportes Excel
│   │   └── PdfHelper.php           # Generación de reportes PDF
│   └── views/                      # Plantillas
│       ├── layouts/                # Diseño base
│       │   ├── header.php          # Encabezado y navbar
│       │   ├── footer.php          # Pie de página
│       │   └── sidebar.php         # Menú lateral con enlaces
│       ├── auth/                   # Vistas autenticación
│       │   ├── login.php           # Formulario login
│       │   ├── register.php        # Formulario registro
│       │   ├── forgot.php          # Solicitar recuperación
│       │   ├── verifyCode.php      # Verificar código recuperación
│       │   ├── reset.php           # Cambiar contraseña
│       │   ├── verifyEmail.php     # Verificar email registro
│       │   ├── succes.php          # Página éxito
│       │   └── terminos.php        # Términos y condiciones
│       ├── home/                   # Dashboard
│       │   └── index.php           # Página principal
│       ├── usuarios/               # Gestión de usuarios
│       │   ├── gestion_de_usuarios.php  # Tabla lista usuarios
│       │   ├── crear.php           # Crear usuario
│       │   ├── editar.php          # Editar usuario
│       │   ├── detalles.php        # Ver detalles usuario
│       │   └── index.php           # Redirección
│       ├── perfil/                 # Perfil de usuario
│       │   ├── ver.php             # Ver perfil personal
│       │   ├── editar.php          # Editar perfil
│       │   └── verificarCambioCorreo.php  # Verificar cambio email
│       ├── materiales/             # Gestión de materiales e inventario
│       │   ├── index.php           # Redirección
│       │   ├── crear.php           # Crear material
│       │   ├── editar.php          # Editar material
│       │   ├── detalles.php        # Ver detalles material
│       │   ├── historial_inventario.php  # Historial de cambios inventario
│       │   └── partials/           # Componentes reutilizables
│       ├── audit/                  # Auditoría
│       │   └── historial.php       # Historial de cambios
│       └── dashboard/              # (para futura expansión)
│
├── config/
│   └── config.php                  # Configuración (BD, BASE_URL)
│
├── database/
│   ├── inventario_db.sql           # Esquema completo de BD
│   └── migrations/                 # Scripts de migración
│
├── vendor/                         # Dependencias (Composer)
│   ├── autoload.php
│   ├── composer/
│   └── phpmailer/                  # PHPMailer 7.x
│
├── .github/
│   └── copilot-instructions.md    # Instrucciones para IA
│
├── composer.json                   # Dependencias PHP
├── composer.lock                   # Lock file
├── error_log.txt                   # Log de errores
└── README.md                       # Este archivo
```

---

## 🔧 Configuración e Instalación

### Requisitos Previos

- **PHP 7.4+** (recomendado 8.1+)
- **MySQL 5.7+** o **MariaDB 10.2+**
- **Composer** (para instalar dependencias)
- **Servidor Web** (Apache, Nginx, o PHP built-in server)

### Pasos de Instalación

#### 1. Clonar el repositorio

```bash
git clone https://github.com/dantamarioso/proyecto_sena.git
cd proyecto_sena
```

#### 2. Instalar dependencias

```bash
composer install
```

Esto instalará **PHPMailer 7.x** y sus dependencias.

#### 3. Configurar Base de Datos

Crea una base de datos MySQL:

```sql
CREATE DATABASE inventario_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Importa el esquema SQL (si existe `database.sql`):

```bash
mysql -u root inventario_db < database.sql
```

**Si no existe, crea manualmente la estructura:**

```sql
-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    celular VARCHAR(20),
    cargo VARCHAR(100),
    foto VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    estado TINYINT(1) DEFAULT 1,
    rol ENUM('admin', 'usuario', 'invitado') DEFAULT 'usuario',
    
    -- Recuperación de contraseña
    recovery_code VARCHAR(6),
    recovery_expire DATETIME,
    
    -- Verificación de email
    verification_code VARCHAR(6),
    verification_expire DATETIME,
    email_verified TINYINT(1) DEFAULT 0,
    
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de auditoría
CREATE TABLE auditoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    tabla VARCHAR(50),
    registro_id INT,
    accion VARCHAR(50),
    detalles JSON,
    admin_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de líneas de inventario
CREATE TABLE lineas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de nodos (ubicaciones de almacenamiento)
CREATE TABLE nodos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    ubicacion VARCHAR(200),
    capacidad INT,
    estado TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de materiales
CREATE TABLE materiales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    cantidad INT DEFAULT 0,
    unidad VARCHAR(50),
    linea_id INT,
    nodo_id INT,
    precio_unit DECIMAL(10, 2),
    estado TINYINT(1) DEFAULT 1,
    usuario_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (linea_id) REFERENCES lineas(id) ON DELETE SET NULL,
    FOREIGN KEY (nodo_id) REFERENCES nodos(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de archivos de materiales
CREATE TABLE material_archivos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    material_id INT NOT NULL,
    nombre_archivo VARCHAR(255),
    archivo_path VARCHAR(255),
    tipo_archivo VARCHAR(50),
    tamano INT,
    usuario_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (material_id) REFERENCES materiales(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Índices para mejor rendimiento
CREATE INDEX idx_usuarios_correo ON usuarios(correo);
CREATE INDEX idx_usuarios_nombre_usuario ON usuarios(nombre_usuario);
CREATE INDEX idx_usuarios_rol ON usuarios(rol);
CREATE INDEX idx_auditoria_usuario ON auditoria(usuario_id);
CREATE INDEX idx_auditoria_admin ON auditoria(admin_id);
CREATE INDEX idx_auditoria_fecha ON auditoria(fecha_creacion);
CREATE INDEX idx_auditoria_accion ON auditoria(accion);
CREATE INDEX idx_materiales_codigo ON materiales(codigo);
CREATE INDEX idx_materiales_linea ON materiales(linea_id);
CREATE INDEX idx_materiales_nodo ON materiales(nodo_id);
CREATE INDEX idx_materiales_estado ON materiales(estado);
CREATE INDEX idx_material_archivos ON material_archivos(material_id);
```

#### 4. Configurar aplicación

Edita `config/config.php`:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventario_db');
define('DB_USER', 'root');
define('DB_PASS', 'tu_contraseña');

define('BASE_URL', 'http://localhost:8000');
```

#### 5. Configurar correo (MailHelper)

Edita `app/helpers/MailHelper.php` y actualiza las credenciales SMTP:

```php
$mail->Host       = "smtp.gmail.com";
$mail->SMTPAuth   = true;
$mail->Username   = "tu_email@gmail.com";
$mail->Password   = "tu_contraseña_app";  // Usa contraseña de app de Google
$mail->SMTPSecure = "tls";
$mail->Port       = 587;
```

> **⚠️ Seguridad:** Las credenciales están hardcodeadas. Considere usar variables de entorno (.env).

#### 6. Crear directorios de uploads

```bash
mkdir -p public/uploads/fotos
mkdir -p public/uploads/materiales
chmod 777 public/uploads/fotos
chmod 777 public/uploads/materiales
```

#### 7. Ejecutar servidor

**Opción 1: PHP built-in server**

```bash
php -S localhost:8000 -t public
```

Accede a: `http://localhost:8000`

**Opción 2: Apache**

Configura el DocumentRoot a `proyecto_sena/public`

**Opción 3: Nginx**

```nginx
server {
    listen 8000;
    server_name localhost;
    root /path/to/proyecto_sena/public;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## 🏗️ Arquitectura MVC

### Patrón Minimalista

La aplicación implementa un **MVC sin framework** con componentes simples:

#### **1. Router (index.php)**

```php
// URL: index.php?url=usuarios/crear
$url = $_GET['url'] ?? 'auth/login';
$parts = explode('/', $url);

$controllerName = ucfirst($parts[0]) . 'Controller'; // usuarios -> UsuariosController
$method = $parts[1] ?? 'login';

$controller = new $controllerName();
$controller->$method();
```

#### **2. Controlador Base (Controller.php)**

```php
abstract class Controller {
    protected function view($view, $data = []) {
        extract($data);  // Variables disponibles en la vista
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
    
    protected function redirect($route) {
        header('Location: ' . BASE_URL . '/?url=' . $route);
        exit;
    }
}
```

#### **3. Modelo Base (Model.php)**

```php
abstract class Model {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
}
```

#### **4. Base de Datos (Database.php - Singleton)**

```php
class Database {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }
        return self::$instance;
    }
}
```

---

## 🔐 Autenticación y Autorización

### Sistema de Sesiones

La sesión se inicia en `public/index.php`:

```php
session_start();
```

**Usuario autenticado guardado en:**

```php
$_SESSION['user'] = [
    'id'     => 1,
    'nombre' => 'Juan',
    'cargo'  => 'Administrador',
    'foto'   => 'uploads/fotos/foto_xxx.jpg',
    'rol'    => 'admin'  // 'admin', 'usuario', 'invitado'
];
```

### Control de Acceso por Rol

**En controladores:**

```php
private function requireAdmin() {
    if (($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
        http_response_code(403);
        echo "Acceso denegado.";
        exit;
    }
}
```

**En vistas:**

```php
<?php if (($_SESSION['user']['rol'] ?? 'usuario') === 'admin'): ?>
    <!-- Contenido solo para admins -->
<?php endif; ?>
```

### Regeneración de ID

En login seguro:

```php
session_regenerate_id(true);
```

---

## 📝 Flujos Principales

### 1. **Flujo de Login**

```
AuthController::login() [GET]
    ↓ (si POST)
    → Validar credenciales
    → Regenerar ID de sesión
    → Guardar en $_SESSION['user']
    → Redirigir a home/index
```

**Validaciones:**
- Email o nombre de usuario + contraseña
- Usuarios inactivos (estado=0) no pueden entrar
- Hash verificado con `password_verify()`

### 2. **Flujo de Recuperación de Contraseña**

```
1. AuthController::forgot()              [Formulario solicitud]
2. AuthController::sendCode()            [Genera código 6 dígitos, expira en 10 min]
3. Código guardado en: usuarios.recovery_code
4. AuthController::verifyCode()          [Formulario verificación]
5. AuthController::verifyCodePost()      [Valida código]
6. AuthController::resetPassword()       [Formulario nueva contraseña]
7. AuthController::resetPasswordPost()   [Actualiza contraseña]
```

**Características:**
- Códigos de 6 dígitos aleatorios
- Expiran en 10 minutos
- Cooldown de 90 segundos antes de reenviar
- Email de verificación con HTML profesional

### 3. **Flujo de Registro con Verificación de Email**

```
1. AuthController::register()                [Formulario registro]
2. Validar datos (email, contraseña, etc)
3. User::create()                           [Crear con email_verified=0]
4. Generar código de verificación
5. MailHelper::sendCode()                   [Enviar email]
6. AuthController::verifyEmail()            [Formulario código]
7. AuthController::verifyEmailPost()        [Validar código]
8. User::markEmailAsVerified()              [Marcar verificado]
9. Redirigir a login
```

### 4. **Flujo de Gestión de Usuarios (Admin)**

```
UsuariosController::gestionDeUsuarios()     [Lista con filtros]
    ├─ UsuariosController::crear()          [Crear nuevo usuario]
    ├─ UsuariosController::editar()         [Editar datos usuario]
    ├─ UsuariosController::bloquear()       [Desactivar usuario]
    ├─ UsuariosController::desbloquear()    [Activar usuario]
    ├─ UsuariosController::eliminar()       [Borrar usuario]
    └─ UsuariosController::buscar() [AJAX]  [Búsqueda y filtrado]
```

**Validaciones:**
- Solo admins acceden
- Email único
- Nombre de usuario único
- Contraseña: mín 8 chars, 1 mayúscula, 1 carácter especial
- Foto (JPG/PNG) opcional

### 5. **Flujo de Perfil de Usuario**

```
PerfilController::ver()                     [Ver perfil actual]
PerfilController::editar()                  [Editar perfil]
    ├─ Usuarios: editan el propio
    └─ Admins: editan cualquiera
PerfilController::cambiarFoto() [AJAX]      [Cambiar foto desde sidebar]
PerfilController::verificarCambioCorreo()   [Si cambia email]
```

**Restricciones no-admin:**
- Pueden editar: nombre, correo, usuario, celular, cargo, contraseña, foto
- No pueden editar: rol, estado

**Restricciones admin:**
- Pueden editar todo incluyendo rol y estado

### 6. **Flujo de Auditoría**

```
Audit::registrarCambio()
    ├─ Tabla: usuarios
    ├─ Acción: crear, actualizar, eliminar, desactivar/activar
    ├─ Detalles: JSON con antes/después
    └─ Admin ID: quien hizo el cambio

AuditController::historial()                [Ver historial]
    ├─ Filtros: usuario, acción, fechas
    ├─ Paginación: 20 registros por página
    └─ Búsqueda AJAX
```

### 7. **Flujo de Gestión de Materiales e Inventario**

```
MaterialesController::crear()               [Crear nuevo material]
    ├─ Validaciones: código único, nombre
    ├─ Asignar a línea de inventario
    ├─ Asignar a nodo de almacenamiento
    ├─ Guardar en: materiales
    └─ Registrar en auditoría

MaterialesController::editar()              [Editar material existente]
    ├─ Cargar datos actual
    ├─ Validar cambios
    ├─ Actualizar stock
    └─ Registrar cambios en auditoría

MaterialesController::historialInventario() [Ver historial de movimientos]
    ├─ Filtros: material, usuario, rango de fechas
    ├─ Mostrar: cantidad anterior, nueva, diferencia
    ├─ Paginación: 20 registros por página
    └─ Export a Excel/PDF

Material::incrementarStock()                [Agregar stock]
Material::decrementarStock()                [Restar stock - cuando se consume]
```

---

## 📊 Modelos de Datos

### **User.php** (60+ métodos)

| Método | Descripción |
|--------|-------------|
| `create($data)` | Crear usuario |
| `findById($id)` | Obtener por ID |
| `findByCorreo($correo)` | Obtener por email |
| `findByCorreoOrUsername($login)` | Login |
| `all()` | Listar todos |
| `allExceptId($id)` | Listar excepto uno |
| `updateFull($id, $data)` | Actualizar completo |
| `updateEstado($id, $estado)` | Cambiar estado |
| `deleteById($id)` | Eliminar |
| `searchUsers($q, $estado, $rol, $limit, $offset)` | Búsqueda |
| `countUsersFiltered($q, $estado, $rol)` | Contar búsqueda |
| `existsByCorreo($correo)` | Verificar email |
| `existsByNombreUsuario($usuario)` | Verificar usuario |
| `saveRecoveryCode($id, $code)` | Guardar código recuperación |
| `verifyCode($correo, $code)` | Verificar código |
| `setNewPassword($id, $pass)` | Nueva contraseña |
| `saveVerificationCode($id, $code)` | Guardar código verificación |
| `verifyEmailCode($correo, $code)` | Verificar email |
| `markEmailAsVerified($id)` | Marcar email verificado |
| `canResendVerificationCode($id)` | Cooldown 90s |
| `getRemainingCooldownTime($id)` | Tiempo restante cooldown |

### **Audit.php**

| Método | Descripción |
|--------|-------------|
| `registrarCambio()` | Registrar cambio en auditoría |
| `obtenerHistorialUsuario()` | Historial de un usuario |
| `obtenerHistorialCompleto()` | Historial completo con filtros |
| `contarHistorial()` | Contar registros |
| `obtenerUsuariosEliminados()` | Usuarios eliminados en auditoría |

### **Material.php** (Gestión de Materiales e Inventario)

| Método | Descripción |
|--------|-------------|
| `create($data)` | Crear nuevo material |
| `findById($id)` | Obtener material por ID |
| `all()` | Listar todos los materiales |
| `search($q, $limit, $offset)` | Búsqueda de materiales |
| `countSearch($q)` | Contar resultados búsqueda |
| `updateFull($id, $data)` | Actualizar material |
| `updateEstado($id, $estado)` | Cambiar estado material |
| `deleteById($id)` | Eliminar material |
| `obtenerHistorialMovimientos($materialId)` | Historial de cambios |
| `obtenerStockActual($materialId)` | Stock disponible |
| `decrementarStock($materialId, $cantidad)` | Restar stock |
| `incrementarStock($materialId, $cantidad)` | Sumar stock |

### **Linea.php** (Líneas de Inventario)

Modelo para gestionar líneas o categorías de materiales.

| Método | Descripción |
|--------|-------------|
| `create($data)` | Crear línea de inventario |
| `findById($id)` | Obtener línea por ID |
| `all()` | Listar todas las líneas |
| `update($id, $data)` | Actualizar línea |
| `delete($id)` | Eliminar línea |

### **Nodo.php** (Nodos de Inventario)

Modelo para gestionar ubicaciones o nodos de almacenamiento.

| Método | Descripción |
|--------|-------------|
| `create($data)` | Crear nodo |
| `findById($id)` | Obtener nodo por ID |
| `all()` | Listar todos los nodos |
| `update($id, $data)` | Actualizar nodo |
| `delete($id)` | Eliminar nodo |
| `obtenerMaterialesEnNodo($nodoId)` | Materiales en este nodo |

### **MaterialArchivo.php** (Archivos de Materiales)

| Método | Descripción |
|--------|-------------|
| `create($data)` | Crear archivo adjunto |
| `findById($id)` | Obtener archivo por ID |
| `obtenerArchivosMaterial($materialId)` | Archivos de un material |
| `deleteById($id)` | Eliminar archivo |

---

## 🔒 Validaciones

### **Email**

```php
filter_var($correo, FILTER_VALIDATE_EMAIL)
```

### **Contraseña**

Requisitos mínimos:
- ✅ Mínimo 8 caracteres
- ✅ Al menos 1 mayúscula
- ✅ Al menos 1 carácter especial: `!@#$%^&*(),.?":{}|<>_-`

```php
$hasLength  = strlen($password) >= 8;
$hasUpper   = preg_match('/[A-Z]/', $password);
$hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>_\-]/', $password);
```

### **Estado de Usuario**

- `0` = Inactivo (bloqueado)
- `1` = Activo

### **Roles**

- `admin` = Administrador (acceso total)
- `usuario` = Usuario normal (uso limitado)
- `invitado` = Invitado (lectura)

### **Upload de Archivos**

```php
$permitidas = ['jpg', 'jpeg', 'png'];
$ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $permitidas)) {
    $errores[] = "Formato no permitido";
}
```

Nombres únicos:
```php
$nombreFoto = "uploads/fotos/" . uniqid("foto_") . "." . $ext;
```

---

## 🧰 Helpers Disponibles

### **MailHelper.php**
Centraliza el envío de emails con PHPMailer:
- `sendCode($correo, $asunto, $codigo, $tipo)` - Envía código por email
- Plantillas HTML profesionales
- SMTP configurado para Gmail

### **ValidationHelper.php**
Validaciones reutilizables:
- Email válido con `filter_var()`
- Contraseña segura (8 chars, mayúscula, especial)
- Celular numérico
- Validación de extensiones de archivo

### **PermissionHelper.php**
Control de acceso:
- `requireAdmin()` - Solo admins
- `requireUser()` - Usuarios autenticados
- `canEditProfile($userId)` - Puede editar perfil
- `canEditUser($userId)` - Puede editar otro usuario

### **ViewHelpers.php**
Helpers para vistas:
- `formatDate($date)` - Formatear fecha
- `getInitials($name)` - Iniciales de nombre
- `getRoleColor($role)` - Color por rol
- `getStatusBadge($status)` - Badge estado

### **DebugHelper.php**
Debugging en desarrollo:
- `dd($var)` - Die & dump
- `log($msg)` - Log a error_log.txt

### **EnvHelper.php**
Manejo de variables de entorno

### **ExcelHelper.php**
Generación de reportes en Excel (PHPSpreadsheet)

### **PdfHelper.php**
Generación de reportes en PDF

---

## 🔑 Hashing de Contraseñas

**Guardar:**
```php
password_hash($password, PASSWORD_DEFAULT)
```

**Verificar:**
```php
password_verify($input, $stored_hash)
```

> ✅ Usa algoritmo `bcrypt` automáticamente con `PASSWORD_DEFAULT`

---

## 🗄️ Prepared Statements (Seguridad SQL)

**Siempre usar:**

```php
// Con named placeholders
$stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $id]);

// Con ? placeholders
$stmt = $this->db->prepare("SELECT * FROM usuarios WHERE correo = ?");
$stmt->execute([$correo]);

// Binding de tipos
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
```

**Nunca concatenar:**
```php
// ❌ MAL - SQL Injection!
$sql = "SELECT * FROM usuarios WHERE id = " . $id;

// ✅ BIEN
$sql = "SELECT * FROM usuarios WHERE id = ?";
```

---

## 📧 Sistema de Correos (PHPMailer)

**Helper centralizado: `MailHelper::sendCode()`**

```php
MailHelper::sendCode(
    $correo,
    "Asunto",
    $codigo,
    'recuperacion' // o 'verificacion'
);
```

**Plantilla HTML profesional**
- Logo de la empresa
- Código en caja destacada
- Avisos de seguridad
- Links de contacto

**Configuración:**
- SMTP: Gmail
- Puerto: 587
- Seguridad: TLS
- Charset: UTF-8

---

## 🎨 Front-end

### Bootstrap 5.3.3
- Responsive y moderno
- Componentes listos para usar

### Bootstrap Icons
- Iconografía profesional

### CSS Personalizado

| Archivo | Propósito |
|---------|-----------|
| `sidebar.css` | Menú lateral |
| `layout.css` | Header, footer |
| `style.css` | Estilos globales |
| `login.css` | Formulario login |
| `register.css` | Formulario registro |
| `recovery.css` | Recuperación contraseña |
| `perfil.css` | Perfil usuario |
| `usuarios.css` | Gestión usuarios |
| `usuarios_form.css` | Formularios usuarios |
| `usuarios_responsive.css` | Responsive |
| `audit.css` | Auditoría |

### JavaScript

| Archivo | Funcionalidad |
|---------|---------------|
| `app.js` | App principal |
| `login.js` | Login interactivo |
| `register.js` | Validación registro |
| `recovery.js` | Recuperación contraseña |
| `password_toggle.js` | Mostrar/ocultar contraseña |
| `sidebar.js` | Sidebar responsive |
| `perfil.js` | Cambio de foto AJAX |
| `usuarios.js` | Búsqueda, filtrado, paginación |
| `audit.js` | Auditoría |

---

## 🚀 URLs y Rutas

### Autenticación

```
GET  /?url=auth/login              Formulario login
POST /?url=auth/login              Procesar login
GET  /?url=auth/register           Formulario registro
POST /?url=auth/register           Crear usuario
GET  /?url=auth/verifyEmail        Verificar email
POST /?url=auth/verifyEmail        Procesar verificación email
GET  /?url=auth/forgot             Solicitar recuperación
POST /?url=auth/sendCode           Enviar código
GET  /?url=auth/verifyCode         Verificar código
POST /?url=auth/verifyCodePost     Procesar verificación
GET  /?url=auth/resetPassword      Formulario nueva contraseña
POST /?url=auth/resetPasswordPost  Procesar reset
GET  /?url=auth/logout             Cerrar sesión
```

### Dashboard

```
GET  /?url=home/index              Página principal
```

### Usuarios (Admin)

```
GET  /?url=usuarios/gestionDeUsuarios      Lista usuarios
POST /?url=usuarios/crear                  Crear usuario
GET  /?url=usuarios/editar&id=1            Editar usuario
POST /?url=usuarios/editar                 Procesar edición
POST /?url=usuarios/bloquear               Desactivar usuario
POST /?url=usuarios/desbloquear            Activar usuario
POST /?url=usuarios/eliminar               Eliminar usuario
GET  /?url=usuarios/buscar                 API búsqueda (JSON)
GET  /?url=usuarios/verificarNombreUsuario API validar usuario (JSON)
```

### Perfil

```
GET  /?url=perfil/ver              Ver perfil
GET  /?url=perfil/editar           Editar perfil
POST /?url=perfil/editar           Procesar edición
POST /?url=perfil/cambiarFoto      Cambiar foto AJAX (JSON)
GET  /?url=perfil/verificarCambioCorreo       Verificar cambio email
POST /?url=perfil/verificarCambioCorreo       Procesar verificación
```

### Auditoría (Admin)

```
GET  /?url=audit/historial         Historial cambios
GET  /?url=audit/buscar            API búsqueda (JSON)
```

### Materiales (Gestión de Inventario)

```
GET  /?url=materiales/index        Redirección a lista
GET  /?url=materiales/crear        Formulario crear material
POST /?url=materiales/crear        Crear nuevo material
GET  /?url=materiales/editar&id=1  Formulario editar material
POST /?url=materiales/editar       Procesar edición material
GET  /?url=materiales/detalles&id=1  Ver detalles material
POST /?url=materiales/eliminar     Eliminar material
GET  /?url=materiales/historial_inventario  Historial movimientos
GET  /?url=materiales/buscar       API búsqueda materiales (JSON)
```

---

## 🔍 Búsqueda y Filtrado

### Usuarios

```javascript
// AJAX con búsqueda y filtros
GET /?url=usuarios/buscar?q=juan&estado=1&rol=admin&page=1

Respuesta:
{
    "data": [...],
    "total": 25,
    "page": 1,
    "perPage": 10,
    "totalPages": 3
}
```

### Auditoría

```javascript
GET /?url=audit/buscar?usuario_id=5&accion=crear&fecha_inicio=2025-01-01&page=1

Respuesta:
{
    "data": [...],
    "total": 50,
    "page": 1,
    "perPage": 20,
    "totalPages": 3
}
```

---

## 📱 Diseño Responsive

- ✅ Mobile-first
- ✅ Tablet optimizado
- ✅ Desktop completo
- ✅ Sidebar colapsable
- ✅ Menú hamburguesa en móvil

---

## 🔒 Seguridad

### ✅ Implementado

1. **Hash de contraseñas** - `PASSWORD_DEFAULT` (bcrypt)
2. **Prepared Statements** - Prevención SQL Injection
3. **Sesiones seguras** - Regeneración de ID en login
4. **CSRF Protection** - (Implementar si se requiere)
5. **XSS Prevention** - Escapado en vistas con `htmlspecialchars()`
6. **Control de acceso** - Validación de roles en controladores
7. **Códigos de verificación** - 6 dígitos, expiración 10 min
8. **Cooldown de reenvío** - 90 segundos entre intentos

### ⚠️ TODO - Mejorar

1. **CSRF Tokens** - Agregar a formularios
2. **Rate Limiting** - Limitar intentos de login
3. **Variables de entorno** - Mover credenciales a `.env`
4. **HTTPS** - Usar en producción
5. **Content Security Policy** - Agregar headers
6. **Logging** - Sistema de logs para eventos críticos

---

## 📝 Ejemplos de Código

### Crear Usuario

```php
// UsuariosController::crear()
$userModel = new User();

$nuevoUsuarioId = $userModel->create([
    'nombre'         => 'Juan Pérez',
    'correo'         => 'juan@example.com',
    'nombre_usuario' => 'juanperez',
    'celular'        => '3001234567',
    'cargo'          => 'Empleado',
    'foto'           => 'uploads/fotos/foto_xxx.jpg',
    'password'       => password_hash('MiPassword123!', PASSWORD_DEFAULT),
    'estado'         => 1,
    'rol'            => 'usuario',
]);
```

### Registrar en Auditoría

```php
$auditModel = new Audit();
$auditModel->registrarCambio(
    $usuarioId,
    'usuarios',
    $usuarioId,
    'actualizar',
    [
        'nombre' => [
            'anterior' => 'Juan',
            'nuevo' => 'Juan Carlos'
        ],
        'rol' => [
            'anterior' => 'usuario',
            'nuevo' => 'admin'
        ]
    ],
    $_SESSION['user']['id']  // Admin que hizo el cambio
);
```

### Búsqueda de Usuarios

```php
$userModel = new User();

$usuarios = $userModel->searchUsers(
    $q = 'juan',           // Búsqueda
    $estado = '1',         // Solo activos
    $rol = 'admin',        // Solo admins
    $limit = 10,
    $offset = 0
);

$total = $userModel->countUsersFiltered('juan', '1', 'admin');
```

### Enviar Email

```php
MailHelper::sendCode(
    'usuario@example.com',
    'Código de recuperación',
    '123456',
    'recuperacion'
);
```

### Middleware de Admin

```php
private function requireAdmin() {
    if (($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
        http_response_code(403);
        echo "Acceso denegado";
        exit;
    }
}
```

### Crear Material

```php
// MaterialesController::crear()
$materialModel = new Material();

$materialId = $materialModel->create([
    'nombre'        => 'Tornillo M8',
    'descripcion'   => 'Tornillo de cabeza hexagonal',
    'codigo'        => 'TOR-M8-001',
    'cantidad'      => 1000,
    'unidad'        => 'Unidad',
    'linea_id'      => 5,        // FK a tabla lineas
    'nodo_id'       => 3,        // FK a tabla nodos
    'precio_unit'   => 0.50,
    'estado'        => 1,
    'usuario_id'    => $_SESSION['user']['id']
]);
```

### Actualizar Stock de Material

```php
$materialModel = new Material();

// Restar stock (cuando se consume)
$materialModel->decrementarStock($materialId, 50);

// Sumar stock (cuando llega nuevo)
$materialModel->incrementarStock($materialId, 100);
```

### Obtener Historial de Inventario

```php
// Ver todos los movimientos de un material
$auditModel = new Audit();

$historial = $auditModel->obtenerHistorialCompleto(
    $usuario_id = null,
    $accion = 'actualizar',
    $fecha_inicio = '2025-01-01',
    $fecha_fin = '2025-12-31',
    $limit = 20,
    $offset = 0
);
```

---

## 🐛 Debugging y Logs

### Modo Desarrollo

En `Database.php`:
```php
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
```

Las excepciones se lanzan automáticamente.

### Ver Errores

```php
try {
    // Código
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
```

## 📊 Reportes y Exportación

### **Exportar a Excel**

```php
// Usar ExcelHelper para exportar datos
ExcelHelper::generarReporte($data, 'Inventario');

// Genera archivo Excel con:
// - Encabezados
// - Datos formateados
// - Estilos básicos
// - Descarga automática
```

### **Exportar a PDF**

```php
// Usar PdfHelper para reportes en PDF
PdfHelper::generarReporte($html, 'Reporte_Inventario.pdf');

// Genera PDF profesional con:
// - Encabezados y pie de página
// - Tablas formateadas
// - Imágenes y estilos
```

---

## 📚 Dependencias

```json
{
    "require": {
        "phpmailer/phpmailer": "^7.0",
        "phpoffice/phpspreadsheet": "^1.28",
        "tcpdf/tcpdf": "^6.6"
    }
}
```

- **PHPMailer 7.x** - Envío de emails SMTP
- **PHPSpreadsheet** - Generación de reportes Excel
- **TCPDF** - Generación de reportes PDF

---

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama (`git checkout -b feature/nueva-feature`)
3. Commit cambios (`git commit -am 'Agregar feature'`)
4. Push a la rama (`git push origin feature/nueva-feature`)
5. Abre un Pull Request

---

## 📄 Licencia

Este proyecto es desarrollado como parte del programa de SENA.

---

## 👨‍💻 Autor

**Desarrollador:** Danta Marioso  
**Institución:** SENA  
**Fecha:** Noviembre 2025

---

## 📞 Contacto y Soporte

- **Email:** dantamarioso@gmail.com
- **GitHub:** https://github.com/dantamarioso/proyecto_sena

---

## 🗂️ Historial de Cambios

### v1.1.0 - Actualización (Diciembre 2025)
- ✅ Gestión completa de materiales e inventario
- ✅ Modelos Material, Linea, Nodo, MaterialArchivo
- ✅ Historial de movimientos de inventario
- ✅ Búsqueda y filtrado de materiales
- ✅ Helpers adicionales (Validation, Permission, View, etc)
- ✅ Reportes en Excel y PDF
- ✅ UI mejorada con estilos adicionales
- ✅ Vistas para detalles de usuarios y materiales
- ✅ Historial de inventario con análisis

### v1.0.0 - Inicial
- ✅ Autenticación completa
- ✅ Recuperación de contraseña
- ✅ Registro con verificación
- ✅ Gestión de usuarios
- ✅ Perfil editable
- ✅ Auditoría completa
- ✅ Control de roles

---

## 📋 Checklist de Seguridad Producción

Antes de ir a producción:

- [ ] Cambiar credenciales de BD (no 'root' sin contraseña)
- [ ] Cambiar credenciales SMTP
- [ ] Mover credenciales a variables de entorno (.env)
- [ ] Habilitar HTTPS/SSL
- [ ] Desactivar debug mode
- [ ] Configurar permisos de directorios `chmod 750`
- [ ] Proteger directorios sensibles (config, vendor)
- [ ] Agregar CSRF tokens a formularios
- [ ] Implementar rate limiting en login
- [ ] Configurar sistema de logs
- [ ] Hacer backup automático BD (diario/semanal)
- [ ] Configurar firewalls y WAF
- [ ] Establecer política de contraseñas
- [ ] Implementar 2FA (autenticación de dos factores)
- [ ] Validar uploads de archivo (MIME type, tamaño máx)
- [ ] Scan de seguridad (OWASP Top 10)
- [ ] Tests de penetración
- [ ] Monitoreo de errores (Sentry, etc)
- [ ] CDN para archivos estáticos
- [ ] Caché de aplicación (Redis, Memcached)

---

## 🎓 Notas Educativas

Este proyecto es un **ejemplo educativo** de cómo construir una aplicación web con PHP puro sin framework. Ilustra:

✅ **Patrón MVC** minimalista  
✅ **Conexión a BD** segura con PDO  
✅ **Autenticación** basada en sesiones  
✅ **Validación** de datos  
✅ **Manejo de archivos** (upload)  
✅ **Envío de emails** con PHPMailer  
✅ **Control de acceso** basado en roles  
✅ **Auditoría** de cambios  
✅ **AJAX** para interactividad  
✅ **HTML/CSS/JS** responsivo

---

**¡Gracias por usar el Sistema de Gestión de Usuarios e Inventario!** 🎉
