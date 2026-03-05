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
    <title>Información - DocVerifAI</title>
<body class="with-side-menu">

<?php require_once("../MainHeader/header.php"); ?>

	<div class="mobile-menu-left-overlay"></div>


    <?php require_once("../MainNav/nav.php"); ?>

	<div class="page-content">
		<h2>
            ℹ️
			Información
		</h2>
		<div class="box-typical box-typical-padding">
			
				<h5 class="m-t-lg with-border">¿De que trata el Sitio?</h5>

				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Provident magnam quasi, vitae a in sed ratione magni alias nihil. Veniam ratione amet aliquam ex facilis possimus dignissimos porro eos. Corporis?</p>
				
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