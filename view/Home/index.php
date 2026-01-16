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
		<h2>
			🏠
			Inicio
		</h2>
		<div class="box-typical box-typical-padding">
			
				<h5 class="m-t-lg with-border">Crea una consulta nueva</h5>

				<p>(*) Campo obligatorio</p>
				<div class="row">
					<form method="post" id="consul_form">

						<input type="hidden" name="usu_id" id="usu_id" value="<?php echo $_SESSION["usu_id"] ?>">

						<div class="col-lg-12">
							<fieldset class="form-group">
								<label class="form-label semibold" for="cons_nom">Título (*)</label>
								<input type="text" class="form-control" id="cons_nom" name="cons_nom" placeholder="Ingrese el título" required>
							</fieldset>
						</div>

							
						<div class="col-lg-12">
							<button type="submit" id="btnguardar"name="action" value="add" class="btn btn-rounded btn-inline btn_primary">
							<!-- <i class="fa fa-floppy-o" aria-hidden="true"></i>	 -->
							💾
							Guardar y Crear</button>
						</div>
					</form>
				</div><!--.row-->
			</div>

	<?php require_once("../MainJs/js.php"); ?>
    <script type="text/javascript" src="home.js"></script>

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