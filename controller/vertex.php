<?php
require_once dirname(__DIR__,1) . "/vendor/autoload.php";
require_once dirname(__DIR__,1) . "/config/config.php";
    
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
            //echo  "[Vertex AI] Iniciando solicitud a Vertex AI...";
            $auth = ApplicationDefaultCredentials::getCredentials('https://www.googleapis.com/auth/cloud-platform');
            $token = $auth->fetchAuthToken();
            $accessToken = $token['access_token'];

            $PROJECTID = $_ENV['PROJECT_ID'];
            $LOCATION = $_ENV['LOCATION'];

            $url = "https://{$LOCATION}-aiplatform.googleapis.com/v1/projects/{$PROJECTID}/locations/{$LOCATION}/publishers/google/models/gemini-2.5-flash:generateContent";
            $data = [
                "contents" => $mensajes
            ];
            //echo "Informacion que contiene el mensaje: " . json_encode($data);
            // 3. Realizar la solicitud
            $ch = curl_init($url);

            curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$accessToken}",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
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
}
?>