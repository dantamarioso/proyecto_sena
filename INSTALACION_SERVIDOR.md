# Guía de Instalación - Proyecto Sistema de Inventario

## 📋 Descripción

Sistema web de gestión de usuarios e inventario con PHP puro (sin framework), autenticación, auditoría y gestión de roles.

**Compatible con:**
- ✅ XAMPP (local)
- ✅ Servidores compartidos (hostings)
- ✅ VPS/Servidores dedicados
- ✅ Docker
- ✅ ngrok (para desarrollo)

---

## 🔧 Requisitos del Servidor

```
PHP:        8.0 o superior
MySQL:      5.7 o superior (recomendado 8.0)
Extensiones:
  - PDO MySQL
  - GD (para redimensionamiento de imágenes)
  - JSON (generalmente incluida)
  - Session
  
Espacio en disco: Mínimo 100MB
```

---

## 📦 Opción 1: Instalación en XAMPP (Local)

### Paso 1: Descargar proyecto

```bash
# Clonar o descargar en htdocs
cd C:\xampp\htdocs
git clone https://github.com/dantamarioso/proyecto_sena.git
cd proyecto_sena
```

### Paso 2: Instalar dependencias

```bash
composer install
```

### Paso 3: Configurar base de datos

Editar `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventario_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // tu contraseña
```

### Paso 4: Crear base de datos

1. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Crear base de datos: `inventario_db`
3. Importar `database/migrations/schema.sql` (si existe)

O ejecutar las tablas manualmente:

```sql
-- Ver archivos en database/migrations/
```

### Paso 5: Iniciar servidor

1. Abrir XAMPP Control Panel
2. Iniciar Apache y MySQL
3. Acceder: `http://localhost/proyecto_sena/public`

---

## 🌐 Opción 2: Instalación en Servidor Compartido (Hosting)

### Paso 1: Subir archivos

Usar FTP/SFTP para subir el proyecto a la carpeta pública (generalmente `public_html/` o `www/`):

```
tu-dominio.com/
└── proyecto_sena/
    ├── app/
    ├── config/
    ├── database/
    ├── public/
    ├── vendor/
    └── composer.json
```

### Paso 2: Configurar base de datos

1. Acceder al panel de control del hosting (cPanel, Plesk, etc.)
2. Crear nueva base de datos MySQL
3. Crear usuario y asignar permisos: `SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP`

### Paso 3: Editar configuración

Modificar `config/config.php`:

```php
define('DB_HOST', 'localhost'); // o IP/hostname del servidor
define('DB_NAME', 'tu_usuario_inventario_db'); // nombre de la BD
define('DB_USER', 'tu_usuario_bd');
define('DB_PASS', 'tu_contraseña_bd');
```

### Paso 4: Importar schema

Usar phpMyAdmin del hosting para importar tablas desde `database/migrations/`

### Paso 5: Ajustar permisos

```bash
# Via SSH
chmod 755 public/
chmod 755 public/uploads/
chmod 755 public/uploads/fotos/
chmod 755 public/uploads/materiales/

# Crear directorios si no existen
mkdir -p public/uploads/fotos
mkdir -p public/uploads/materiales
chmod 777 public/uploads/fotos
chmod 777 public/uploads/materiales
```

### Paso 6: Acceder

```
https://tu-dominio.com/proyecto_sena/public
```

---

## 🐳 Opción 3: Instalación en Docker

Crear `docker-compose.yml`:

```yaml
version: '3.8'

services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: inventario_db
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql

  php:
    image: php:8.2-apache
    ports:
      - "80:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
    environment:
      - DB_HOST=mysql

volumes:
  mysql_data:
```

Ejecutar:

```bash
docker-compose up -d
docker exec php composer install
```

Acceder: `http://localhost/proyecto_sena/public`

---

## 🚀 Opción 4: VPS/Servidor Dedicado (Ubuntu/Debian)

### Paso 1: Instalar stack

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Apache
sudo apt install apache2 -y

# Instalar PHP 8.2
sudo apt install php8.2 php8.2-cli php8.2-mysql php8.2-gd php8.2-json -y

# Instalar MySQL
sudo apt install mysql-server -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Paso 2: Configurar Apache

Crear archivo `/etc/apache2/sites-available/proyecto_sena.conf`:

