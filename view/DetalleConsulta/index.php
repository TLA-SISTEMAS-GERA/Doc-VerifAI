<?php
	require_once("../../config/conexion.php");
	require_once("../../vendor/autoload.php");

	use Dotenv\Dotenv;

	$config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();
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

		<section class="activity-line" id="lbldetalle">
		
			
		</section><!--.activity-line-->

		<div class="box-typical box-typical-padding" id="pnldetalle">
			<div class="row">
					<div class="col-lg-12">
						<fieldset class="form-group">
							<div class="summernote-theme-1">
								<textarea class="summernote" id="prompt" name="prompt"></textarea>
							</div>
						</fieldset>
						
					</div>

					<div class="col-lg-12">
						<fieldset class="form-group">
								<label class="form-label semibold" for="fileElem">Adjuntar Pedimento</label>
								<input type="file" name="fileElem" id="fileElem" class="form-control" multiple>
						</fieldset>
						
					</div>
					<div class="col-lg-12">
						<!-- <button id="btnUrgente" type="button" class="btn btn-rounded btn-inline btn-secondary">
							<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>	
							Marcar como Urgente
						</button>

						<button type="button" id="btnseguidores" name="action" value="add" class="btn btn-rounded btn-inline btn-secondary">
							<i class="fa fa-users" aria-hidden="true"></i>
							Añadir Seguidor/es
						</button> -->

						<button type="button" id="btnenviar" name="action" value="add" class="btn btn-rounded btn-inline btn_primary">
						<i class="fa fa-paper-plane" aria-hidden="true"></i>	
						Enviar</button>

						<!-- <button type="button" id="btncerrar" name="action" value="add" class="btn btn-rounded btn-inline btn-default">
						<i class="fa fa-times-circle" aria-hidden="true"></i>	
						Cerrar Ticket</button> -->
					</div>
					
			</div><!--.row-->
				
		</div>

	<?php require_once("../MainJs/js.php"); ?>
    <script type="text/javascript" src="detalleconsulta.js"></script>

	<script src="js/app.js"></script>
</body>
</html>

<?php
	}else{
		$URL_FRONTEND = $_ENV['URL_FRONTEND'];
		header("Location:"."$URL_FRONTEND"."index.php");  
		//header("Location:"."https://support-tracking.tecnologisticaaduanal.com/"."index.php");
	}

?>