<?php

require_once dirname(__DIR__ ,1) . '/vendor/autoload.php';
require_once dirname(__DIR__ ,1) . '/config/config.php';

use League\CommonMark\CommonMarkConverter;
use Dotenv\Dotenv;
use Google\Auth\ApplicationDefaultCredentials;

$config = App\Config::getInstance();
$dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());

$dotenv->load();

//use GuzzleHttp\Client;
//use GuzzleHttp\HandlerStack;

putenv('GOOGLE_APPLICATION_CREDENTIALS' . $_ENV['GOOGLE_APPLICATION_CREDENTIALS_VERTEX']);

$auth = ApplicationDefaultCredentials::getCredentials('https://www.googleapis.com/auth/cloud-platform');
$token = $auth->fetchAuthToken();
$accessToken = $token['access_token'];

$protectID = $_ENV['PROJECT_ID'];
$location = $_ENV['LOCATION'];



$url = "https://{$location}-aiplatform.googleapis.com/v1/projects/{$protectID}/locations/{$location}/publishers/google/models/gemini-2.5-flash:generateContent";

$data = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => 'Crea una tabla comparativa de los siguientes documentos y verifica que los datos coincidan: Marca, modelo, numeros de serie y numero de factura. Los documentos son:'],
                [
                    "file_data" => [
                        "mime_type" => 'application/pdf',
                        "file_uri" => 'gs://ejemplosarchivos/FACTURA.pdf',
                    ],
                    "file_data" => [
                        "mime_type" => 'application/pdf',
                        "file_uri" => 'gs://ejemplosarchivos/20250903_101621.jpg',
                    ],

                    "file_data" => [
                        "mime_type" => 'image/jpeg',
                        "file_uri" => 'gs://ejemplosarchivos/20250903_101857.jpg',
                    ],
                    "file_data" => [
                        "mime_type" => 'image/jpeg',
                        "file_uri" => 'gs://ejemplosarchivos/20250903_102336.jpg',
                    ],
                    "file_data" => [
                        "mime_type" => 'image/jpeg',
                        "file_uri" => 'gs://ejemplosarchivos/20250903_101621.jpg',
                    ],
                    "file_data" => [
                        "mime_type" => 'application/pdf',
                        "file_uri" => 'gs://ejemplosarchivos/Referencia.pdf',
                    ]
                ],
            ]
        ]
    ]
];

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

    // 1️⃣ El response suele venir en JSON
    $decoded = json_decode($response, true);

    // 2️⃣ Extraemos el texto que devuelve el modelo
    // (ajusta la ruta según tu API)
    $rawMarkdown = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

    // 3️⃣ Markdown → HTML
    $converter = new CommonMarkConverter();
    $html = $converter->convert($rawMarkdown)->getContent();

    // 4️⃣ Sanitizar HTML (evita XSS)
    $config = HTMLPurifier_Config::createDefault(); //linea 97
    $purifier = new HTMLPurifier($config);
    $cleanHtml = $purifier->purify($html);

    // 5️⃣ Mostrar HTML seguro
    echo $cleanHtml;
?>