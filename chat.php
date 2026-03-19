<?php
// chat.php - Versión con depuración visible

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use Google\Auth\ApplicationDefaultCredentials;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $_ENV['GOOGLE_APPLICATION_CREDENTIALS_VERTEX']);

class Chat {
    public function vertexStream($mensajes) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        
        if (empty($mensajes)) {
            echo "[ERROR: No se recibió mensaje]";
            exit;
            }
            
            // Obtener token
        try {
            $auth = ApplicationDefaultCredentials::getCredentials('https://www.googleapis.com/auth/cloud-platform');
            $token = $auth->fetchAuthToken();
                $accessToken = $token['access_token'] ?? null;
            } catch (Exception $e) {
                echo "[ERROR de autenticación: " . $e->getMessage() . "]";
                exit;
        }
                    
        if (!$accessToken) {
            echo "[ERROR: No se pudo obtener token de acceso]";
            exit;
        }

        $projectId = $_ENV['PROJECT_ID'];
        $location = $_ENV['LOCATION'] ?? 'us-central1';
        $model = 'gemini-2.5-flash';

        $url = "https://{$location}-aiplatform.googleapis.com/v1/projects/{$projectId}/locations/{$location}/publishers/google/models/{$model}:streamGenerateContent?alt=sse";

        $data = [
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        ["text" => $mensajes]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.9
            ],
        ];


         // Configurar cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        // Buffer para acumular líneas incompletas

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
            echo $data;
            ob_flush();
            flush();
            return strlen($data);
        });

        // Ejecutar la petición
        $result = curl_exec($ch);
        if (curl_error($ch)) {
            echo "[Error de conexión: " . curl_error($ch) . "]";
        }
        curl_close($ch);

    }
}
?>