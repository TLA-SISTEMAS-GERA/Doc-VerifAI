<?php
    require_once("../config/conexion.php");
    require_once("../models/Usuario.php");
    require_once dirname(__DIR__, 1) . '/config/config.php';

    use Dotenv\Dotenv;
    $config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();

    $usuario=new Usuario();

    $key = $_ENV['APP_ENCRIPT_KEY'];
    $cipher = "aes-256-cbc";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

?>