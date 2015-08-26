<?php
	class CargarArchivo {
		private $files;
		private $file;
		private $multiple;
		private $size;
		private $ext;
		private $overwrite;
		private $ext_permitidas = array('jpg' => array('image/jpeg', 'image/jpg'), 
		                                'png' => 'image/png', 
										'gif' => 'image/gif'
									   );
		
		function __construct($file = '', $multiple = '0', $size = 1024, $ext, $overwrite = '1') {			
			$this->file = $file;
			$this->files = $_FILES;
			$this->multiple = $multiple;
			$this->size = $size;
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
				$this->size = 1024;
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
				default:
					throw new RuntimeException('Error desconocido.');
			}
			return true;
		}
		
		function checkSize($fileSize) {
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
		
		function isSimple() {			
			if (isset($this->files[$this->file]['error']) && !is_array($this->files[$this->file]['error']) && !$this->multiple) {
                return true;
		    } else {
				throw new RuntimeException('Parametros Inválidos.');
			}
		}
		
		function isArray() {
			if (isset($this->files[$this->file]['error']) && is_array($this->files[$this->file]['error']) && $this->multiple) {
                return true;
		    } else {
				throw new RuntimeException('Parametros Inválidos.');
			}
		}
		
		function subirArchivo($ext) {
			$nombre = sprintf('../uploads/%s.%s', sha1_file($this->files[$this->file]['tmp_name']), $ext);
			if (!$this->overwrite) {
				$nombre = sprintf('../uploads/%s.%s', sha1($this->files[$this->file]['tmp_name'] . date('YmdHis')), $ext);
			}
			if (!move_uploaded_file($this->files[$this->file]['tmp_name'], $nombre)) {
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
					if ($this->checkError($this->files[$this->file]['error']) && $this->checkSize($this->files[$this->file]['size']) && $ext = $this->checkMime($finfo, $this->files[$this->file]['tmp_name'])) {						
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
				if ($this->isArray()) {
					if ($this->checkError($this->files[$this->file]['error'][0])) {
						return "Array";
					} 
				} 
			} catch(RuntimeException $e) {
				//throw $e;
				return json_encode(array("error" => $e->getMessage()));
			}		
        }			
	}