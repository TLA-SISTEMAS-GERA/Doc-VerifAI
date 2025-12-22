<?php 

    require_once dirname(__DIR__,1) . "/vendor/autoload.php";
    require_once dirname(__DIR__,1) . "/config/config.php";
    
    use Dotenv\Dotenv;

    $config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();



    class AIController {
        private $apiKey;

        public function __construct() {
            $config = include __DIR__ . '/../config/geminiai.php';
            $this->apiKey = $config['API_KEY'];
        }

        public function procesarPrompt($mensajes) {

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";
        
            // Aquí $mensajes ya ES un array con "role" y "parts"
            $data = [
                "contents" => $mensajes
            ];
        
            $ch = curl_init($url);
        
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "x-goog-api-key: $this->apiKey"
                ],
                CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE)
            ]);
        
            $response = curl_exec($ch);
            curl_close($ch);
        
            return $response;
        }
    }
?>