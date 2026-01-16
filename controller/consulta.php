<?php
    require_once dirname(__DIR__ ,1) . '/config/conexion.php';
    require_once dirname(__DIR__, 1) . '/config/config.php';
    require_once dirname(__DIR__ ,1) . '/models/Consulta.php';
    require_once dirname(__DIR__ ,1) . '/controller/gemini.php';
    require_once dirname(__DIR__ ,1) . '/controller/vertex.php';
    require_once dirname(__DIR__ ,1) . '/controller/storage.php';
    require_once dirname(__DIR__ ,1) . '/controller/documentai.php';
    require_once dirname(__DIR__ ,1) . '/models/CloudStorage.php';

    require_once("../models/Documento.php");

    $documento= new Documento();
    $consulta = new Consulta();

    use Dotenv\Dotenv;
    $config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();

    $key = $_ENV['APP_ENCRIPT_KEY'];
    $cipher = "aes-256-cbc";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

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

                //CIFRADO
                $cifrado = openssl_encrypt($row["cons_id"], $cipher, $key,OPENSSL_RAW_DATA, $iv);
                $textoCifrado = base64_encode($iv . $cifrado);

                $sub_array[] = '<button type="button" data-ciphertext="'.$textoCifrado.'" data-real-id="'.$row["cons_id"].'"  id="'.$textoCifrado.'" class="btn btn-inline btn-primary btn-sm ladda-button">✏️</button>';

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
            $iv_dec = substr(base64_decode($_POST["cons_id"]), 0, openssl_cipher_iv_length($cipher));
            $cifradoSinIV= substr(base64_decode($_POST["cons_id"]), openssl_cipher_iv_length($cipher));
            $descifrado = openssl_decrypt($cifradoSinIV, $cipher, $key, OPENSSL_RAW_DATA, $iv_dec);

            $datos = $consulta -> mostrar_consulta($descifrado);

            error_log("DESCIFRADO: " . var_export($descifrado, true));
            error_log("DATOS: " . var_export($datos, true));

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
            $iv_dec = substr(base64_decode($_POST["cons_id"]), 0, openssl_cipher_iv_length($cipher));
            $cifradoSinIV= substr(base64_decode($_POST["cons_id"]), openssl_cipher_iv_length($cipher));
            $descifrado = openssl_decrypt($cifradoSinIV, $cipher, $key, OPENSSL_RAW_DATA, $iv_dec);

            $datos = $consulta -> insert_detalle($descifrado, $_POST["usu_id"], $_POST["det_contenido"]);

            if (is_array($datos) && count($datos) > 0){
                foreach ($datos as $row) {

                    $output["det_id"] = $row["det_id"];
                    $output["cons_id"] = $row["cons_id"];

                    if(isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
                        $countfiles = count($_FILES['files']['name']);

                        for ($index = 0; $index < $countfiles; $index++) {

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
            $iv_dec = substr(base64_decode($_POST["cons_id"]), 0, openssl_cipher_iv_length($cipher));
            $cifradoSinIV= substr(base64_decode($_POST["cons_id"]), openssl_cipher_iv_length($cipher));
            $descifrado = openssl_decrypt($cifradoSinIV, $cipher, $key, OPENSSL_RAW_DATA, $iv_dec);

            $datos = $consulta -> listar_detalle_x_consulta($descifrado);

            ?>
                <?php
                    foreach($datos as $row){
                            // 1️⃣ CONSULTAR DOCUMENTOS DEL DETALLE
                            $datos_det = $documento->get_documento_detalle_x_det($row["det_id"]);

                            // 2️⃣ VALIDAR SI TIENE TEXTO O DOCUMENTOS
                            $tieneTexto = trim($row['det_contenido']) !== '';
                            $tieneDocs  = is_array($datos_det) && count($datos_det) > 0;

                            // 3️⃣ SI NO TIENE NADA → NO SE RENDERIZA
                            if (!$tieneTexto && !$tieneDocs) {
                                continue;
                            }
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

                                            <br>
                                            
                                            <!-- LISTAR NOMBRES DE LOS DOCUMENTOS ADJUNTOS -->
                                            <?php
                                                $datos_det = $documento -> get_documento_detalle_x_det($row["det_id"]);
                                                if(is_array($datos_det) == true and count($datos_det) > 0){
                                                    ?>
                                                        <p><strong>Documentos adjuntos</strong></p>
                                                        <p>
                                                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full">
                                                                <!-- ENCABEZADO DE LA TABLA -->
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width: 40%;">Nombre</th>
                                                                        
                                                                    </tr>
                                                                </thead>

                                                                <tbody>    
                                                                    <?php
                                                                        foreach($datos_det as $row_det){
                                                                        
                                                                            ?>    
                                                                            <tr>
                                                                                <td>
                                                                                    <!-- AQUI EMPIEZO A EMITIR EL ID DEL DOC PARA ELIMINARLO -->
                                                                                    <button 
                                                                                        type="button"
                                                                                        class="btn btn-rounded btn-inline btn-secondary-outline btnEliminarDoc"
                                                                                        data-docid="<?php echo $row_det["docd_id"]; ?>">
                                                                                        <i class="fa fa-trash-o"></i>
                                                                                    </button>
                                                                                    <!-- SE MUESTRA EL NOMBRE DEL DOC ADJUNTO -->
                                                                                    <i class="fa fa-paperclip"></i>
                                                                                    <?php echo $row_det["doc_nom"]; ?>
                                                                                </td>
                                                                            </tr> 
                                                                            <tr>
                                                                                
                                                                            </tr>
                                                                            <?php
                                                                        }
                                                                        ?>
                                                                    
                                                                </tbody>
                                                            </table>
                                                        </p>
                                                    <?php
                                                }
                                            ?>
                                            
                                        </div>
                                    </div>
                                </section><!--.activity-line-action-->                                
                                </div>
                            </article>
                            <?php
                    }
                    ?>
                    <progress id="barra_progreso" class="progress progress-success" value="0" max="100"></progress>
            <?php
        break;

        case "obtener_historial":
            $iv_dec = substr(base64_decode($_POST["cons_id"]), 0, openssl_cipher_iv_length($cipher));
            $cifradoSinIV= substr(base64_decode($_POST["cons_id"]), openssl_cipher_iv_length($cipher));
            $descifrado = openssl_decrypt($cifradoSinIV, $cipher, $key, OPENSSL_RAW_DATA, $iv_dec);
            
            $datos = $consulta->obtener_historial($descifrado);
            echo json_encode($datos);
        break;

        case "subir_archivos_cloud":
            $iv_dec = substr(base64_decode($_POST["cons_id"]), 0, openssl_cipher_iv_length($cipher));
            $cifradoSinIV= substr(base64_decode($_POST["cons_id"]), openssl_cipher_iv_length($cipher));
            $descifrado = openssl_decrypt($cifradoSinIV, $cipher, $key, OPENSSL_RAW_DATA, $iv_dec);

            require_once "../models/CloudStorage.php";
            require_once "../vendor/autoload.php";
            $cloud = new CloudStorage();

            $archivos = $cloud -> subirArchivos($descifrado, $_FILES["files"]);         
            $resultado = [];

            file_put_contents(
                __DIR__ . "/debug_files_api.json",
                json_encode($resultado, JSON_PRETTY_PRINT)
            );            
            error_log("RESPUESTA FINAL:");
            error_log(json_encode($resultado));
            echo json_encode($resultado);
        break;

        case "eliminar_archivo_bucket":
            $iv_dec = substr(base64_decode($_POST["cons_id"]), 0, openssl_cipher_iv_length($cipher));
            $cifradoSinIV= substr(base64_decode($_POST["cons_id"]), openssl_cipher_iv_length($cipher));
            $descifrado = openssl_decrypt($cifradoSinIV, $cipher, $key, OPENSSL_RAW_DATA, $iv_dec);

            require_once "../models/CloudStorage.php";
            require_once "../vendor/autoload.php";
            $cloud = new CloudStorage();

            $archivos = $cloud -> eliminarArchivo($descifrado, $_POST["docd_id"]);         
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
            $iv_dec = substr(base64_decode($_POST["cons_id"]), 0, openssl_cipher_iv_length($cipher));
            $cifradoSinIV= substr(base64_decode($_POST["cons_id"]), openssl_cipher_iv_length($cipher));
            $descifrado = openssl_decrypt($cifradoSinIV, $cipher, $key, OPENSSL_RAW_DATA, $iv_dec);

            $cloud = new CloudStorage();
            $contentType_GSutilresult = $cloud -> obtenerContentTypeyGsutil($descifrado);
            echo json_encode($contentType_GSutilresult);
        break;
    }
?>