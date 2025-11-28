<?php

    session_start();

    class Conectar {
        protected $dbh;

        protected function Conexion() {
            try {
                $conectar = $this->dbh = new PDO("mysql:host=localhost;dbname=tla_revision_docs", "root", "");
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
            return "http://localhost:80/TLA_Revision_Docs/";
        }
    }
?>