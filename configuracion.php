<?php
	header('Content-Type: application/json; charset=UTF-8');
	date_default_timezone_set('America/Bogota');
	setlocale(LC_ALL, "es_CO@COP", "es_CO", "esp");
	
	require('lib/cargar_archivo.php');
	
	echo CargarArchivo::getConfiguracion();