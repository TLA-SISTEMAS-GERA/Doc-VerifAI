<?php
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
                            AND usu_pass = ?
                            AND est = 1";
                    $stmt = $conectar->prepare($sql);
                    $stmt->bindValue(1, $correo);
                    $stmt->bindValue(2, $pass);
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

        public function get_usuario(){
            $conectar= parent::conexion();
            parent::set_names();
            $sql="SELECT * FROM tm_usuario 
                  WHERE est=1;";
            $sql=$conectar->prepare($sql);
            $sql->execute();
            return $resultado=$sql->fetchAll();
        }
    }
?>