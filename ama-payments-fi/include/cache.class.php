<?php

class cache {
	
	/**
	 * @var $moduleName string
	 * We hold module name that we cache
	 */
	private $moduleName;
	
	/**
	 * @var $cacheFile string
	 * We itentify certain cache with this hash
	 */
	private $cacheFile;
	
	/**
	 * @var $cacheDir string
	 * File cache dir
	 */
	private $cacheDir = '/cache/';
	
	/**
	 * @var $disableCache bool
	 * Disable cache when we dont want to use it
	 */
	public $disableCache = true;
	
	
	public function __construct( $moduleName, $fileString = '' ) {
		
		#print_r($fileString);
		$this->moduleName = $moduleName;
		$this->cacheFile  = md5($fileString).'.inc';
		$this->cacheDir   = _DIR_PATH . $this->cacheDir.$this->moduleName.'/';
		
		/**
		 * check for the dir if not exist create
		 */
		if ( !is_dir($this->cacheDir) ) @mkdir($this->cacheDir); 
	}
	
	/**
	 * We check for cache file
	 * Do we use get or set
	 * 
	 * @return bool
	 */
	public function check($defaultTime = '5 minute') {
		
		/**
		 * Sometimes we dont want to use cache
		 */
		if ( $this->disableCache ) return false;
		
		return file_exists($this->cacheDir.$this->cacheFile) && filemtime($this->cacheDir.$this->cacheFile) > strtotime('now -'.$defaultTime);
	}
	
	/**
	 * WE get value of current cache
	 * 
	 * @return array
	 */
	public function get() {
		return unserialize(gzuncompress(file_get_contents($this->cacheDir.$this->cacheFile)));
	}
	
	/**
	 * WE set value of current cache
	 * 
	 * @param  value
	 * @return void
	 */
	public function set( $value ) {
		if ( $this->disableCache ) return;
		$this->write(gzcompress(serialize($value)));
	}
	
	/**
	 * WE destroy cache
	 * 
	 * @return void
	 */
	public function destroyFile() {
		@unlink($this->cacheDir.$this->cacheFile);
	}
	
	/**
	 * WE clear cache
	 * 
	 * @return void
	 */
	public function clear() {
		$this->rrmdir($this->cacheDir);
	}
	
	/**
	 * WE write cache
	 * 
	 * @return void
	 */
	public function write( $value ) {
		$fp = fopen($this->cacheDir.$this->cacheFile, 'w+');
		@fwrite( $fp, $value );
		fclose($fp);
	}
	
	/**
	 * Custom remove dir function
	 * 
	 * @return void
	 */
	private function  rrmdir($dir) {
	   if (is_dir($dir)) {
	     $objects = scandir($dir);
	     foreach ($objects as $object) {
	       if ($object != "." && $object != "..") {
	         if (filetype($dir.$object) == "dir") rrmdir($dir.$object); else @unlink($dir.$object);
	       }
	     }
	     reset($objects);
	     @rmdir($dir);
	   }
 	} 
	
}


?>