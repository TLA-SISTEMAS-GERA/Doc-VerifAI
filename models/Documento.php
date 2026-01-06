<?php 
    class Documento extends Conectar {
        //INSERTAR REGISTRO DE DOCUMENTO POR CONSULTA
        public function insert_documento_detalle($det_id, $doc_nom) {
            $conectar= parent::conexion();
            $sql ="INSERT INTO td_documento_detalle (docd_id, det_id, doc_nom, est) VALUES (null,?,?,1);";
            $sql = $conectar->prepare($sql);
            $sql->bindParam(1,$det_id);
            $sql->bindParam(2,$doc_nom);
            $sql->execute();
        }
    }
?>