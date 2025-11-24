<?php
/**
 * Script de depuración - Verificar headers y configuración
 * Accede a: https://your-ngrok-url/proyecto_sena/public/debug.php
 */

// No usar la configuración normal, solo mostrar headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Debug - Headers y Configuración</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .highlight { background-color: #fff3cd; }
        code { background-color: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍 Debug - Sistema de Inventario</h1>
    
    <div class="section">
        <h2>Información del Servidor</h2>
        <table>
            <tr>
                <th>Propiedad</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>PHP Version</td>
                <td><?= phpversion() ?></td>
            </tr>
            <tr>
                <td>Server Software</td>
                <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td>Server Protocol</td>
                <td><?= $_SERVER['SERVER_PROTOCOL'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td>HTTP Host</td>
                <td><?= $_SERVER['HTTP_HOST'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td>Remote Address</td>
                <td><?= $_SERVER['REMOTE_ADDR'] ?? 'N/A' ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Detección de Protocolo</h2>
        <table>
            <tr>
                <th>Header/Variable</th>
                <th>Valor</th>
                <th>Estado</th>
            </tr>
            <tr <?= !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'class="highlight"' : '' ?>>
                <td>$_SERVER['HTTPS']</td>
                <td><?= $_SERVER['HTTPS'] ?? 'No definido' ?></td>
                <td><?= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '<span class="success">✓ HTTPS</span>' : 'N/A' ?></td>
            </tr>
            <tr <?= !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? 'class="highlight"' : '' ?>>
                <td>$_SERVER['HTTP_X_FORWARDED_PROTO']</td>
                <td><?= $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'No definido' ?></td>
                <td><?= !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? '<span class="success">✓ ngrok/Proxy</span>' : 'N/A' ?></td>
            </tr>
            <tr <?= !empty($_SERVER['HTTP_CF_VISITOR']) ? 'class="highlight"' : '' ?>>
                <td>$_SERVER['HTTP_CF_VISITOR']</td>
                <td><?= $_SERVER['HTTP_CF_VISITOR'] ?? 'No definido' ?></td>
                <td><?= !empty($_SERVER['HTTP_CF_VISITOR']) ? '<span class="success">✓ Cloudflare</span>' : 'N/A' ?></td>
            </tr>
            <tr>
                <td>$_SERVER['SERVER_PORT']</td>
                <td><?= $_SERVER['SERVER_PORT'] ?? 'N/A' ?></td>
                <td><?= $_SERVER['SERVER_PORT'] === '443' ? '<span class="success">✓ HTTPS (puerto 443)</span>' : 'HTTP (puerto ' . $_SERVER['SERVER_PORT'] . ')' ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>BASE_URL Detectado</h2>
        <?php
        $protocol = 'http';
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $protocol = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
        } elseif (!empty($_SERVER['HTTP_CF_VISITOR'])) {
            $cf_visitor = json_decode($_SERVER['HTTP_CF_VISITOR']);
            if ($cf_visitor && isset($cf_visitor->scheme)) {
                $protocol = strtolower($cf_visitor->scheme);
            }
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } elseif (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443') {
            $protocol = 'https';
        }
        
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base_url = $protocol . '://' . $host . '/proyecto_sena/public';
        ?>
        <p><strong>Protocolo detectado:</strong> <code><?= $protocol ?></code></p>
        <p><strong>Host:</strong> <code><?= $host ?></code></p>
        <p><strong>BASE_URL:</strong> <code><?= $base_url ?></code></p>
        <p class="<?= $protocol === 'https' ? 'success' : 'warning' ?>">
            <?= $protocol === 'https' ? '✓ HTTPS Detectado Correctamente' : '⚠ HTTP Detectado (esperar HTTPS)' ?>
        </p>
    </div>

    <div class="section">
        <h2>Todos los Headers HTTP</h2>
        <table>
            <tr>
                <th>Header</th>
                <th>Valor</th>
            </tr>
            <?php
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header_name = str_replace('HTTP_', '', $key);
                    $header_name = str_replace('_', '-', $header_name);
                    echo "<tr>";
                    echo "<td><code>$header_name</code></td>";
                    echo "<td>" . htmlspecialchars(is_array($value) ? json_encode($value) : $value) . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>
    </div>

    <div class="section">
        <h2>Test de Fetch (JavaScript)</h2>
        <button onclick="testFetch()">Probar Fetch HTTPS</button>
        <div id="resultado" style="margin-top: 10px; padding: 10px; background-color: #f9f9f9; border-radius: 3px; display: none;">
            <p id="resultado-texto"></p>
        </div>
        <script>
            function testFetch() {
                const url = window.location.protocol + '//' + window.location.host + '/proyecto_sena/public/?url=home/index';
                console.log('Probando URL:', url);
                
                fetch(url)
                    .then(response => {
                        document.getElementById('resultado').style.display = 'block';
                        document.getElementById('resultado-texto').innerHTML = 
                            '<span class="success">✓ Fetch exitoso!</span><br>' +
                            'Status: ' + response.status + '<br>' +
                            'URL probada: ' + url;
                    })
                    .catch(error => {
                        document.getElementById('resultado').style.display = 'block';
                        document.getElementById('resultado-texto').innerHTML = 
                            '<span class="error">✗ Error en Fetch</span><br>' +
                            'Error: ' + error.message + '<br>' +
                            'URL probada: ' + url;
                    });
            }
        </script>
    </div>

    <div class="section">
        <h2>Instrucciones</h2>
        <ul>
            <li>Si ves <span class="success">✓ HTTPS Detectado Correctamente</span>, la configuración es correcta</li>
            <li>Si ves <span class="warning">⚠ HTTP Detectado</span>, verifica que ngrok esté enviando los headers correctos</li>
            <li>Los headers HTTP_X_FORWARDED_PROTO deben estar presentes cuando uses ngrok</li>
            <li>Abre la consola del navegador (F12) para ver logs adicionales</li>
        </ul>
    </div>
</body>
</html>