```apache
<VirtualHost *:80>
    ServerName tu-dominio.com
    ServerAlias www.tu-dominio.com
    
    DocumentRoot /var/www/proyecto_sena/public
    
    <Directory /var/www/proyecto_sena/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/proyecto_sena_error.log
    CustomLog ${APACHE_LOG_DIR}/proyecto_sena_access.log combined
</VirtualHost>
```

Habilitar:

```bash
sudo a2enmod rewrite
sudo a2ensite proyecto_sena
sudo systemctl restart apache2
```

### Paso 3: Instalar proyecto

```bash
cd /var/www
git clone https://github.com/dantamarioso/proyecto_sena.git
cd proyecto_sena
composer install

# Permisos
sudo chown -R www-data:www-data /var/www/proyecto_sena
sudo chmod -R 755 /var/www/proyecto_sena
sudo chmod -R 777 /var/www/proyecto_sena/public/uploads
```

### Paso 4: Configurar BD

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE inventario_db;
CREATE USER 'inventario_user'@'localhost' IDENTIFIED BY 'contraseña_fuerte';
GRANT ALL PRIVILEGES ON inventario_db.* TO 'inventario_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Paso 5: SSL (HTTPS)

Usar Certbot:

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d tu-dominio.com
```

---

## 🔐 Configuración de Seguridad Recomendada

### 1. Archivo `.htaccess` en raíz

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Prevenir acceso directo a app/
    RewriteRule ^app/ - [F]
    RewriteRule ^config/ - [F]
    RewriteRule ^database/ - [F]
    RewriteRule ^vendor/ - [F]
</IfModule>
```

### 2. Variables de entorno (opcional)

Crear `.env` en la raíz:

```
DB_HOST=localhost
DB_NAME=inventario_db
DB_USER=usuario
DB_PASS=contraseña
APP_ENV=production
APP_DEBUG=false
```

### 3. Limitar upload de archivos

En `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 60
```

### 4. Firewall

- Permitir solo puertos: 80 (HTTP), 443 (HTTPS), 3306 (MySQL - solo local)
- Bloquear acceso directo a archivos de configuración
- Usar HTTPS obligatorio

---

## 📊 Creación de Base de Datos

Ejecutar SQL desde `database/migrations/` o phpMyAdmin:

**Tablas principales:**
- `usuarios` - Gestión de usuarios
- `nodos` - Ubicaciones/departamentos
- `lineas` - Líneas de productos
- `materiales` - Inventario
- `movimientos_inventario` - Entrada/salida
- `material_archivos` - Documentos adjuntos
- `auditoria` - Registro de cambios
- `linea_nodo` - Relaciones

---

## 🧪 Verificar Instalación

### Acceso inicial

```
URL: http://tu-dominio.com/proyecto_sena/public/?url=auth/login
Usuario: admin@example.com
Contraseña: (según base de datos)
```

### Archivos de debug

```
http://tu-dominio.com/proyecto_sena/public/debug.php
```

Muestra:
- Versión PHP
- Conexión a BD
- Headers HTTP
- Protocolo detectado (HTTP/HTTPS)

---

## 🐛 Troubleshooting

### Error: "Base de datos no encontrada"

```php
// Verificar config/config.php
define('DB_HOST', 'localhost'); // ¿Correctamente configurado?
define('DB_NAME', 'inventario_db');
```

### Error: "Permiso denegado" en carpeta uploads

```bash
chmod 777 public/uploads/fotos
chmod 777 public/uploads/materiales
```

### Error: "Controlador no encontrado"

- Verificar que las rutas estén bien en la URL
- Revisar logs: `error_log.txt` en la raíz

### Página en blanco

- Ver `error_log.txt`
- Verificar permisos de archivos (mínimo 644 para archivos, 755 para directorios)
- Activar `display_errors` temporalmente en `public/index.php`

---

## 🔄 Actualizar Proyecto

```bash
cd /ruta/proyecto_sena
git pull origin main
composer install --no-dev
# Realizar backups de BD
```

---

## 📞 Soporte

- Logs: `error_log.txt` en la raíz del proyecto
- Revisar `debug.php` para diagnosticar problemas
- Verificar permisos de archivos y directorios

---

**Última actualización:** 2024
**Versión:** 1.0
**Autor:** Sistema de Inventario SENA
