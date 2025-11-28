<?php
	require_once("../../config/conexion.php");
	if(isset($_SESSION["usu_id"])){
		
?>
<!DOCTYPE html>
<html>
    <?php require_once("../MainHead/head.php"); ?>
    <title>Verifica tus Documentos</title>
<body class="with-side-menu">

<?php require_once("../MainHeader/header.php"); ?>

	<div class="mobile-menu-left-overlay"></div>


    <?php require_once("../MainNav/nav.php"); ?>

	<div class="page-content">
		<h2 id="lblnomconsulta">Consulta: </h2>
		<div class="box-typical box-typical-padding">
			
				
		</div>

	<?php require_once("../MainJs/js.php"); ?>
    <script type="text/javascript" src="detalleconsulta.js"></script>

<script src="js/app.js"></script>
</body>
</html>

<?php
	}else{
		header("Location:"."http://localhost:80/TLA_Revision_Docs/"."index.php");  
		//header("Location:"."https://support-tracking.tecnologisticaaduanal.com/"."index.php");
	}

?>