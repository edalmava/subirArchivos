<?php
	class CargarArchivo {
		private $files;
		private $file;
		private $multiple;
		private $size;
		private $ext;
		private $overwrite;
		private $uploaddir = '../uploads/';
		// Media Types
		// http://www.iana.org/assignments/media-types/media-types.xhtml#application
		private $ext_permitidas = array('avi' => array('video/msvideo', 'video/avi', 'video/x-msvideo'),
										'bmp' => 'image/bmp',
										'css' => 'text/css',
										'csv' => 'text/csv',
										'html'=> 'text/html',
										'js'  => 'application/javascript',
										'doc' => 'application/msword',
										'docx'=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
										'ppt' => 'application/vnd.ms-powerpoint',
										'pptx'=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
										'xls' => 'application/vnd.ms-excel',
										'xlsx'=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
										'pdf' => 'application/pdf',
										'jpg' => array('image/jpeg', 'image/jpg'), 
		                                'png' => 'image/png', 
										'gif' => 'image/gif',
										'mp3' => 'audio/mpeg',
										'mp4' => array('audio/mp4', 'video/mp4'),
										'mpeg'=> array('audio/mpeg', 'video/mpeg'),
										'ogg' => array('audio/vorbis', 'application/ogg', 'audio/ogg', 'video/ogg'),
										'xml' => 'application/xml',
										'zip' => 'application/zip'										
									   );
		
		function __construct($file = '', $multiple = '0', $size_max = 2048, $ext, $overwrite = '1') {			
			$this->file = $file;
			//$this->files = $_FILES;
			$this->multiple = $multiple;
			$this->size = $size_max;
			$this->ext = $ext;
			$this->overwrite = $overwrite;
		}
		
		function verificarPropiedades() {
			if (empty($this->file)) {
				$this->file = 'userfile';
			}
            if (!in_array($this->multiple, array('0', '1'))) {
				$this->multiple = '0';
            }	
            if (!filter_var($this->size, FILTER_VALIDATE_INT)) {
				$this->size = 2048;
            }	
            if (!in_array($this->overwrite, array('0', '1'))) {
				$this->overwrite = '1';
            }
            $this->validarExtensiones($this->ext);
		}
		
		function validarExtensiones() {
			$exts = explode(",", str_replace(' ', '', $this->ext));
			$ext_permitidas = array_keys($this->ext_permitidas);
			foreach ($exts as $ext) {
				if (!in_array($ext, $ext_permitidas)) {
					throw new RuntimeException("Extension de archivo inválida: $ext");
				}
			}
			$this->ext = $exts;
		}		
		
		function checkError($error) {
			switch ($error) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException('No se ha enviado ningún archivo');
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException('Tamaño del archivo excede el límite.');
				case UPLOAD_ERR_PARTIAL:
				    throw new RuntimeException('El archivo subido fue sólo parcialmente cargado.');
				default:
					throw new RuntimeException('Error desconocido.');
			}
			return true;
		}
		
		function checkSizeMax($fileSize) {
			if ($fileSize > ($this->size * 1024)) {
				throw new RuntimeException('Tamaño del archivo excede el límite.');
			} else {
				return true;
			}
		}
		
		function getTipos() {			
			$tipos = array();
			foreach ($this->ext as $ext) {
				if (is_array($this->ext_permitidas[$ext])) {
					foreach ($this->ext_permitidas[$ext] as $typeMime) {
						$tipos[$typeMime] = $ext;
					}
				} else {
					$tipos[$this->ext_permitidas[$ext]] = $ext;
				}
			}
			return $tipos;
		}
		
		function checkMime($finfo, $fileTmp) {
			$tipos = $this->getTipos();
            $keys = array_keys($tipos);
            if (in_array($finfo->file($fileTmp), $keys)) {
				return $tipos[$finfo->file($fileTmp)];
            } else {
				throw new RuntimeException('Formato de archivo inválido.');
            }										
		}

        // Se necesita asegurar que el formulario no intenta cargar más archivos que max_file_uploads en una petición. 	
		/*function checkMaxUploadFiles() {
			$max_file_uploads = ini_get('max_file_uploads');
			if ($max_file_uploads === false) {
				$max_file_uploads = 20;
			}
			if (count($_FILES[$this->file]['error']) > $max_file_uploads) {
				throw new RuntimeException('Cantidad de archivos a subir excede los límites.');
			} else {
				return true;
			}
        }*/			
		
		function isSimple() {			
			if (isset($_FILES[$this->file]['error']) && !is_array($_FILES[$this->file]['error']) && !$this->multiple) {
                return true;
		    } else {
				throw new RuntimeException('Parametros Inválidos.');
			}
		}
		
		function isArray() {
			if (isset($_FILES[$this->file]['error']) && is_array($_FILES[$this->file]['error']) && $this->multiple) {
                return true;
		    } else {
				throw new RuntimeException('Parametros Inválidos.');
			}
		}
		
		function subirArchivo($ext) {
			$nombre = sprintf('%s.%s', sha1_file($_FILES[$this->file]['tmp_name']), $ext);
			if (!$this->overwrite) {
				$nombre = sprintf('%s.%s', sha1($_FILES[$this->file]['tmp_name'] . date('YmdHis')), $ext);
			}
			if (!move_uploaded_file($_FILES[$this->file]['tmp_name'], sprintf('%s%s', $uploaddir, $nombre))) {
				throw new RuntimeException('Fallo al mover el archivo subido.');
			} else {
				return json_encode(array("success" => "Archivo fue subido exitosamente.", "nombre" => $nombre));
			}
		}

        function cargarSimple() {
			try {
				$this->verificarPropiedades();
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				if ($this->isSimple()) {
					if ($this->checkError($_FILES[$this->file]['error']) && $this->checkSizeMax($_FILES[$this->file]['size']) && $ext = $this->checkMime($finfo, $_FILES[$this->file]['tmp_name'])) {						
						return $this->subirArchivo($ext);
					} 
				} 
			} catch(RuntimeException $e) {
				//throw $e;				
				return json_encode(array("error" => $e->getMessage()));
			}			
        }	

        function cargarArray() {
			try {
				$this->verificarPropiedades();
				//$finfo = new finfo(FILEINFO_MIME_TYPE);
				if ($this->isArray()) {					
					if ($this->checkError($_FILES[$this->file]['error'][0])) {
						return count($_FILES[$this->file]['error']);
					} 
				} 
			} catch(RuntimeException $e) {
				//throw $e;
				return json_encode(array("error" => $e->getMessage()));
			}		
        }			
	}