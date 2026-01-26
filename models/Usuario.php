<?php
    require_once dirname(__DIR__, 1) . '/config/config.php';

    use Dotenv\Dotenv;
    $config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();

    $key = $_ENV['APP_ENCRIPT_KEY'];
    $cipher = "aes-256-cbc";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
            
    //MODELO USUARIO
    class Usuario extends Conectar {

        // FUNCION LOGIN
        public function login() {
            // CONECTAR A LA BDD
            $conectar = parent::conexion();
            parent::set_names();

            // SI SE HIZO CLIC EN "Enviar" SE GUARDAN EL CORREO Y PASSWORD
            if (isset($_POST["enviar"])){
                $correo = $_POST["usu_correo"];
                $pass = $_POST["usu_pass"];

                // SI NO SE ENVIO NADA, MUESTRA M=2 (CAMPOS VACÍOS)
                if (empty($correo) and empty($pass)) {
                    header("Location:".Conectar::ruta() . "index.php?m=2");
                    exit();
                } else {
                    // SI SE ENVIO ALGO, SE CONSULTA LA INFO ENVIADA
                    $sql = "SELECT * FROM tm_usuario 
                            WHERE usu_correo = ?
                            AND est = 1";
                    $stmt = $conectar->prepare($sql);
                    $stmt->bindValue(1, $correo);;
                    $stmt->execute();
                    $resultado = $stmt->fetch();
                    
                    // SI HAY INFO DEL USUARIO SE GUARDAN EL ID, NOMBRE Y APELLIDO EN VARIABLES DE SESION
                    if (is_array($resultado) and count($resultado) > 0) {
                        $_SESSION[ "usu_id" ] = $resultado[ "usu_id" ];
                        $_SESSION[ "usu_nom" ] = $resultado[ "usu_nom" ];
                        $_SESSION[ "usu_ape" ] = $resultado[ "usu_ape" ];
                        $_SESSION[ "rol_id" ] = $resultado[ "rol_id" ];
                        // SE DIRECCIONA A LA RUTA view/Home
                        header("Location:".Conectar::ruta() . "view/Home/");
                    } else {
                        // SI NO HAY INFO DEL USUARIO, MUESTRA M=1 (DATOS INCORRECTOS)
                        header("Location:".Conectar::ruta() . "index.php?m=1");
                        exit();
                    }
                }
            }
        }    

        public function get_usuario() {
            $conectar= parent::conexion();
            parent::set_names();
            $sql="SELECT * FROM tm_usuario 
                  WHERE est = 1;";
            $sql=$conectar->prepare($sql);
            $sql->execute();
            return $resultado=$sql->fetchAll();
        }

        public function get_usuario_x_id($usu_id) {
            $conectar= parent::conexion();
            parent::set_names();
            $sql="SELECT * from tm_usuario 
                  where usu_id= ?;";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1, $usu_id);
            $sql->execute();
            return $resultado=$sql->fetchAll();
        }

        //BUSCAR USUARIOS DESDE EL CORREO
        public function get_usuario_x_correo($usu_correo){
            $conectar= parent::conexion();
            parent::set_names();
            $sql="SELECT * FROM tm_usuario WHERE usu_correo = ?";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1, $usu_correo);
            $sql->execute();
            return $resultado=$sql->fetchAll();
        }

        //INSERTAR USUARIO NUEVO
        public function insert_usuario($usu_nom,$usu_ape,$usu_correo,$usu_pass,$rol_id){
            //ENCRIPTADO DE LA CONTRASEÑA 
            $key = $_ENV['APP_ENCRIPT_KEY'];
            $cipher = "aes-256-cbc";
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
            $cifrado = openssl_encrypt($usu_pass, $cipher, $key,OPENSSL_RAW_DATA, $iv);
            $textoCifrado = base64_encode($iv . $cifrado);

            $conectar= parent::conexion();
            parent::set_names();
            $sql="INSERT INTO tm_usuario 
                    (usu_id, 
                    usu_nom, 
                    usu_ape, 
                    usu_correo, 
                    usu_pass, 
                    rol_id, 
                    fech_crea, 
                    fech_modi, 
                    fech_elim, 
                    est) 
                VALUES 
                    (NULL, ?, ?, ?, ?, ?,now(), NULL, NULL, '1');";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1, $usu_nom);
            $sql->bindValue(2, $usu_ape);
            $sql->bindValue(3, $usu_correo);
            $sql->bindValue(4, $textoCifrado);
            $sql->bindValue(5, $rol_id);
            $sql->execute();
            return $resultado=$sql->fetchAll();
        }

        //EDITAR USUARIO
        public function update_usuario($usu_nom,$usu_ape,$usu_correo,$usu_pass,$rol_id, $usu_id){
            //ENCRIPTADO DE LA CONTRASEÑA 
            $key = $_ENV['APP_ENCRIPT_KEY'];
            $cipher = "aes-256-cbc";
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
            $cifrado = openssl_encrypt($usu_pass, $cipher, $key,OPENSSL_RAW_DATA, $iv);
            $textoCifrado = base64_encode($iv . $cifrado);

            $conectar= parent::conexion();
            parent::set_names();
            $sql="UPDATE tm_usuario 
                SET 
                    usu_nom = ?,
                    usu_ape = ?, 
                    usu_correo = ?, 
                    usu_pass = ?, 
                    rol_id = ?,
                    fech_modi = NOW()
                WHERE
                    usu_id =?;";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1, $usu_nom);
            $sql->bindValue(2, $usu_ape);
            $sql->bindValue(3, $usu_correo);
            $sql->bindValue(4, $textoCifrado);
            $sql->bindValue(5, $rol_id);
            $sql->bindValue(6, $usu_id);
            $sql->execute();
            return $resultado=$sql->fetchAll();
        }

        //ELIMINAR USUARIO
        public function delete_usuario($usu_id){
            $conectar= parent::conexion();
            parent::set_names();
            $sql="UPDATE tm_usuario 
                  SET est = 0
                  WHERE usu_id = ?";
            $sql=$conectar->prepare($sql);
            $sql->bindValue(1, $usu_id);
            $sql->execute();
            return $resultado=$sql->fetchAll();
        }
    }
?>