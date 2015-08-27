<?php
	header('Content-Type: application/json; charset=UTF-8');
	date_default_timezone_set('America/Bogota');
	setlocale(LC_ALL, "es_CO@COP", "es_CO", "esp");
	
	require('lib/cargar_archivo.php');
	
	try {
		/*$cargar = new CargarArchivo("archivos", 1, 100, 'jpg', '0');
		if($result = $cargar->cargarArray()) {*/
		$cargar = new CargarArchivo("archivos2", 0, 100, 'jpg', '0');
		if($result = $cargar->cargarSimple()) {
			//$success = json_decode($result);
			//echo $success->nombre;
			//var_dump($success);
			echo $result;
		} 
	} catch (RuntimeException $e) {
		echo json_encode(array("error" => $e->getMessage()));
	}