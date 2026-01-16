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

				<div>
					
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

					<button type="button" id="btncargar" class="btn btn-rounded btn-inline btn-secondary">
						<i class="fa fa-cloud-upload" aria-hidden="true"></i>
						Cargar Documentos
					</button>

					<button type="button" id="btnenviar" name="action" value="add" class="btn btn-rounded btn-inline btn_primary">
						<!-- <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stars" viewBox="0 0 16 16">
							<path d="M7.657 6.247c.11-.33.576-.33.686 0l.645 1.937a2.89 2.89 0 0 0 1.829 1.828l1.936.645c.33.11.33.576 0 .686l-1.937.645a2.89 2.89 0 0 0-1.828 1.829l-.645 1.936a.361.361 0 0 1-.686 0l-.645-1.937a2.89 2.89 0 0 0-1.828-1.828l-1.937-.645a.361.361 0 0 1 0-.686l1.937-.645a2.89 2.89 0 0 0 1.828-1.828zM3.794 1.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387A1.73 1.73 0 0 0 4.593 5.69l-.387 1.162a.217.217 0 0 1-.412 0L3.407 5.69A1.73 1.73 0 0 0 2.31 4.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387A1.73 1.73 0 0 0 3.407 2.31zM10.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732L9.1 2.137a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z"></path>
						</svg> -->
						✨
						Enviar y Procesar
					</button>

					<!-- <button type="button" id="btncerrar" name="action" value="add" class="btn btn-rounded btn-inline btn-default">
					<i class="fa fa-times-circle" aria-hidden="true"></i>	
					Cerrar Ticket</button> -->
				</div>
					
			</div><!--.row-->
				
		</div>

	<?php require_once("../MainJs/js.php"); ?>
    <script type="text/javascript" src="detalleconsulta.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.2/dist/purify.min.js"></script>
	<script src="js/app.js"></script>

	
</body>
</html>

<?php
	}else{
		header("Location:"."http://localhost:80/TLA_Revision_Docs/"."index.php");  
		
	}

?>