<?php 
    class Documento extends Conectar {
        //INSERTAR REGISTRO DE DOCUMENTO POR CONSULTA
        public function insert_documento_detalle($det_id, $doc_nom) {
            $conectar= parent::conexion();
            $sql ="INSERT INTO td_documento_detalle (docd_id,det_id,doc_nom,est) VALUES (null,?,?,1);";
            $sql = $conectar->prepare($sql);
            $sql->bindParam(1,$det_id);
            $sql->bindParam(2,$doc_nom);
            $sql->execute();
        }

         //LISTAR DOCUMENTOS ADJUNTOS AL DETALLE/MENSAJE
         public function get_documento_detalle_x_det($det_id) {
            $conectar= parent::conexion();
            $sql ="SELECT 
                    d.docd_id,
                    d.det_id,
                    d.doc_nom,
                    d.est,
                    cd.det_id,
                    cd.cons_id,
                    cd.det_contenido,
                    cd.fech_crea
                FROM td_documento_detalle d
                INNER JOIN tm_detalle cd 
                    ON d.det_id = cd.det_id
                WHERE cd.det_id = ?;";
            $sql = $conectar->prepare($sql);
            $sql->bindParam(1,$det_id);
            $sql->execute();

            return $resultado = $sql->fetchAll();
        }

        // public function cargar_documento($det_id,$doc_nom, $cons_id) {
        //     $conectar= parent::conexion();
        //     $sql ="INSERT INTO td_documento_detalle (docd_id,det_id,doc_nom,est) VALUES (null,?,?,1);";
        //     $sql = $conectar->prepare($sql);
        //     $sql->bindParam(1,$doc_nom);
        //     $sql->execute();

        //     $sql1 = "SELECT LAST_INSERT_ID() AS det_id, ? AS cons_id;";
        //     $sql1=$conectar->prepare($sql1);
        //     $sql1->bindValue(1,$cons_id);
        //     $sql1->execute();
        //     return $resultado = $sql1->fetchAll();
        // }
    }


?>