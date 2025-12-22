<?php

require_once dirname(__DIR__,1) . "/vendor/autoload.php";
require_once dirname(__DIR__,1) . "/config/config.php";

use Dotenv\Dotenv;

$config = App\Config::getInstance();
$dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());

$dotenv->load();

use Google\Cloud\Storage\StorageClient;

putenv('GOOGLE_APPLICATION_CREDENTIALS' . $_ENV['GOOGLE_APPLICATION_CREDENTIALS']);
class CloudStorage {

    private $credentials;

    // public function __construct() {
    //     // Ruta a credentials.json
    //     $this->credentials = __DIR__ . "/../config/credentials.json";
    // }
    // Crea un bucket con el nombre basado en el archivo 
    private function crearBucketDinamico($nombreArchivoOriginal) {

        // if (!file_exists($this->credentials)) {
        //     return ["ERROR" => "credentials.json NO encontrado en: $this->credentials"];
        // }
        $PROJECT_ID = $_ENV['PROJECT_ID'];
        $storage = new StorageClient([
            'projectId' => $PROJECT_ID
        ]);

        // Convertir nombre.pdf → nombre sin extensión
        $base = pathinfo($nombreArchivoOriginal, PATHINFO_FILENAME);

        // Normalizar a bucket-name-válido
        $bucketName = strtolower($base);
        $bucketName = preg_replace('/[^a-z0-9\-]/', '-', $bucketName); // Solo minúsculas y guiones
        $bucketName = substr($bucketName, 0, 50); // Limitar tamaño
        $bucketName .= "-" . uniqid(); // Evitar duplicados globales

        // Crear bucket
        $bucket = $storage->createBucket($bucketName);

        return $bucketName;
    }

    /**
     * Sube archivos a un bucket único basado en su nombre
     */
    public function subirArchivos($files) {

        // if (!file_exists($this->credentials)) {
        //     return ["ERROR" => "credentials.json NO encontrado en: $this->credentials"];
        // }

        $storage = new StorageClient([ //linea 50
            'projectId' => '416462877074'
        ]);

        $resultados = [];

        foreach ($files['tmp_name'] as $i => $tmpFile) {

            if (!is_uploaded_file($tmpFile)) continue;

            $nombreOriginal = $files['name'][$i];

            // Crear bucket basado en el nombre del archivo
            $bucketName = $this->crearBucketDinamico($nombreOriginal);
            $bucket = $storage->bucket($bucketName);

            // Crear un nombre único para el archivo dentro del bucket
            $objectName = uniqid() . "_" . basename($nombreOriginal);

            // Subir archivo
            $bucket->upload(
                fopen($tmpFile, 'r'),
                ['name' => $objectName]
            );

            // Crear Signed URL válida 24h (la que Gemini SÍ acepta)
            $file = $bucket->object($objectName);

            $signedUrl = $file->signedUrl(
                new \DateTime('24 hours'),
                [
                    'version' => 'v4',
                    'method' => 'GET'
                ]
            );

            // Regresar info para Gemini 
            $resultados[] = [
                "bucket" => $bucketName,
                "file"   => $objectName,
                "signedUrl" => $signedUrl //esta es la URL autenticada
            ];
        }

        return $resultados;
    }
}