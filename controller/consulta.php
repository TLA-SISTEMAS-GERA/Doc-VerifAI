<?php
    // require_once("../config/conexion.php");
    require_once dirname(__DIR__ ,1) . '/config/conexion.php';
    // require_once("../models/Consulta.php");
    require_once dirname(__DIR__ ,1) . '/models/Consulta.php';
    // require_once("../controller/gemini.php");
    require_once dirname(__DIR__ ,1) . '/controller/gemini.php';
    require_once dirname(__DIR__ ,1) . '/controller/vertex.php';
    // require_once("../controller/storage.php");
    require_once dirname(__DIR__ ,1) . '/controller/storage.php';
    // require_once("../controller/documentai.php");
    require_once dirname(__DIR__ ,1) . '/controller/documentai.php';
    // require_once("../models/CloudStorage.php");
    require_once dirname(__DIR__ ,1) . '/models/CloudStorage.php';

    require_once("../models/Documento.php");

    $documento= new Documento();
    $consulta = new Consulta();

    switch($_GET["op"]) {
        //CREAR UNA NUEVA CONSULTA Y BUCKET CON EL NOMBRE DE LA CONSULTA
        case "insert":
            // SE CREA UN BUCKET CON EL NOMBRE DE LA CONSULTA RECIEN CREADA
            $cloud = new CloudStorage();
            $bucket = $cloud ->crearBucketDinamico($_POST["cons_nom"]);
            if (!empty($resultado['ERROR'])) {
                echo $resultado['ERROR'];
            }
            //INSERCCION DE CONSULTA + NOMBRE BUCKET
            $datos = $consulta -> insert_consulta($_POST["usu_id"], $_POST["cons_nom"], $bucket);
        break;

        //LISTAR LAS CONSULTAS QUE EL USUARIO HA CREADO
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
        
        case "ai_prompt":
            $mensajesRaw = $_POST["mensajes"] ?? null;
        
            if (!$mensajesRaw) {
                echo json_encode(["error" => "No llegaron mensajes"]);
                exit;
            }
            // Convertir string JSON → array PHP
            $mensajes = json_decode($mensajesRaw, true);
        
            // LOG para verificar
            file_put_contents("debug_gemini.txt", print_r($mensajes, true));
        
            // $ai = new AIController();
            // $respuesta = $ai->procesarPrompt($mensajes);
            $vertex = new VertexAI();
            $respuesta = $vertex->generarRespuestaVertex($mensajes);
            echo $respuesta;
        break;

        case "insertdetalle":
            $datos = $consulta -> insert_detalle($_POST["cons_id"], $_POST["usu_id"], $_POST["det_contenido"]);

            if (is_array($datos) && count($datos) > 0){
                foreach ($datos as $row) {

                    $output["det_id"] = $row["det_id"];
                    $output["cons_id"] = $row["cons_id"];

                    if(isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                        $countfiles = count($_FILES['files']['name']);
                        // La carpeta usa el ID de cada consulta
                        // Ahi se almacenan los documentos que se cargan a la consulta
                        $ruta = "../public/document_detalle/" . $output["cons_id"] . "/";

                        echo $ruta;
                        if (!file_exists($ruta)) {
                            mkdir($ruta, 0777, true);
                        }

                        for ($index = 0; $index < $countfiles; $index++) {
                            $doc1 = $_FILES['files']['tmp_name'][$index];
                            $destino = $ruta . $_FILES['files']['name'][$index];

                            $documento->insert_documento_detalle(
                                $output["det_id"],
                                $_FILES['files']['name'][$index]
                            );
    
                            move_uploaded_file($doc1, $destino);
                        }
                    }
                }
            }
            echo json_encode($datos);
        break;

        case "listardetalle":
            $datos = $consulta -> listar_detalle_x_consulta($_POST["cons_id"]);

            ?>
                <?php
                    foreach($datos as $row){
                        ?>
                        <h1></h1>
                            <article class="activity-line-item box-typical">
                                <div class="activity-line-date">
                                    <?php echo date("d/m/Y H:i", strtotime($row["fech_crea"])); ?>
                                </div>
                                <header class="activity-line-item-header">
                                    <div class="activity-line-item-user">
                                        <div class="activity-line-item-user-photo">
                                            <a href="#">
                                        
                                            </a>
                                        </div>
                                        <div class="activity-line-item-user-name"><?php echo $row['usu_nom'].' '.$row['usu_ape']?></div>
                                        <div class="activity-line-item-user-status">                                        
                                    </div>
                                </header>
                                <div class="activity-line-action-list">
                                    <section class="activity-line-action">
                                    <div class="time"><?php echo date("H:i", strtotime($row["fech_crea"])); ?></div>
                                    <div class="cont">
                                        <div class="cont-in">
                                            <p>
                                                <?php echo $row['det_contenido'];?>
                                            </p>	                                            
                                        </div>
                                    </div>
                                </section><!--.activity-line-action-->                                
                                </div>
                            </article>
                        <?php
                    }
                ?>
            <?php
        break;

        case "obtener_historial":
            $datos = $consulta->obtener_historial($_POST["cons_id"]);
            echo json_encode($datos);
        break;

        case "subir_archivos_cloud":
            require_once "../models/CloudStorage.php";
            require_once "../vendor/autoload.php";
            $cloud = new CloudStorage();
            $archivos = $cloud -> subirArchivos($_POST["cons_id"], $_FILES["files"]);         
            $resultado = [];
            file_put_contents(
                __DIR__ . "/debug_files_api.json",
                json_encode($resultado, JSON_PRETTY_PRINT)
            );            
            error_log("RESPUESTA FINAL:");
            error_log(json_encode($resultado));
            echo json_encode($resultado);
        break;

        case "obtener_Info_Gsutil":
            $cloud = new CloudStorage();
            $contentType_GSutilresult = $cloud -> obtenerContentTypeyGsutil($_POST["cons_id"]);
            echo json_encode($contentType_GSutilresult);
        break;
    }
?>