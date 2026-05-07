<?php 
	if($_SESSION["rol_id"] == 1){
?>
	
		<nav class="side-menu">
				<ul class="side-menu-list">
					<li class="blue">
						<a href="..\Home\">
							<span>
								<!-- <i class="fa fa-home" aria-hidden="true"></i> -->
								🏠
								<span class="lbl">Inicio</span>
							</span>  
						
						</a>  

						<a href="..\MisConsultas\">
							<span>
								<!-- <i class="fa fa-table" aria-hidden="true"></i> -->
								📃
								<span class="lbl">Mis Consultas</span>
							</span>  
						
						</a> 
					</li>
				
				</ul>
			
				<section>
					<header class="side-menu-title">INFORMACIÓN</header>
					<ul class="side-menu-list">
						<li>
							<a href="..\Info\info.php">
								<!-- <i class="fa fa-question-circle" aria-hidden="true"></i> -->
								ℹ️
								<span class="lbl">¿De que trata el sitio?</span>
							</a>
						</li>
					</ul>
				</section>
			</nav><!--.side-menu-->

<?php 
	} else if ($_SESSION["rol_id"] == 2) { // SESION DE ADMINISTRADOR
		?>

		<body class="with-side-menu theme-side-madison-caribbean.html">
		
			<nav class="side-menu">
				<ul class="side-menu-list">
					<li class="blue">
						<a href="..\Home\">
							<span>
								<!-- <i class="fa fa-home" aria-hidden="true"></i> -->
								🏠
								<span class="lbl">Inicio</span>
							</span>  
						
						</a>  

						<a href="..\MisConsultas\">
							<span>
								<!-- <i class="fa fa-table" aria-hidden="true"></i> -->
								📃
								<span class="lbl">Mis Consultas</span>
							</span>  
						
						</a> 

						<a href="..\MtnUsuario\">
							<span>
								<!-- <i class="fa fa-table" aria-hidden="true"></i> -->
								👥
								<span class="lbl">Usuarios</span>
							</span>  
						
						</a> 
					</li>
				
				</ul>
			
				<section>
					<header class="side-menu-title">INFORMACIÓN</header>
					<ul class="side-menu-list">
						<li>
							<a href="..\Info\info.php">
								<!-- <i class="fa fa-question-circle" aria-hidden="true"></i> -->
								ℹ️
								<span class="lbl">¿De que trata?</span>
							</a>
						</li>
					</ul>
				</section>
			</nav><!--.side-menu-->
		</body>
		<?php
	}
	if($_SESSION["rol_id"] == 3){
		?>
			
				<nav class="side-menu">
						<ul class="side-menu-list">
							<li class="blue">
								<a href="..\Home\">
									<span>
										<!-- <i class="fa fa-home" aria-hidden="true"></i> -->
										🏠
										<span class="lbl">Inicio</span>
									</span>  
								
								</a>  
		
								<a href="..\MisConsultas\">
									<span>
										<!-- <i class="fa fa-table" aria-hidden="true"></i> -->
										📃
										<span class="lbl">Mis Consultas</span>
									</span>  
								
								</a> 
							</li>
						
						</ul>
					
						<section>
							<header class="side-menu-title">INFORMACIÓN</header>
							<ul class="side-menu-list">
								<li>
									<a href="..\Info\info.php">
										<!-- <i class="fa fa-question-circle" aria-hidden="true"></i> -->
										ℹ️
										<span class="lbl">¿De que trata el sitio?</span>
									</a>
								</li>
							</ul>
						</section>
					</nav><!--.side-menu--><?php
	}

?>