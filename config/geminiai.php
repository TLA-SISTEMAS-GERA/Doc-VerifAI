<?php 
    require_once dirname(__DIR__,1) . "/vendor/autoload.php";
    require_once dirname(__DIR__,1) . "/config/config.php";
    
    use Dotenv\Dotenv;

    $config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();

    $API_KEY_GEMINI = $_ENV['API_KEY_GEMINI'];
    return [
        'API_KEY' => $API_KEY_GEMINI
    ];
?>