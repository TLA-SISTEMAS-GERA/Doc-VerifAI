<?php

    require_once dirname(__DIR__,1) . "/vendor/autoload.php";
    require_once dirname(__DIR__,1) . "/config/config.php";

    use Dotenv\Dotenv;

    session_start();

    $config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();

    class Conectar {
        protected $dbh;

        protected function Conexion() {
            try {
                $DB_HOST = $_ENV['DB_HOST'];
                $DB_NAME = $_ENV['DB_NAME'];
                $DB_USER = $_ENV['DB_USER'];
                $DB_PASSWORD = $_ENV['DB_PASSWORD'];
                $conectar = $this->dbh = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME}", $DB_USER, $DB_PASSWORD);
                return $conectar;
            } catch (Exception $e) {
                print "Error de conexion: " . $e -> getMessage() . "<br/>";
                die();
            }
        }

        public function set_names() {
            return $this->dbh->query("SET NAMES 'utf8'");
        }

        public function ruta() {
            $URl_FRONTEND = $_ENV['URL_FRONTEND'];
            return "{$URl_FRONTEND}";
        }
    }
?>