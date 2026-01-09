<?php
    require_once dirname(__DIR__,1) . "/vendor/autoload.php";
    require_once dirname(__DIR__,1) . "/config/config.php";
    
    use Dotenv\Dotenv;

    $config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();

    class PromptVertex {
        private static $cache = null;
        private static $lastModified = 0;
        private static $cacheTime = 3600; // 1 hora
        public function obtenerPromptVertexAI() {
            $promptPath = $_ENV['PROMPT_VERTEX_AI'];
            $currentTime = time();
            $fileModified = filemtime($promptPath);
            //Verifica si el prompt está en caché y si no ha expirado
            if (self::$cache !== null && 
                ($currentTime - self::$lastModified) < self::$cacheTime &&
                self::$lastModified >= $fileModified) {
                return self::$cache;
            }
            //Carga el prompt desde el archivo
            if (file_exists($promptPath)) {
                self::$cache = file_get_contents($promptPath);
                self::$lastModified = $currentTime;
                return self::$cache;
            } else {
                throw new Exception("El archivo de prompt no existe en la ruta especificada: " . $promptPath);
            }
        }
    }
?>