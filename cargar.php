<?php
	header('Content-Type: application/json; charset=UTF-8');
	date_default_timezone_set('America/Bogota');
	setlocale(LC_ALL, "es_CO@COP", "es_CO", "esp");
	
	require('lib/cargar_archivo.php');
	
	class Json {		
		public static function setErrors($errors) {			
			return json_encode(array("errors" => $errors));
		}
		
		public static function setSuccess($success) {
			return json_encode(array("success" => $success));
		}
	}
	
	try {
		$cargar = new CargarArchivo("archivos", 1, 100, 'jpg', '0');		
		//$cargar = new CargarArchivo("archivos2", 0, 100, 'jpg');		
		if ($cargar->validar()) {			
			if($cargar->upload()) {
				echo Json::setSuccess($cargar->getSuccess());
			} else {
				echo Json::setErrors($cargar->getErrors());
			}
		} else {
			echo Json::setErrors($cargar->getErrors());
		}
	} catch (RuntimeException $e) {
		echo Json::setErrors($e->getMessage());		
	}