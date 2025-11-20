#!/usr/bin/env php
<?php
/**
 * Script de prueba para la funcionalidad de archivos en materiales
 * Uso: php test_archivos.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/models/MaterialArchivo.php';

echo "=== TEST: Sistema de Archivos en Materiales ===\n\n";

// Test 1: Verificar tabla existe
echo "1. Verificando tabla material_archivos...\n";
try {
    $db = Database::getInstance();
    $result = $db->query("SHOW TABLES LIKE 'material_archivos'");
    $tables = $result->fetchAll();
    if (!empty($tables)) {
        echo "   ✓ Tabla existe\n";
    } else {
        echo "   ✗ Tabla no existe\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Verificar columnas
echo "\n2. Verificando estructura de tabla...\n";
try {
    $result = $db->query("DESCRIBE material_archivos");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $requiredColumns = ['id', 'material_id', 'nombre_original', 'nombre_archivo', 'tamaño', 'usuario_id', 'fecha_creacion'];
    
    $columnNames = array_column($columns, 'Field');
    $missing = array_diff($requiredColumns, $columnNames);
    
    if (empty($missing)) {
        echo "   ✓ Todas las columnas requeridas existen\n";
    } else {
        echo "   ✗ Faltan columnas: " . implode(', ', $missing) . "\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Verificar modelo
echo "\n3. Verificando clase MaterialArchivo...\n";
try {
    $modelo = new MaterialArchivo();
    if (method_exists($modelo, 'getByMaterial')) {
        echo "   ✓ Método getByMaterial existe\n";
    } else {
        echo "   ✗ Método getByMaterial no existe\n";
        exit(1);
    }
    
    if (method_exists($modelo, 'create')) {
        echo "   ✓ Método create existe\n";
    } else {
        echo "   ✗ Método create no existe\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Verificar directorio de uploads
echo "\n4. Verificando directorio de uploads...\n";
$uploadDir = __DIR__ . '/public/uploads/materiales';
if (is_dir($uploadDir)) {
    echo "   ✓ Directorio existe: $uploadDir\n";
    if (is_writable($uploadDir)) {
        echo "   ✓ Directorio tiene permisos de escritura\n";
    } else {
        echo "   ⚠ Directorio NO tiene permisos de escritura\n";
    }
} else {
    echo "   ✗ Directorio no existe: $uploadDir\n";
    exit(1);
}

// Test 5: Verificar controlador
echo "\n5. Verificando métodos en MaterialesController...\n";
try {
    require_once __DIR__ . '/app/controllers/MaterialesController.php';
    $controller = new MaterialesController();
    
    if (method_exists($controller, 'subirArchivo')) {
        echo "   ✓ Método subirArchivo existe\n";
    } else {
        echo "   ✗ Método subirArchivo no existe\n";
        exit(1);
    }
    
    if (method_exists($controller, 'eliminarArchivo')) {
        echo "   ✓ Método eliminarArchivo existe\n";
    } else {
        echo "   ✗ Método eliminarArchivo no existe\n";
        exit(1);
    }
    
    if (method_exists($controller, 'obtenerArchivos')) {
        echo "   ✓ Método obtenerArchivos existe\n";
    } else {
        echo "   ✗ Método obtenerArchivos no existe\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 6: Verificar helpers
echo "\n6. Verificando ViewHelpers...\n";
try {
    require_once __DIR__ . '/app/helpers/ViewHelpers.php';
    if (function_exists('formatearBytes')) {
        echo "   ✓ Función formatearBytes existe\n";
        $test = formatearBytes(1024);
        echo "   ✓ Prueba: 1024 bytes = $test\n";
    } else {
        echo "   ✗ Función formatearBytes no existe\n";
        exit(1);
    }
    
    if (function_exists('obtenerIconoArchivo')) {
        echo "   ✓ Función obtenerIconoArchivo existe\n";
    } else {
        echo "   ✗ Función obtenerIconoArchivo no existe\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 7: Verificar vistas
echo "\n7. Verificando vistas...\n";
$vistasRequeridas = [
    'app/views/materiales/partials/archivos.php',
];

foreach ($vistasRequeridas as $vista) {
    $path = __DIR__ . '/' . $vista;
    if (file_exists($path)) {
        echo "   ✓ Vista existe: $vista\n";
    } else {
        echo "   ✗ Vista no existe: $vista\n";
        exit(1);
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✓ TODOS LOS TESTS PASARON\n";
echo "Sistema de archivos en materiales listo para usar\n";
echo str_repeat("=", 50) . "\n";
?>
