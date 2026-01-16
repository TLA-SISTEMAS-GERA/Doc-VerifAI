<?php

require_once dirname(__DIR__ ,1) . '/vendor/autoload.php';
require_once dirname(__DIR__ ,1) . '/config/conexion.php';
//require_once dirname(__DIR__ ,1) . '/models/Consulta.php';
use Dotenv\Dotenv;

$config = App\Config::getInstance();
$dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());

$dotenv->load();

use Google\Cloud\Storage\StorageClient;
putenv('GOOGLE_APPLICATION_CREDENTIALS' . $_ENV['GOOGLE_APPLICATION_CREDENTIALS']);
class CloudStorage {

    public function crearBucketDinamico($nombreConsultaReferencia) {
        $PROJECT_ID = $_ENV['PROJECT_ID'];
        $storage = new StorageClient([
            'projectId' => $PROJECT_ID
        ]);

        // Convertir nombre.pdf → nombre sin extensión
        $base = pathinfo($nombreConsultaReferencia, PATHINFO_FILENAME);
        echo "[GCS] Iniciando creación de bucket para consulta: " . $base;
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

    //SUBIR ARCHIVO/S A UN BUCKET BASADO EN SU NOMBRE
    public function subirArchivos($cons_id, $files) {
        $PROJECT_ID = $_ENV['PROJECT_ID'];
        $storage = new StorageClient([
            'projectId' => $PROJECT_ID
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

            // Crear un nombre único para el archivo dentro del bucket
            $objectName = basename($nombreOriginal);

            // SUBIDA DE ARCHIVO/S AL BUCKET ACTUAL
            $bucket->upload(
                fopen($tmpFile, 'r'),
                ['name' => $objectName]
            ); 
            // Regresar info para Gemini 
            $resultados[] = [
                "bucket" => $bucket,
                "file"   => $objectName
            ];
        }

        return $resultados;
    }

    public function eliminarArchivo($cons_id, $docd_id){
        $PROJECT_ID = $_ENV['PROJECT_ID'];
        $storage = new StorageClient([
            'projectId' => $PROJECT_ID
        ]);

        //CONSULTO EL NOMBRE DEL BUCKET DESDE LA CONSULTA
        $consulta = new Consulta();
        $data = $consulta->obtenerBucketPorConsulta($cons_id);

        if (!$data || !isset($data[0]['nom_bucket'])) {
            throw new \Exception("No se encontró bucket para la consulta $cons_id");
        }
        //GUARDO EL NOMBRE DEL BUCKET
        $nom_bucket = $data[0]['nom_bucket'];

        $documento = new Documento();
        $data_doc = $documento->obtener_documento_det($docd_id);   

        $objectName = $data_doc[0]['doc_nom'];
        //OBTENER BUCKET
        $bucket = $storage->bucket($nom_bucket);
        //OBTENER OBJETO
        $object = $bucket->object($objectName);

        if (!$object->exists()) {
            throw new \Exception("El archivo no existe en el bucket");
        }

        $object->delete();

        return [
            "status" => "ok",
            "bucket" => $nom_bucket,
            "object" => $objectName
        ];
    }


    //OBTENER CONTENTTYPE (TIPO DE ARCHIVO) / GSUTIL
    public function obtenerContentTypeyGsutil($cons_id) {
        $PROJECT_ID = $_ENV['PROJECT_ID'];
        $storage = new StorageClient([
            'projectId' => $PROJECT_ID
        ]);
        $consulta = new Consulta();
        $data = $consulta->obtenerBucketPorConsulta($cons_id);
        $bucket = $storage->bucket($data[0]['nom_bucket']);
        $contentType_GSutil = [];
        foreach ($bucket -> objects() as $object) {
            $nombreObjeto = $object->name();
            $file = $bucket->object($nombreObjeto);
            $info = $file->info();
            $gsUtil = $file->gcsUri();

            $contentType_GSutil[] = [
                "contentType" => $info['contentType'],
                "gs_util" => $gsUtil
            ];
        }
        return $contentType_GSutil;
    }
}