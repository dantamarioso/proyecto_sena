<?php
/**
 * Test de carga de archivo - Debug
 * Accede a: https://ngrok-url/proyecto_sena/public/test_upload.php
 */

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    // Log de debug
    $debug = [
        'tiempo' => date('Y-m-d H:i:s'),
        'metodo' => $_SERVER['REQUEST_METHOD'],
        'protocol' => $_SERVER['HTTPS'] ?? 'no definido',
        'x_forwarded_proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'no definido',
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'no definido',
        'http_host' => $_SERVER['HTTP_HOST'] ?? 'no definido',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'no definido',
        'files' => array_keys($_FILES),
        'post_data' => array_keys($_POST),
    ];
    
    // Logging
    error_log(json_encode($debug));
    
    if (!isset($_SESSION['user'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No autenticado',
            'debug' => $debug
        ]);
        exit;
    }
    
    if (empty($_FILES['archivo'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No se envió archivo',
            'debug' => $debug
        ]);
        exit;
    }
    
    $archivo = $_FILES['archivo'];
    
    // Simular operación de carga
    $nombreArchivo = "uploads/materiales/" . date('YmdHis_') . basename($archivo['name']);
    $rutaSistema = __DIR__ . "/" . $nombreArchivo;
    $uploadDir = __DIR__ . "/uploads/materiales/";
    
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }
    
    if (move_uploaded_file($archivo['tmp_name'], $rutaSistema)) {
        echo json_encode([
            'success' => true,
            'message' => 'Archivo subido exitosamente',
            'archivo' => [
                'nombre_original' => $archivo['name'],
                'nombre_archivo' => $nombreArchivo,
                'tamano' => $archivo['size'],
                'ruta_sistema' => $rutaSistema
            ],
            'debug' => $debug
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al subir el archivo',
            'debug' => $debug
        ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test de Carga - Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, textarea { padding: 8px; width: 100%; max-width: 500px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        #resultado { margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 4px; display: none; }
        pre { background: #333; color: #0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Test de Carga de Archivo</h1>
    
    <div class="form-group">
        <p><strong>URL actual:</strong> <code id="url-actual"></code></p>
        <p><strong>BASE_URL (JS):</strong> <code id="base-url"></code></p>
        <p><strong>Protocolo:</strong> <code id="protocolo"></code></p>
    </div>

    <form id="formulario">
        <div class="form-group">
            <label for="material_id">Material ID:</label>
            <input type="number" id="material_id" name="material_id" value="27" required>
        </div>
        
        <div class="form-group">
            <label for="archivo">Seleccionar archivo:</label>
            <input type="file" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv" required>
        </div>
        
        <button type="button" onclick="subirTest()">Subir Test (Local)</button>
        <button type="button" onclick="subirViaApi()">Subir via API</button>
        <button type="button" onclick="testDiagnostico()">📊 Ejecutar Diagnóstico</button>
    </form>

    <div id="resultado">
        <h2>Resultado:</h2>
        <pre id="resultado-contenido"></pre>
    </div>

    <script>
        // Mostrar información
        document.getElementById('url-actual').textContent = window.location.href;
        document.getElementById('protocolo').textContent = window.location.protocol;
        
        if (typeof window.BASE_URL !== 'undefined') {
            document.getElementById('base-url').textContent = window.BASE_URL;
        } else {
            document.getElementById('base-url').textContent = 'NO DEFINIDO';
        }

        function subirTest() {
            const archivo = document.getElementById('archivo').files[0];
            if (!archivo) {
                alert('Selecciona un archivo');
                return;
            }

            const formData = new FormData();
            formData.append('material_id', document.getElementById('material_id').value);
            formData.append('archivo', archivo);

            // Usar la URL local
            const urlUpload = window.location.origin + window.location.pathname.replace('test_upload.php', 'test_upload.php');
            
            console.log('Subiendo a:', urlUpload);
            
            fetch(urlUpload, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('resultado').style.display = 'block';
                document.getElementById('resultado-contenido').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('resultado').style.display = 'block';
                document.getElementById('resultado-contenido').textContent = 'ERROR: ' + error.message;
            });
        }

        function subirViaApi() {
            const archivo = document.getElementById('archivo').files[0];
            if (!archivo) {
                alert('Selecciona un archivo');
                return;
            }

            const formData = new FormData();
            formData.append('material_id', document.getElementById('material_id').value);
            formData.append('archivo', archivo);

            // Usar window.BASE_URL
            if (typeof window.BASE_URL === 'undefined') {
                alert('BASE_URL no está definido');
                return;
            }

            const urlUpload = window.BASE_URL + '?url=materiales/subirArchivo';
            
            console.log('Subiendo a:', urlUpload);
            
            fetch(urlUpload, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('resultado').style.display = 'block';
                document.getElementById('resultado-contenido').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('resultado').style.display = 'block';
                document.getElementById('resultado-contenido').textContent = 'ERROR: ' + error.message;
            });
        }

        function testDiagnostico() {
            const archivo = document.getElementById('archivo').files[0];
            if (!archivo) {
                alert('Selecciona un archivo');
                return;
            }

            const formData = new FormData();
            formData.append('material_id', document.getElementById('material_id').value);
            formData.append('archivo', archivo);

            const urlDiag = window.location.origin + window.location.pathname.replace('test_upload.php', 'diagnostico_upload.php');
            
            console.log('Diagnosticando a:', urlDiag);
            
            fetch(urlDiag, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('resultado').style.display = 'block';
                document.getElementById('resultado-contenido').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('resultado').style.display = 'block';
                document.getElementById('resultado-contenido').textContent = 'ERROR: ' + error.message;
            });
        }
    </script>
</body>
</html>
