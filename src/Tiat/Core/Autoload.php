<?php
/**
 * Tiat Platform
 * LICENSE
 * This framework is using MIT licence
 *
 * @category     Platform
 * @package      Core
 * @author       Jan Theon <jan@jantheon.com>
 * @copyright    Copyright (c) 2005-2021 Jan Theon (All rights reserved)
 * @license      MIT. See also the license.txt
 */

/**
 * Bootstrap package for Tiat Platform
 *
 * @category     Platform
 * @package      Core
 * @copyright    Copyright (c) 2005-2021 Jan Theon
 * @license      MIT. See also the license.txt
 */
namespace Tiat\Core;

//
use Exception;
use RuntimeException;

use function spl_autoload_extensions;
use function spl_autoload_register;
use function spl_autoload_unregister;

/**
 * Class Autoload
 *
 * @package Tiat\Core
 */
class Autoload {
	
	//
	private Loader $_loader;
	
	/**
	 *
	 */
	final public function __construct() {
		$this->_registerAutoload(TRUE);
	}
	
	/**
	 * @param    bool    $status
	 *
	 * @return  bool
	 */
	private function _registerAutoload(bool $status = TRUE) : bool {
		// Use namespace if defined
		if($status):
			// Modify extension order
			spl_autoload_extensions('.php');
			
			// Register class
			return spl_autoload_register([__CLASS__, '_autoload'], TRUE, TRUE);
		else:
			return spl_autoload_unregister([__CLASS__, '_autoload']);
		endif;
	}
	
	/**
	 * @param    string    $class
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _autoload(string $class) : bool {
		if($class):
			try {
				$file = $this->_getLoader()->loadClass($class);
			} catch(Exception $e) {
				throw new RuntimeException('Class ' . $class . ' can not be loaded [' . $e . ']');
			} finally {
				return $file ?? FALSE;
			}
		endif;
		
		// False on failure
		return FALSE;
	}
	
	/**
	 * @return Loader|bool
	 */
	private function _getLoader() : Loader|bool {
		if($this->_getLoaderStatus()):
			return $this->_loader;
		endif;
		
		return FALSE;
	}
	
	/**
	 * @return bool
	 */
	private function _getLoaderStatus() : bool {
		if(empty($this->_loader)):
			// Set Loader filename & require it
			$filename = PATH_CORE . 'Core' . DIRECTORY_SEPARATOR . 'Loader.php';
			require_once $filename;
			
			//
			$this->_loader = new Loader();
			
			return ( $this->_loader instanceof Loader );
		endif;
		
		return (bool)$this->_loader;
	}
}
