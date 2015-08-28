<?php
	header('Content-Type: application/json; charset=UTF-8');
	date_default_timezone_set('America/Bogota');
	setlocale(LC_ALL, "es_CO@COP", "es_CO", "esp");
	
	require('lib/cargar_archivo.php');
	
	try {
		$cargar = new CargarArchivo("archivos", 1, 100, 'jpg', '0');		
		//$cargar = new CargarArchivo("archivos2", 0, 100, 'jpg');
		//echo json_encode($cargar->cargar());						
		var_dump($cargar->cargar());
	} catch (RuntimeException $e) {
		echo json_encode(array("errors" => $e->getMessage()));
	}