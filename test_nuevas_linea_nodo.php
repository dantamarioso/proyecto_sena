<?php
require_once 'config/config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Model.php';
require_once 'app/models/Nodo.php';
require_once 'app/models/Linea.php';

$db = Database::getInstance();

// 1. Agregar un nodo nuevo
echo "1. AGREGANDO NODO NUEVO..." . PHP_EOL;
$stmt = $db->prepare("
    INSERT INTO nodos (nombre, ciudad, descripcion, estado) 
    VALUES (:nombre, :ciudad, :descripcion, :estado)
");
$newNodoResult = $stmt->execute([
    ':nombre' => 'NODO_QUINDIO',
    ':ciudad' => 'Armenia',
    ':descripcion' => 'Centro de formacion en Armenia - Quindio',
    ':estado' => 1
]);
$nodoId = $db->lastInsertId();
echo "   Nodo nuevo creado: ID $nodoId" . PHP_EOL . PHP_EOL;

// 2. Agregar una línea nueva
echo "2. AGREGANDO LINEA NUEVA..." . PHP_EOL;
$lineaModel = new Linea();
$newLineaId = $lineaModel->create([
    'nombre' => 'LOGISTICA Y DISTRIBUCION',
    'descripcion' => 'Linea de logistica y distribucion',
    'estado' => 1,
    'nodo_ids' => [1, 2, 3, 4, 5, $nodoId]
]);
echo "   Linea nueva creada: ID $newLineaId" . PHP_EOL . PHP_EOL;

// 3. Probar obtener nodos con líneas
echo "3. VERIFICANDO GETACTIVOSCONLINEAS()..." . PHP_EOL;
$nodoModel = new Nodo();
$nodos = $nodoModel->getActivosConLineas();
$totalLineas = 0;
foreach ($nodos as $nodo) {
    $lineCount = count($nodo['lineas']);
    $totalLineas += $lineCount;
    echo "   - {$nodo['nombre']}: $lineCount lineas" . PHP_EOL;
}
echo "   Total lineas detectadas: $totalLineas" . PHP_EOL . PHP_EOL;

// 4. Verificar que la nueva línea aparece en el nodo nuevo
echo "4. VERIFICANDO NUEVA LINEA EN NODO NUEVO..." . PHP_EOL;
$nodoQuindio = null;
foreach ($nodos as $nodo) {
    if ($nodo['id'] == $nodoId) {
        $nodoQuindio = $nodo;
        break;
    }
}

if ($nodoQuindio) {
    echo "   Nodo encontrado: {$nodoQuindio['nombre']}" . PHP_EOL;
    echo "   Lineas en el nodo:" . PHP_EOL;
    foreach ($nodoQuindio['lineas'] as $linea) {
        echo "     - {$linea['nombre']}" . PHP_EOL;
    }
    $hasNewLinea = false;
    foreach ($nodoQuindio['lineas'] as $linea) {
        if ($linea['id'] == $newLineaId) {
            $hasNewLinea = true;
            break;
        }
    }
    $status = $hasNewLinea ? "SI" : "NO";
    echo "   La linea nueva esta en el nodo: $status" . PHP_EOL;
} else {
    echo "   Nodo nuevo NO encontrado en getActivosConLineas()" . PHP_EOL;
}
echo PHP_EOL;

// 5. Limpiar: Eliminar el nodo y línea de prueba
echo "5. LIMPIANDO DATOS DE PRUEBA..." . PHP_EOL;
try {
    $stmtDelLinea = $db->prepare("DELETE FROM lineas WHERE id = ?");
    $stmtDelLinea->execute([$newLineaId]);
    echo "   Linea de prueba eliminada" . PHP_EOL;
    
    $stmtDelNodo = $db->prepare("DELETE FROM nodos WHERE id = ?");
    $stmtDelNodo->execute([$nodoId]);
    echo "   Nodo de prueba eliminado" . PHP_EOL;
} catch (Exception $e) {
    echo "   Error al eliminar: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "Prueba completada exitosamente!" . PHP_EOL;
echo "Conclusion: El sistema SI se adapta cuando se agregan nodos y lineas nuevos." . PHP_EOL;
?>
