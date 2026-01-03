<?php 
    class Consulta extends Conectar {
        public function insert_consulta ($usu_id, $cons_nom, $nom_bucket) {
            $conectar = parent::conexion();
            $sql="INSERT 
                    INTO tm_consulta(usu_id, cons_nom, nom_bucket, fech_crea)
                    VALUES (?, ?, ?, NOW());";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1,$usu_id);
            $sql->bindValue(2,$cons_nom);
            $sql->bindValue(3,$nom_bucket);
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

        public function insert_detalle ($cons_id, $usu_id, $det_contenido) {
            $conectar = parent::conexion();
            $sql="INSERT 
                    INTO tm_detalle(cons_id, usu_id, det_contenido, fech_crea)
                    VALUES (?, ?, ?, NOW());";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1,$cons_id);
            $sql->bindValue(2,$usu_id);
            $sql->bindValue(3,$det_contenido);
            $sql->execute();
            return $resultado = $sql->fetchAll();

        }

        public function insert_detalle_ai ($cons_id, $usu_id, $det_contenido) {
            $conectar = parent::conexion();
            $sql="INSERT 
                  INTO tm_detalle(cons_id, usu_id, det_contenido, fech_crea)
                  VALUES (?, ?, ?, NOW());";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1,$cons_id);
            $sql->bindValue(2,$usu_id);
            $sql->bindValue(3,$det_contenido);
            $sql->execute();
            return $resultado = $sql->fetchAll();
        }

        public function listar_detalle_x_consulta($cons_id){
            $conectar= parent::conexion();
            parent::set_names();
            $sql="SELECT * FROM tm_detalle
                  INNER JOIN tm_usuario on tm_usuario.usu_id = tm_detalle.usu_id
                  WHERE cons_id = ?";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1, $cons_id);
            $sql->execute();
            return $resultado=$sql->fetchAll();
        }

        public function obtener_historial($cons_id) {
            $conectar = parent::conexion();
            parent::set_names();
            $sql = "SELECT * 
                    FROM tm_detalle 
                    WHERE cons_id=? 
                    ORDER BY fech_crea ASC";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $cons_id);
            $sql->execute();
            return $sql->fetchAll(PDO::FETCH_ASSOC);
        }

        public function obtenerBucketPorConsulta($cons_id) {
            $conectar = parent::conexion();
            $sql = "SELECT nom_bucket FROM tm_consulta WHERE cons_id = ?";
            $sql = $conectar->prepare($sql);
            $sql->bindValue(1, $cons_id);
            $sql->execute();
            return $sql->fetchAll(PDO::FETCH_ASSOC);
        }
        
    }
    
?>