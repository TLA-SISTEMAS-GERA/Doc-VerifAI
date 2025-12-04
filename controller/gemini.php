<?php 
    class AIController {
        private $apiKey;

        public function __construct() {
            $config = include __DIR__ . '/../config/geminiai.php';
            $this->apiKey = $config['API_KEY'];
        }

        public function procesarPrompt($prompt) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";

            $data = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt]
                        ]
                    ]
                ]
            ];

            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "x-goog-api-key: $this->apiKey"
                ],
                CURLOPT_POSTFIELDS => json_encode($data)
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
    }
?>