<?php
	header('Content-Type: application/json; charset=UTF-8');
	date_default_timezone_set('America/Bogota');
	setlocale(LC_ALL, "es_CO@COP", "es_CO", "esp");

	require('lib/cargar_archivo.php');

	/**
	 * Clase Json
	 *
	 * Permite gestionar la representación en formato JSON de los mensajes de error y éxito
	 *
	 * @package Json
	 * @author  edalmava
	 * @version v0.2 31-08-2015 15:35:00
	 */
	class Json {
		public static function setErrors($errors) {
			return json_encode(array("errors" => $errors));
		}

		public static function setSuccess($success) {
			return json_encode(array("success" => $success));
		}
	}

	try {
		$cargar = new CargarArchivo("archivos", 1, 4096, 'png,jpg,gif,pdf', '0'); // Ejemplo de subida múltiple sin sobreescritura
		//$cargar = new CargarArchivo("archivos2", 0, 100, 'jpg');		     // Ejemplo de subida de un solo archivo con sobreescritura
    $cargar->setUploadDir('uploads/');

		if ($cargar->validar()) {
			if($cargar->upload()) {
				//if ($cargar->eliminarArchivo($cargar->getSuccess()['nombre'])) {				
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
