<?php 
    class Consulta extends Conectar {
        public function insert_consulta ($usu_id, $cons_nom) {
            $conectar = parent::conexion();
            $sql="INSERT 
                    INTO tm_consulta(usu_id, cons_nom, fech_crea)
                    VALUES (?, ?, NOW());";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1,$usu_id);
            $sql->bindValue(2,$cons_nom);
            $sql->execute();
            return $resultado = $sql->fetchAll();
        }

        public function listar_consultas ($usu_id) {
            $conectar = parent::conexion();
            $sql="SELECT *
                  FROM tm_consulta 
                  WHERE usu_id = ?;";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1,$usu_id);
            $sql->execute();
            return $resultado = $sql->fetchAll();
        }

        public function mostrar_consulta ($cons_id) {
            $conectar = parent::conexion();
            $sql="SELECT *
                  FROM tm_consulta 
                  WHERE cons_id = ?;";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1,$cons_id);
            $sql->execute();
            return $resultado = $sql->fetchAll();
        }
    }
    
?>