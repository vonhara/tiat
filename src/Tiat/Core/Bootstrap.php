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
use Error;
use Exception;
use RuntimeException;
use Tiat\Router\Request\Route;

use function define;
use function defined;
use function file_exists;
use function ini_set;
use function is_scalar;
use function strtoupper;

/**
 * Class Bootstrap
 *
 * @package Tiat\Core
 */
class Bootstrap {
	
	//
	protected object $_router;
	
	//
	private bool $_autoload;
	private Autoload $_loader;    // MVC Router instance
	
	/**
	 * Bootstrap constructor.
	 *
	 * @param    array    $ini
	 */
	public function __construct(array $ini = []) {
		if(! empty($ini)):
			$this->setIni($ini);
		endif;
	}
	
	/**
	 * Set PHP ini params
	 *
	 * @param    array    $ini
	 *
	 * @return  bool
	 */
	final public function setIni(array $ini = []) : bool {
		if(! empty($ini)):
			foreach($ini as $key => $val):
				if(is_scalar($key) && is_scalar($val)):
					ini_set($key, $val);
				endif;
			endforeach;
			
			return TRUE;
		endif;
		
		return FALSE;
	}
	
	/**
	 * Custom destruct
	 */
	final public static function destruct() : void {
		return;
	}
	
	/**
	 * Define path constants & set them to include path
	 *
	 * @param    string    $base    Base path
	 * @param    array     $path    Constant names & path
	 *
	 * @return  bool
	 */
	final public function setPath(string $base = '', array $path = []) : bool {
		if(! empty($path)):
			foreach($path as $key => $val):
				$this->_definePath($key, $base . $val);
			endforeach;
			
			return TRUE;
		endif;
		
		return FALSE;
	}
	
	/**
	 * @param    string    $name
	 * @param    string    $path
	 *
	 * @return  bool
	 */
	private function _definePath(string $name, string $path) : bool {
		// Set vars
		$name = 'PATH_' . strtoupper($name);
		
		if($path[strlen($path) - 1] !== DIRECTORY_SEPARATOR):
			$path .= DIRECTORY_SEPARATOR;
		endif;
		
		if(file_exists($path)):
			if(! defined($name)):
				define($name, $path);
			endif;
			
			if(! str_contains(ini_get('include_path'), $path)):
				ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $path);
			endif;
		endif;
		
		return FALSE;
	}
	
	/**
	 * @return bool
	 * @throws Exception
	 */
	public function init() : bool {
		// Try start the Core
		if($this->_setAutoload()):
			//
			$this->_router = new Route();
			
			//
			return TRUE;
		else:
			// Throw an error
			// Oh my...
			throw new Error('Oh my...there are no autoloader. Try fix it.');
		endif;
	}
	
	/**
	 * @return  bool
	 * @throws  Exception
	 */
	final protected function _setAutoload() : bool {
		if(empty($this->_autoload)):
			// Load Autoload file
			$filename = PATH_CORE . 'Core' . DIRECTORY_SEPARATOR . 'Autoload.php';
			
			if(file_exists($filename)):
				require_once $filename;
				$this->_loader = new Autoload();
			else:
				throw new RuntimeException('Autoloader does not exists');
			endif;
			
			// Set autoloader status
			$this->_autoload = ( $this->_loader instanceof Autoload );
		endif;
		
		//
		return $this->_autoload;
	}
	
	/**
	 * Factory & execute router + application
	 *
	 * @throws Exception
	 */
	public function factory() : void {
		if($this->_router instanceof Route):
			// Prepare
			if($this->_router->factory()):
				// Launch the application
				$this->_router->execute($this->_router->prepare());
			endif;
		else:
			throw new Error('Router is not a correct instance');
		endif;
		
		return;
	}
}
