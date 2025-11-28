<?php
    require_once("../config/conexion.php");
    require_once("../models/Consulta.php");

    $consulta = new Consulta();

    switch($_GET["op"]) {
        case "insert":
            $datos = $consulta -> insert_consulta($_POST["usu_id"], $_POST["cons_nom"]);
        break;

        case "listar_consultas":
            $datos = $consulta->listar_consultas($_POST["usu_id"]);
            $data = Array();
            foreach ($datos as $row) {
                $sub_array = array();

                $sub_array[] = $row["cons_id"];
                $sub_array[] = $row["cons_nom"];

                $sub_array[] = date("d/m/Y H:i", strtotime($row["fech_crea"]));
                $sub_array[] = '<button type="button" data-ciphertext="'.$row["cons_id"].'" data-real-id="'.$row["cons_id"].'"  id="'.$row["cons_id"].'" class="btn btn-inline btn-primary btn-sm ladda-button"><i class="fa fa-pencil"></i></button>';

                $data[] = $sub_array;
            }

            $results = array(
                "sEcho"=>1,
                "iTotalRecords"=>count($data),
                "iTotalDisplayRecords"=>count($data),
                "aaData"=>$data);
            echo json_encode($results);
        break;

        case "mostrar":
            $datos = $consulta -> mostrar_consulta($_POST["cons_id"]);

            if(is_array($datos) == true and count($datos)>0){
                foreach($datos as $row)
                {
                    $output["cons_id"] = $row["cons_id"];
                    $output["cons_nom"] = $row["cons_nom"];
                }
                echo json_encode($output);
            }
        break;
    }
?>