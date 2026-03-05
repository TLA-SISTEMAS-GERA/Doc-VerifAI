<?php
require_once dirname(__DIR__,1) . "/vendor/autoload.php";
require_once dirname(__DIR__,1) . "/config/config.php";
require_once dirname(__DIR__,1) . "/models/Prompt.php";
    
use Dotenv\Dotenv;
use Google\Auth\ApplicationDefaultCredentials;

$config = App\Config::getInstance();
$dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
$dotenv->load();

putenv('GOOGLE_APPLICATION_CREDENTIALS' . $_ENV['GOOGLE_APPLICATION_CREDENTIALS_VERTEX']);

class VertexAI {

    public function generarRespuestaVertex($mensajes) {
        try {
            // 1. Configurar autenticación
            $auth = ApplicationDefaultCredentials::getCredentials('https://www.googleapis.com/auth/cloud-platform');
            $token = $auth->fetchAuthToken();
            $accessToken = $token['access_token'];

            $PROJECTID = $_ENV['PROJECT_ID'];
            $LOCATION = $_ENV['LOCATION'];

            //2. Obtener el prompt desde el archivo
            $promptObj = new PromptVertex();
            $prompt = $promptObj->obtenerPromptVertexAI();

            // 3. Configurar la solicitud
            $url = "https://{$LOCATION}-aiplatform.googleapis.com/v1/projects/{$PROJECTID}/locations/{$LOCATION}/publishers/google/models/gemini-2.5-flash:generateContent";
            $data = [
                "contents" => $mensajes,
                "systemInstruction" => [
                    "role" => "system",
                    "parts" => [
                        [
                            "text" => $prompt
                        ]
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.1,
                    "topP" => 0.8,
                    "topK" => 40
                ]
            ];
            // 4. Realizar la solicitud
            $ch = curl_init($url);

            curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$accessToken}",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 600, // Tiempo máximo total en segundos
            CURLOPT_CONNECTTIMEOUT => 60, // Tiempo máximo para conectar            
            ]);

            $response = curl_exec($ch);
            curl_close($ch);
            //echo $response;

        } catch (Exception $e) {
            error_log("Error al generar respuesta con Vertex AI: " . $e->getMessage());
            return null;
        }
        return $response;
    }

    // public function generarRespuestaStream($mensajes) {

    //     $auth = ApplicationDefaultCredentials::getCredentials('https://www.googleapis.com/auth/cloud-platform');
    //     $token = $auth->fetchAuthToken();
    //     $accessToken = $token['access_token'];
    
    //     $PROJECTID = $_ENV['PROJECT_ID'];
    //     $LOCATION = $_ENV['LOCATION'];
    
    //     $promptObj = new PromptVertex();
    //     $prompt = $promptObj->obtenerPromptVertexAI();
    
    //     $url = "https://{$LOCATION}-aiplatform.googleapis.com/v1/projects/{$PROJECTID}/locations/{$LOCATION}/publishers/google/models/gemini-2.5-flash:streamGenerateContent?alt=sse";
    
    //     $data = [
    //         "contents" => $mensajes,
    //         "systemInstruction" => [
    //             "role" => "system",
    //             "parts" => [
    //                 ["text" => $prompt]
    //             ]
    //         ]
    //     ];
    
    //     $ch = curl_init($url);
    
    //     curl_setopt_array($ch, [
    
    //         CURLOPT_POST => true,
    
    //         CURLOPT_HTTPHEADER => [
    //             "Authorization: Bearer {$accessToken}",
    //             "Content-Type: application/json"
    //         ],
    
    //         CURLOPT_POSTFIELDS => json_encode($data),
    
    //         CURLOPT_WRITEFUNCTION => function ($ch, $chunk) {
    
    //             $lines = explode("\n", $chunk);
    
    //             foreach ($lines as $line) {
    
    //                 if (strpos($line, 'data: ') === 0) {
    
    //                     $json = substr($line, 6);
    
    //                     if ($json === "[DONE]") {
    //                         echo "data: [DONE]\n\n";
    //                         flush();
    //                         return strlen($chunk);
    //                     }
    
    //                     $data = json_decode($json, true);
    
    //                     if (isset($data["candidates"][0]["content"]["parts"][0]["text"])) {
    
    //                         $texto = $data["candidates"][0]["content"]["parts"][0]["text"];
    
    //                         echo "data: " . json_encode(["texto"=>$texto]) . "\n\n";
    
    //                         flush();
    //                     }
    //                 }
    //             }
    
    //             return strlen($chunk);
    //         }
    
    //     ]);
    
    //     curl_exec($ch);
    //     curl_close($ch);
    // }

    public function generarRespuestaStream($mensajes) {

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

        //Obtener el prompt desde el archivo
        $promptObj = new PromptVertex();
        $prompt = $promptObj->obtenerPromptVertexAI();

        //OCONFIGURACION DE LA SOLICITUD
        $projectId = $_ENV['PROJECT_ID'];
        $location = $_ENV['LOCATION'] ?? 'us-central1';
        $model = 'gemini-2.5-flash';

        $url = "https://{$location}-aiplatform.googleapis.com/v1/projects/{$projectId}/locations/{$location}/publishers/google/models/{$model}:streamGenerateContent?alt=sse";

        $data = [
            "contents" => $mensajes,
                "systemInstruction" => [
                    "role" => "system",
                    "parts" => [
                        [
                            "text" => $prompt
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

        echo "data: [DONE]\n\n";
        ob_flush();
        flush();
        exit;
    }
}
?>