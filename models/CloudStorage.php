<?php

require_once dirname(__DIR__ ,1) . '/vendor/autoload.php';
require_once dirname(__DIR__ ,1) . '/config/conexion.php';
require_once dirname(__DIR__ ,1) . '/models/Consulta.php';
use Dotenv\Dotenv;

$config = App\Config::getInstance();
$dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());

$dotenv->load();

use Google\Cloud\Storage\StorageClient;
putenv('GOOGLE_APPLICATION_CREDENTIALS' . $_ENV['GOOGLE_APPLICATION_CREDENTIALS']);

$cloud = new CloudStorage();
$cons = 36;
$data  = $cloud->obtenerInfoArchivos($cons);

class CloudStorage {

    private $credentials;

    public function crearBucketDinamico($nombreConsultaReferencia) {
        $PROJECT_ID = $_ENV['PROJECT_ID'];
        $storage = new StorageClient([
            'projectId' => $PROJECT_ID
        ]);

        // Convertir nombre.pdf → nombre sin extensión
        $base = pathinfo($nombreConsultaReferencia, PATHINFO_FILENAME);
        // Normalizar a bucket-name-válido
        $bucketName = strtolower($base);
        $bucketName = preg_replace('/[^a-z0-9\-]/', '-', $bucketName); // Solo minúsculas y guiones
        $bucketName = trim($bucketName, '-');  //LINEA 63
        $bucketName = substr($bucketName, 0, 50) . '-' . uniqid();

        try {
            $bucket = $storage->createBucket($bucketName);

            echo "[GCS] Bucket creado correctamente: " . $bucketName;
        } catch (\Exception $e) {
            echo $e-> getMessage();
        }

        return $bucketName;
    }

    /**
     * Sube archivos a un bucket único basado en su nombre
     */
    public function subirArchivos($cons_id, $files) {

        $storage = new StorageClient([ //linea 50
            'projectId' => '416462877074'
        ]);

        $consulta = new Consulta();
        //CONSULTA EL NOMBRE EL BUCKET
        $data = $consulta->obtenerBucketPorConsulta($cons_id);

        error_log(print_r($data, true));
        
        //VERIFICA EXISTENCIA DEL NOMBRE DEL BUCKET
        if (!$data || !isset($data[0]['nom_bucket'])) {

            throw new \Exception("No se encontró bucket para la consulta $cons_id");
        }

        //SE TOMA EL NOMBRE DEL BUCKET EN $nom_bucket
        $nom_bucket = $data[0]['nom_bucket'];

        $bucket = $storage->bucket($nom_bucket);
    
        $resultados = [];

        foreach ($files['tmp_name'] as $i => $tmpFile) {

            if (!is_uploaded_file($tmpFile)) continue;

            $nombreOriginal = $files['name'][$i];

            if (is_array($nombreOriginal)) {
                $nombreOriginal = $nombreOriginal[0];
            }

            // Se toma el nombre del archivo para subir al bucket
            $objectName = basename($nombreOriginal);

            // SUBIDA DE ARCHIVO/S AL BUCKET ACTUAL
            $bucket->upload(
                fopen($tmpFile, 'r'),
                ['name' => $objectName]
            );
            
            // Crear Signed URL válida 24h (la que Gemini SÍ acepta)
            $file = $bucket->object($objectName);

            $signedUrl = $file->signedUrl(
                new \DateTime('+24 hours'),
                [
                    'version' => 'v4',
                    'method' => 'GET'
                ]
            );

            // Regresar info para Gemini 
            $resultados[] = [
                "bucket" => $bucket,
                "file"   => $objectName,
                "gcs_uri" => $gcsUri 
            ];
        }

        return $resultados;
    }

    public function obtenerInfoArchivos($cons_id) {
        $storage = new StorageClient([ 
            'projectId' => '416462877074'
        ]);

        $consulta = new Consulta();
        //CONSULTA EL NOMBRE EL BUCKET
        $data = $consulta->obtenerBucketPorConsulta($cons_id);

        //VERIFICA EXISTENCIA DEL NOMBRE DEL BUCKET
        if (!$data || !isset($data[0]['nom_bucket'])) {

            throw new \Exception("No se encontró bucket para la consulta $cons_id");
        }

        //SE TOMA EL NOMBRE DEL BUCKET EN $nom_bucket
        //$nom_bucket = $data[0]['nom_bucket'];
        $nom_bucket = 'bucket-prueba-cons-694f136359c32';

        $bucket = $storage->bucket($nom_bucket);

        $resultado = [];
        //RECORRIDO A CADA UNO DE LOS OBJETOS DEL BUCKET
        foreach ($bucket -> objects() as $object) {
            $nombreObjeto = $object->name();
            
            $file = $bucket->object($nombreObjeto);
            $info = $file->info();
            $gsUtil = $file->gcsUri();
        
            echo "Nombre: " . $nombreObjeto . "<br> Tipo " . $info['contentType']. "<br> GS URI: " . $file->gcsUri() . "<br><br>";

            // OBTENER TYPE DE ARCHIVO Y EL GSUTIL DEL OBJETO/ARCHIVO
            $resultado[] = [
                "contentType" => $info['contentType'],
                "gs_util " => $gsUtil
            ];

        }
        echo $resultado;
    }
}