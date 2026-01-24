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
		<div class="container-fluid">

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
								<label class="form-label semibold" for="fileElem">Adjuntar Documentos</label>
								<input type="file" name="fileElem" id="fileElem" class="form-control" multiple>
						</fieldset>
						
					</div>
	
					<div class="col-lg-12">
						<button type="button" id="btncargar" class="btn btn-rounded btn-inline btn-success">
							<i class="fa fa-cloud-upload" aria-hidden="true"></i>
							Cargar Documentos
						</button>
	
						<button type="button" id="btnenviar" class="btn btn-rounded btn-inline" disabled="">✨
							Enviar y Procesar</button>
						<!-- <button type="button" id="btnenviar" name="action" value="add" class="btn btn-rounded btn-inline btn_primary">
						✨
							Enviar y Procesar
						</button> -->
					</div>
						
				</div><!--.row-->
			</div>
		</div>	<!--.container fluid -->	
	</div> <!--.page content -->

	<?php require_once("../MainJs/js.php"); ?>
    <script type="text/javascript" src="detalleconsulta.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.2/dist/purify.min.js"></script>
	<!-- <script src="js/app.js"></script> -->
	
</body>
</html>

<?php
	}else{
		$URL_FRONTEND = $_ENV['URL_FRONTEND'];
		header("Location:"."$URL_FRONTEND"."index.php"); 
	}

?>