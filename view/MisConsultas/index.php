<?php
	require_once("../../config/conexion.php");
    
    use Dotenv\Dotenv;

	$config = App\Config::getInstance();
    $dotenv = Dotenv::createImmutable($config->getEnvPath(), '.env.' . $config->getEnvironment());
    $dotenv->load();
	if(isset($_SESSION["usu_id"])){
		
?>

<!DOCTYPE html>
<html>
    <?php require_once("../MainHead/head.php"); ?>
    <title>Mis Consultas</title>
<body class="with-side-menu">

<?php require_once("../MainHeader/header.php"); ?>

	<div class="mobile-menu-left-overlay"></div>


    <?php require_once("../MainNav/nav.php"); ?>

	<div class="page-content">
		<h2>Mis Consultas</h2>
		<div class="box-typical box-typical-padding">
            
        <div class="box-typical box-typical-padding" id="table">
            <table id="cons_data" class="table table-bordered table-striped table-vcenter js-dataTable-full">
                <thead>
                    <tr>
                        <th style="width: 2%;">#</th>
                        <th class="d-none d-sm-table-cell" style="width: 60%;">Titulo</th>
                        <th class="d-none d-sm-table-cell" style="width: 35%;">Fecha de Creación</th>
                        <th class="text-center" style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

        </div>

    </div>

	<?php require_once("../MainJs/js.php"); ?>
    <script type="text/javascript" src="misconsultas.js"></script>

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