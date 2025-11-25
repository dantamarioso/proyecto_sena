<?php

// Leer los últimos errores del error_log.txt
$logFile = __DIR__ . '/../error_log.txt';

if (!file_exists($logFile)) {
    die('No existe error_log.txt en ' . $logFile);
}

$content = file_get_contents($logFile);
$lines = explode("\n", trim($content));

// Tomar últimas 100 líneas
$ultimas = array_slice($lines, -100);

// Filtrar líneas relacionadas con subirArchivo
$subirArchivo = array_filter($ultimas, function($line) {
    return strpos($line, 'subirArchivo') !== false || 
           strpos($line, 'EXCEPCIÓN') !== false ||
           strpos($line, 'INICIO') !== false ||
           strpos($line, 'FIN') !== false;
});

?>
<!DOCTYPE html>
<html>
<head>
    <title>Error Log Viewer</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
        .container { max-width: 1200px; }
        h1 { color: #fff; }
        .log-line { padding: 5px; white-space: pre-wrap; word-wrap: break-word; }
        .error { color: #ff6b6b; }
        .success { color: #51cf66; }
        .warning { color: #ffd93d; }
        .info { color: #6b9cff; }
        .refresh { margin-bottom: 20px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h1>Error Log - Últimas Líneas de subirArchivo</h1>
    <div class="refresh">
        <button onclick="location.reload()">Refrescar</button>
        <span style="margin-left: 20px;">Total líneas en log: <?= count($lines) ?></span>
    </div>
    
    <h2>Líneas de subirArchivo (últimas 100 líneas):</h2>
    <?php if (empty($subirArchivo)): ?>
        <div class="log-line warning">No se encontraron líneas de subirArchivo</div>
    <?php else: ?>
        <?php foreach ($subirArchivo as $line): ?>
            <div class="log-line <?= 
                (strpos($line, 'EXCEPCIÓN') !== false ? 'error' :
                (strpos($line, 'SUCCESS') !== false ? 'success' :
                (strpos($line, 'FAIL') !== false ? 'error' :
                (strpos($line, 'FIN') !== false ? 'warning' : 'info'))))
            ?>">
                <?= htmlspecialchars($line) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <h2 style="margin-top: 40px;">Todas las últimas líneas:</h2>
    <?php foreach ($ultimas as $line): ?>
        <div class="log-line info"><?= htmlspecialchars($line) ?></div>
    <?php endforeach; ?>
</div>
</body>
</html>
