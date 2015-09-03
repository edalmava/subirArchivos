<?php
	/**
	 * Clase CargarArchivo
	 *
	 * Permite gestionar la subida de archivos al servidor(upload)
	 *
	 * @package CargarArchivo
	 * @author  edalmava
	 * @version v0.2 31-08-2015 11:55:00
	 */
	class CargarArchivo {
        /**
         * @var '0' | '1' $multiple   Subida de múltiples archivos
		 * @var integer   $size_max   Tamaño máximo del archivo o archivos subidos(en KB)
		 * @var string    $ext        Extensiones permitidas separadas por comas
		 * @var '0' | '1' $overwrite  Sobreescribir el archivo en el momento de moverlo
         * @var string    $file       Nombre del campo tipo file del formulario enviado
		 * @var boolean   $validate   Para saber si se ha validado la subida
		 * @var array     $errors     Errores en la subida
		 * @var array     $success    Éxito en la subida
		 * @var string    $uploaddir  Ruta al directorio de subida
		 * @var array $ext_permitidas Listado de extensiones permitidas y sus correspondientes tipos MIME
		 */		
		private $multiple, $size, $ext, $overwrite;
		
		protected $uploaddir = '../uploads/';
		protected $file, $validate, $errors, $success;
		
		// Media Types
		// http://www.iana.org/assignments/media-types/media-types.xhtml#application
		public $ext_permitidas = array(
									'avi' => array('video/msvideo', 'video/avi', 'video/x-msvideo'),
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
		
		/**
		 * Constructor
		 *
		 * Inicializa propiedades
		 *		 
		 * @param string    $file      Nombre del campo tipo file del formulario enviado.  Por defecto se toma 'userfile'
		 * @param '0' | '1' $multiple  Subida de múltiples archivos.  Por defecto es '0' solo se permite subir un único archivo
		 * @param integer   $size_max  Tamaño máximo del archivo o archivos subidos(en KB).  Por defecto es 2048 (2MB)
		 * @param string    $ext       Extensiones permitidas separadas por comas con o sin espacios.  Por ejemplo: 'gif, jpg, png'
		 * @param '0' | '1' $overwrite Sobreescribir el archivo en el momento de moverlo.  Por defecto es '1' se sobreescribe el archivo
		 */
		function __construct($file = '', $multiple = '0', $size_max = 2048, $ext, $overwrite = '1') {			
			$this->file = $file;			
			$this->multiple = $multiple;
			$this->size = $size_max;
			$this->ext = $ext;
			$this->overwrite = $overwrite;			
		}
		
		/**
		 * Función verificar propiedades
		 *
		 * Función que permite verificar si las propiedades inicializadas en el constructor son válidas 
		 * en caso negativo se inicializan correctamente a sus valores por defecto excepto las extensiones donde
		 * se genera un excepción
		 *
		 * @param void
		 *
		 * @return void | RuntimeException
		 */
		protected function verificarPropiedades() {
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
		
		/**
		 * Función validar extensiones
		 *
		 * Función que permite validar si las extensiones pasadas por el constructor estan permitidas
		 * 
		 * @param void
		 *
		 * @return void | RuntimeException
		 */
		protected function validarExtensiones() {
			$exts = explode(",", str_replace(' ', '', $this->ext));
			$ext_permitidas = array_keys($this->ext_permitidas);
			foreach ($exts as $ext) {
				if (!in_array($ext, $ext_permitidas)) {
					throw new RuntimeException("Extension de archivo inválida o no permitida: $ext");
				}
			}
			$this->ext = $exts;
		}		
		
		/**
		 * Función para validar los códigos de error
		 *
		 * Función que permite verificar si el archivo fue subido exitosamente o ha ocurrido un error
		 *
		 * @param  integer $error Código de error del archivo subido
         * 		 
         * @return true|false 
		 */
		protected function checkError($error) {
			switch ($error) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:					
					$this->errors[] = 'No se ha enviado ningún archivo.';
					return false;
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:					
					$this->errors[] = 'Tamaño del archivo excede el límite.';
					return false;
				case UPLOAD_ERR_PARTIAL:				    
					$this->errors[] = 'El archivo subido fue sólo parcialmente cargado.';
					return false;
				default:					
					$this->errors[] = 'Error desconocido.';
					return false;
			}
			return true;
		}
		
		/**
		 * Función para validar el tamaño máximo
		 *
		 * Función que permite verificar si el archivo tiene un tamaño inferior al tamaño dado por la propiedad $size
		 *
		 * @param  integer $fileSize Tamaño del archivo subido
         * 		 
         * @return true|false 
		 */		
		protected function checkSizeMax($fileSize) {
			if ($fileSize > ($this->size * 1024)) {				
				$this->errors[] = 'Tamaño del archivo excede el límite.';
				return false;
			} else {
				return true;
			}
		}
		
		protected function getTipos() {			
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
		
		/**
		 * Función para validar el tipo mime
		 *
		 * Función que permite verificar si el archivo tiene un tipo MIME permitido en cuyo caso retorna la extensión
		 * del mismo que será utilizada en caso de mover el archivo
		 *
		 * @param string $fileTmp Nombre temporal del archivo
         * 		 
         * @return $ext | false   Retorna la extensión del archivo o false
		 */		
		protected function checkMime($finfo, $fileTmp) {
			$tipos = $this->getTipos();
            $keys = array_keys($tipos);
            if (in_array($finfo->file($fileTmp), $keys)) {
				return $tipos[$finfo->file($fileTmp)];
            } else {				
				$this->errors[] = 'Formato de archivo inválido.';
				return false;
            }										
		}

        /**
		 * @todo Se necesita asegurar que el formulario no intenta cargar más archivos que max_file_uploads en una petición. 	
		 */
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
		
		/**
		 * Función para validar si es subida de un solo archivo
		 *		
		 * @param void
         * 		 
         * @return true | false 
		 */		
		protected function isSimple() {			
			return (isset($_FILES[$this->file]['error']) && !is_array($_FILES[$this->file]['error']) && !$this->multiple)?true:false;                
		}
		
		/**
		 * Función para validar si es subida de múltiples archivos
		 *		
		 * @param void
         * 		 
         * @return true | false 
		 */		
		protected function isArray() {
			return (isset($_FILES[$this->file]['error']) && is_array($_FILES[$this->file]['error']) && $this->multiple)?true:false;
		}
		
		protected function subirArchivo($nameFile, $ext) {
			$nombre = sprintf('%s.%s', sha1_file($nameFile), $ext);
			if (!$this->overwrite) {
				$nombre = sprintf('%s.%s', sha1($nameFile . date('YmdHis')), $ext);
			}
			if (!move_uploaded_file($nameFile, sprintf('%s%s', $this->uploaddir, $nombre))) {				
				$this->errors[] = 'Fallo al mover el archivo subido.';
				return false;
			} else {				
				return $nombre;
			}
		}
		
		protected function subidaMultiple($exts) {
			$nombres = array();
			foreach ($_FILES[$this->file]['tmp_name'] as $key => $name) {				
				if ($nombre = $this->subirArchivo($name, $exts[$key])) {
					$nombres[$key] = $nombre;
				} else {
					$this->errors[] = 'Fallo al mover el archivo subido: ' . $_FILES[$this->file]['name'][$key];
					//$this->eliminarArchivos();
					return false;
				}				
			}
			$this->success = array("msg" => "Archivos fueron subidos exitosamente.", "nombres" => $nombres);
			return true;
		}
		
		protected function cargar() {            		
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			if ($this->isSimple()) {
				$ext = $this->checkMime($finfo, $_FILES[$this->file]['tmp_name']);					
				if ($nombre = $this->subirArchivo($_FILES[$this->file]['tmp_name'], $ext)) {
					$this->success = array("msg" => "Archivo fue subido exitosamente.", "nombre" => $nombre);
					return true;
				} else {
					return false;
				}				
			} else if ($this->isArray()) {
				$exts = array();					
				foreach ($_FILES[$this->file]['error'] as $key => $error) {											
					$exts[$key] = $this->checkMime($finfo, $_FILES[$this->file]['tmp_name'][$key]);									
				}
				return ($this->subidaMultiple($exts))?true:false;					
			} 				
		}  
		
		public function validar() {
			$this->verificarPropiedades();
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			if ($this->isSimple()) {
				if ($this->checkError($_FILES[$this->file]['error']) && $this->checkSizeMax($_FILES[$this->file]['size']) && $this->checkMime($finfo, $_FILES[$this->file]['tmp_name'])) {						
				    $this->validate = true;
					return true;
				} else {
					$this->validate = false;
					return false;
				}
			} else if ($this->isArray()) {
				foreach ($_FILES[$this->file]['error'] as $key => $error) {
					if ($this->checkError($_FILES[$this->file]['error'][$key]) && $this->checkSizeMax($_FILES[$this->file]['size'][$key]) && $this->checkMime($finfo, $_FILES[$this->file]['tmp_name'][$key])) {						
						continue;
					} else {
						$this->validate = false;
						return false;
                    }	
				}
				$this->validate = true;
				return true;
			} else {
                $this->validate = false;				
				throw new RuntimeException('Error al cargar: Parametros inválidos');
			}		
		}
		
		public function upload() {
			if ($this->validate) {
				return ($this->cargar())?true:false; 				 
			} else {
				return ($this->validar())?($this->cargar()):false; 				
			}
		}
		
		public function getErrors() {
			return $this->errors;
		}
		
		public function getSuccess() {
			return $this->success;
		}

        public static function getConfiguracion() {
			$conf = array();
			$conf['file_uploads'] = ini_get('file_uploads');
			$conf['upload_tmp_dir'] = ini_get('upload_tmp_dir');
			$conf['max_input_vars'] = ini_get('max_input_vars');
			$conf['upload_max_filesize'] = ini_get('upload_max_filesize');
			$conf['max_file_uploads'] = ini_get('max_file_uploads');
			
			return json_encode($conf);
        }			
	}