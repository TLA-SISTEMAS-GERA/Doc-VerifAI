<?php
    require_once("../config/conexion.php");
    require_once("../models/Usuario.php");
    $usuario=new Usuario();

    //VARIABLES DE ENTORNO
    require_once dirname(__DIR__, 1) . '/config/config.php';
    use Dotenv\Dotenv;
    $config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();

    //CONFIGURACION DE CIFRADO
    $key = $_ENV['APP_ENCRIPT_KEY'];
    $cipher = "aes-256-cbc";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

    switch($_GET["op"]){

        case "guardaryeditar":
            $datos= $usuario->get_usuario_x_correo($_POST["usu_correo"]);
            if(empty($_POST["usu_id"])){
                // Agregar nuevo usuario
                if(count($datos) == 0){
                    $usuario->insert_usuario($_POST["usu_nom"],$_POST["usu_ape"],$_POST["usu_correo"],$_POST["usu_pass"],$_POST["rol_id"]);
                    echo "1";
                }else {
                    echo "0"; // correo ya existe
                }
            } else {
                // Editar usuario existente
                // Verificar si el correo pertenece a otro usuario
                if($datos[0]["usu_id"] == $_POST["usu_id"]){
                    $usuario->update_usuario($_POST["usu_nom"],$_POST["usu_ape"],$_POST["usu_correo"],$_POST["usu_pass"],$_POST["rol_id"], $_POST["usu_id"]);
                    echo "2";
                } else {
                    echo "0"; // correo duplicado en otro usuario
                }
            }
        break;

        case "listar":
            $datos=$usuario->get_usuario();
            $data= Array();
            foreach($datos as $row){
                $sub_array = array();
                $sub_array[] = $row["usu_nom"];
                $sub_array[] = $row["usu_ape"];
                $sub_array[] = $row["usu_correo"];
                $sub_array[] = $row["usu_pass"];
                
                if($row["rol_id"]=="1"){
                    $sub_array[] = '👤 Usuario';
                }else if($row["rol_id"]=="2"){
                    // $sub_array[] = '<span class="label label-pill label-info aquamarine">Administrador</span>';
                    $sub_array[] = '⚙️ Administrador';
                }

                $sub_array[] = '<button type="button" onClick="editar('.$row["usu_id"].');"  id="'.$row["usu_id"].'" class="btn btn-rounded btn-inline btn-warning-outline">✏️ Editar</button>';
                
                $sub_array[] = '<button type="button" onClick="eliminar('.$row["usu_id"].');"  id="'.$row["usu_id"].'" class="btn btn-rounded btn-inline btn-danger-outline">🗑️ Eliminar</button>';
                $data[] = $sub_array;
            }

            $results = array(
                "sEcho"=>1,
                "iTotalRecords"=>count($data),
                "iTotalDisplayRecords"=>count($data),
                "aaData"=>$data);
            echo json_encode($results);
        break;

        case "mostrar";
            $datos=$usuario->get_usuario_x_id($_POST["usu_id"]);  
            if(is_array($datos)==true and count($datos)>0){
                foreach($datos as $row)
                {
                    $output["usu_id"] = $row["usu_id"];
                    $output["usu_nom"] = $row["usu_nom"];
                    $output["usu_ape"] = $row["usu_ape"];
                    $output["usu_correo"] = $row["usu_correo"];

                    $iv_dec = substr(base64_decode($row["usu_pass"]), 0, openssl_cipher_iv_length($cipher));
                    $cifradoSinIV = substr(base64_decode($row["usu_pass"]), openssl_cipher_iv_length($cipher));
                    $descifrado = openssl_decrypt($cifradoSinIV, $cipher, $key, OPENSSL_RAW_DATA, $iv_dec);

                    $output["usu_pass"] = $descifrado;
                    $output["rol_id"] = $row["rol_id"];
                    
                }
                echo json_encode($output);
            }   
        break;
    }

?>