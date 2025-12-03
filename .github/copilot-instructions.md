# Instrucciones para Agentes IA - Proyecto SENA

## Descripción General
Sistema de gestión de usuarios e inventario desarrollado con PHP puro (sin framework), utilizando patrón MVC básico. Incluye autenticación, recuperación de contraseña, auditoría de cambios y gestión de roles.

## Arquitectura Core

### Patrón MVC Minimal
- **Controller**: Clase base en `app/core/Controller.php` que proporciona `view()` y `redirect()`
- **Model**: Clase base en `app/core/Model.php` que inyecta instancia de BD
- **View**: Templates PHP en `app/views/` con acceso directo a variables extraídas por `extract()`

### Enrutamiento
Router basado en URL query parameter: `index.php?url=controller/method`
- URL `?url=auth/login` → `AuthController->login()`
- Autocarga simple en `public/index.php` usando `spl_autoload_register()`
- Convención: controlador singular → `AuthController`, `UsuariosController`

### Conexión a Base de Datos
- **Singleton Pattern**: `Database::getInstance()` retorna única instancia PDO
- **DSN**: MySQL con charset utf8mb4, modo excepciones activado
- **Credenciales**: En `config/config.php` (definidas, no variables de entorno)
- **Uso**: `$this->db` en modelos (heredado de clase base Model)

## Patrones Clave del Proyecto

### Autenticación y Sesiones
- Sesión iniciada en `public/index.php` con `session_start()`
- Usuario guardado en `$_SESSION['user']` como array: `['id', 'nombre', 'cargo', 'foto', 'rol']`
- **Estados**: `rol` puede ser 'admin', 'usuario', 'invitado'
- **Control de acceso**: `$_SESSION['user']['rol'] === 'admin'` en métodos sensibles
- Regeneración de ID en login (`session_regenerate_id(true)`)

### Flujo de Recuperación de Contraseña
1. `AuthController->forgot()` → formulario
2. `AuthController->sendCode()` → genera código 6 dígitos, expira en 10 min
3. `AuthController->verifyCode()` → valida código
4. `AuthController->resetPassword()` → actualiza contraseña
- **Helpers**: `User->saveRecoveryCode()`, `User->verifyCode()`, cooldown de 90s antes de reenvío

### Flujo de Registro con Verificación de Email
1. `AuthController->register()` → crea usuario con `email_verified = 0`
2. Envía código de verificación a correo
3. `AuthController->verifyEmail()` → usuario ingresa código
4. `User->markEmailAsVerified()` → marca como verificado
- Códigos expiran en 10 minutos, mismo cooldown de 90s para reenvío

### Gestión de Usuarios (Admin)
- `UsuariosController->crear()` → validaciones, upload de foto, hash password
- `UsuariosController->gestionDeUsuarios()` → lista con filtros de búsqueda
- `User->searchUsers()` → búsqueda por nombre/correo/usuario + filtros estado/rol
- Paginación manual con `LIMIT` y `OFFSET`
- Foto guardada en `public/uploads/fotos/` con nombre único `uniqid()`

### Auditoría de Cambios
- `Audit` model registra cambios en tabla `auditoria`
- `registrarCambio()` guarda: usuario_id, tabla, accion, detalles (JSON), admin_id, timestamp
- `obtenerHistorialCompleto()` con filtros por usuario, acción, rango de fechas
- `AuditController->historial()` expone el historial

### Perfil de Usuario
- `PerfilController->ver()` → vista del perfil personal con foto grande
- `PerfilController->editar()` → editar datos personales (usuarios pueden editar los propios, admin puede editar cualquiera)
- `PerfilController->cambiarFoto()` → endpoint AJAX para cambiar foto desde sidebar (click en avatar)
- Usuarios no-admin solo pueden cambiar: nombre, correo, usuario, celular, cargo, contraseña, foto
- Admins pueden cambiar además: rol, estado
- Cambios registrados automáticamente en auditoría
- Foto de perfil interactiva con overlay en `ver.php`

## Convenciones de Código

### Validación
- Email: `filter_var($correo, FILTER_VALIDATE_EMAIL)`
- Contraseña: mín 8 caracteres, 1 mayúscula, 1 carácter especial
- Celular, cargo: opcionales en registro
- Estado: 0 (inactivo), 1 (activo)

### Hashing de Contraseña
- Usar siempre `password_hash($password, PASSWORD_DEFAULT)`
- Verificar con `password_verify($input, $stored_hash)`
- Nunca guardar contraseña en texto plano

### SQL y Prepared Statements
- SIEMPRE usar prepared statements con `?` o `:named_placeholders`
- Binding manual para integers: `$stmt->bindValue(':id', $id, PDO::PARAM_INT)`
- `fetchAll(PDO::FETCH_ASSOC)` para múltiples registros, `fetch()` para uno

### Upload de Archivos
- Validar extensión: `pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)`
- Validar MIME type si es crítico
- Crear directorio si no existe: `mkdir(..., 0777, true)`
- Usar `uniqid()` + extensión para nombres únicos
- Ruta relativa en BD, ruta absoluta para `move_uploaded_file()`

### Estructura de Vistas
- Template engine: extractión de variables con `extract($data)`
- Layout base: `header.php` → contenido → `footer.php`
- CSS/JS específicas: pasadas en array `$pageStyles`, `$pageScripts` desde controller
- Variable `$isLoginPage` para renderizado condicional

### Correos (PHPMailer)
- Helper centralizado: `MailHelper::sendCode()` 
- SMTP: Gmail con credenciales en helper (REVISAR: credenciales hardcodeadas)
- Método: `sendCode($correo, $asunto, $mensaje)` → retorna bool

## Flujos Comunes de Desarrollo

### Crear Nuevo Endpoint
1. Agregar método a Controller heredando de `Controller`
2. Crear view en `app/views/[controller]/[method].php`
3. Inyectar datos con `$this->view('path', ['data' => $value])`
4. URL accesible como `?url=controller/method`

### Agregar Modelo
1. Crear clase en `app/models/` heredando de `Model`
2. Acceder a BD via `$this->db->prepare()` y `execute()`
3. Importar en controller con `new ModelClass()`

### Validar Rol Admin
```php
private function requireAdmin() {
    if (($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
        http_response_code(403);
        echo "Acceso denegado.";
        exit;
    }
}
```

## Dependencias Externas
- **PHPMailer 7.x**: Envío de emails via SMTP (composer)
- **Composer autoload**: Requiere `vendor/autoload.php`

## Datos Sensibles
- Credenciales BD y SMTP en `config/config.php` y `MailHelper.php` (hardcodeadas)
- **TODO**: Migrar a variables de entorno o .env file

## Archivos Clave
- `public/index.php` - Router principal
- `app/core/` - Clases base (Controller, Model, Database)
- `app/controllers/AuthController.php` - Autenticación completa
- `app/controllers/PerfilController.php` - Gestión de perfil de usuario
- `app/models/User.php` - Lógica de usuarios (60+ métodos)
- `config/config.php` - Configuración de BD
- `app/views/layouts/sidebar.php` - Menú lateral con enlace a perfil
