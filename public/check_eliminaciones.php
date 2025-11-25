<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = Database::getInstance();

// Verificar últimas eliminaciones de materiales
echo "<h2>Últimas 10 eliminaciones en auditoria_materiales</h2>";
$sql = "SELECT 
    a.id,
    a.material_id,
    a.admin_id,
    a.accion,
    a.fecha_cambio,
    u.nombre as usuario_nombre,
    u.foto as usuario_foto,
    a.detalles
FROM auditoria_materiales a
LEFT JOIN usuarios u ON a.admin_id = u.id
WHERE a.accion = 'eliminar'
ORDER BY a.fecha_cambio DESC
LIMIT 10";

$stmt = $db->prepare($sql);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID Audit</th><th>Material ID</th><th>Admin ID</th><th>Admin Nombre</th><th>Acción</th><th>Fecha</th><th>Detalles</th></tr>";

foreach ($resultados as $row) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['material_id'] . "</td>";
    echo "<td>" . ($row['admin_id'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['usuario_nombre'] ?? 'SIN USUARIO') . "</td>";
    echo "<td>" . $row['accion'] . "</td>";
    echo "<td>" . $row['fecha_cambio'] . "</td>";
    $detalles = json_decode($row['detalles'], true);
    echo "<td><pre>" . json_encode($detalles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre></td>";
    echo "</tr>";
}
echo "</table>";

// Verificar estructura de tabla
echo "<h2>Estructura de auditoria_materiales</h2>";
$sql_structure = "DESCRIBE auditoria_materiales";
$stmt = $db->prepare($sql_structure);
$stmt->execute();
$estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
foreach ($estructura as $col) {
    echo "<tr>";
    echo "<td>" . $col['Field'] . "</td>";
    echo "<td>" . $col['Type'] . "</td>";
    echo "<td>" . $col['Null'] . "</td>";
    echo "<td>" . (isset($col['Key']) ? $col['Key'] : '') . "</td>";
    echo "<td>" . (isset($col['Default']) ? $col['Default'] : '') . "</td>";
    echo "<td>" . (isset($col['Extra']) ? $col['Extra'] : '') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
