<?php
    require_once("../../config/conexion.php");
    require_once("../../vendor/autoload.php");
    
    use Dotenv\Dotenv;

	$config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();
    session_destroy();
   // header("Location:"."http://localhost:80/Doc-VerifAI/"."index.php");  
   header("Location:"."http://doc-verifai.tecnologisticaaduanal.com/"."index.php");  
?>