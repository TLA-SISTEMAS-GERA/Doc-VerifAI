<?php
    require_once("../../config/conexion.php");
    session_destroy();
    header("Location:"."http://localhost:80/Doc-VerifAI/"."index.php");  
   //header("Location:"."http://doc-verifai.tecnologisticaaduanal.com/"."index.php");  
?>