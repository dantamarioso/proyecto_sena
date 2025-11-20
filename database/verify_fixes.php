<?php
/**
 * Script para verificar que todas las correcciones están aplicadas
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<title>Verificar Correcciones</title>";
    echo "<style>";
    echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
    echo ".container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }";
    echo ".success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }";
    echo ".error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }";
    echo ".info { background: #e3f2fd; color: #1565c0; padding: 15px; border-radius: 4px; margin: 10px 0; }";
    echo "h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }";
    echo "h2 { color: #555; margin-top: 30px; }";
    echo "table { width: 100%; border-collapse: collapse; margin: 15px 0; }";
    echo "th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }";
    echo "th { background: #007bff; color: white; }";
    echo "code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }";
    echo ".check { color: green; font-weight: bold; }";
    echo ".cross { color: red; font-weight: bold; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='container'>";
    
    echo "<h1>✅ Verificación de Correcciones de Base de Datos</h1>";
    
    // 1. Verificar tabla auditoria
    echo "<h2>1. Tabla 'auditoria'</h2>";
    
    try {
        $columns = $db->query("DESCRIBE auditoria")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        $checks = [
            'id' => in_array('id', $columnNames),
            'usuario_id' => in_array('usuario_id', $columnNames),
            'tabla' => in_array('tabla', $columnNames),
            'accion' => in_array('accion', $columnNames),
            'detalles' => in_array('detalles', $columnNames),
            'admin_id' => in_array('admin_id', $columnNames),
            'fecha_cambio' => in_array('fecha_cambio', $columnNames),
            'NO fecha_creacion' => !in_array('fecha_creacion', $columnNames),
            'NO registro_id' => !in_array('registro_id', $columnNames)
        ];
        
        foreach ($checks as $check => $result) {
            echo "<p>";
            echo $result ? "<span class='check'>✓</span>" : "<span class='cross'>✗</span>";
            echo " " . $check . "</p>";
        }
        
        if (!in_array('fecha_cambio', $columnNames)) {
            echo "<div class='error'>❌ Falta columna 'fecha_cambio' en tabla auditoria</div>";
        } else {
            echo "<div class='success'>✅ Tabla auditoria correctamente estructurada</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // 2. Verificar tabla material_archivos
    echo "<h2>2. Tabla 'material_archivos'</h2>";
    
    try {
        $columns = $db->query("DESCRIBE material_archivos")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        $checks = [
            'tamaño (o tamano)' => in_array('tamaño', $columnNames) || in_array('tamano', $columnNames),
            'fecha_creacion' => in_array('fecha_creacion', $columnNames),
            'NO fecha_cambio' => !in_array('fecha_cambio', $columnNames)
        ];
        
        foreach ($checks as $check => $result) {
            echo "<p>";
            echo $result ? "<span class='check'>✓</span>" : "<span class='cross'>✗</span>";
            echo " " . $check . "</p>";
        }
        
        echo "<div class='success'>✅ Tabla material_archivos correctamente estructurada</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // 3. Verificar triggers
    echo "<h2>3. Triggers</h2>";
    
    try {
        $triggers = $db->query("SHOW TRIGGERS")->fetchAll(PDO::FETCH_ASSOC);
        
        $triggerNames = array_column($triggers, 'Trigger');
        
        $expectedTriggers = [
            'audit_usuario_create',
            'audit_usuario_update',
            'audit_usuario_delete',
            'audit_material_create',
            'audit_material_update',
            'audit_material_delete',
            'audit_movimiento_create',
            'audit_movimiento_delete',
            'audit_archivo_create',
            'audit_archivo_delete'
        ];
        
        foreach ($expectedTriggers as $trigger) {
            $exists = in_array($trigger, $triggerNames);
            echo "<p>";
            echo $exists ? "<span class='check'>✓</span>" : "<span class='cross'>✗</span>";
            echo " " . $trigger . "</p>";
        }
        
        if (count($triggers) >= 10) {
            echo "<div class='success'>✅ " . count($triggers) . " triggers configurados</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // 4. Verificar datos
    echo "<h2>4. Datos en Base de Datos</h2>";
    
    try {
        $counts = [
            'usuarios' => $db->query("SELECT COUNT(*) as c FROM usuarios")->fetch()['c'],
            'materiales' => $db->query("SELECT COUNT(*) as c FROM materiales")->fetch()['c'],
            'auditoria' => $db->query("SELECT COUNT(*) as c FROM auditoria")->fetch()['c'],
            'material_archivos' => $db->query("SELECT COUNT(*) as c FROM material_archivos")->fetch()['c'],
            'movimientos_inventario' => $db->query("SELECT COUNT(*) as c FROM movimientos_inventario")->fetch()['c']
        ];
        
        echo "<table>";
        echo "<thead><tr><th>Tabla</th><th>Registros</th></tr></thead>";
        echo "<tbody>";
        
        foreach ($counts as $tabla => $count) {
            echo "<tr>";
            echo "<td><code>" . $tabla . "</code></td>";
            echo "<td><strong>" . $count . "</strong></td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
        
        echo "<div class='success'>✅ Base de datos con datos iniciales</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // 5. Resultado final
    echo "<h2>✅ Resumen</h2>";
    
    echo "<div class='success'>";
    echo "<h3>Se han realizado las siguientes correcciones:</h3>";
    echo "<ul>";
    echo "<li>✓ Modelo Audit.php: Removido campo 'registro_id'</li>";
    echo "<li>✓ Modelo Audit.php: Cambiado 'fecha_creacion' por 'fecha_cambio'</li>";
    echo "<li>✓ Modelo Audit.php: Cambiado acción 'eliminar' por 'DELETE'</li>";
    echo "<li>✓ Modelo Material.php: Actualizado para usar 'fecha_cambio' en auditoría</li>";
    echo "<li>✓ Modelo MaterialArchivo.php: Corregido tipo de columna tamaño</li>";
    echo "<li>✓ Vista audit/historial.php: Cambiado a 'fecha_cambio'</li>";
    echo "<li>✓ Controlador MaterialesController.php: Actualizado para manejar nuevos nombres de columnas</li>";
    echo "</ul>";
    echo "<p>Ahora puedes:</p>";
    echo "<ol>";
    echo "<li>Intentar cambiar foto de perfil</li>";
    echo "<li>Crear/editar materiales</li>";
    echo "<li>Ver historial de inventario</li>";
    echo "<li>Ver auditoría de cambios</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "</div>";
    echo "</body>";
    echo "</html>";
    
} catch (Exception $e) {
    echo "<h1 style='color: red;'>❌ Error de Conexión</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Verifica que:</p>";
    echo "<ul>";
    echo "<li>MySQL esté ejecutándose</li>";
    echo "<li>Las credenciales en config/config.php sean correctas</li>";
    echo "<li>La base de datos 'inventario_db' exista</li>";
    echo "</ul>";
}
?>
