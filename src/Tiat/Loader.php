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

namespace Tiat;

//
use Tiat\Core\Bootstrap;

use function define;
use function defined;
use function register_shutdown_function;

/**
 * Class Loader
 *
 * @package Tiat
 */
class Loader {
	
	//
	public const TIAT_VERSION      = '10.0.0';
	public const TIAT_VERSION_NAME = 'Hawk';
	
	//
	private object $_bootstrap;
	
	/**
	 * Loader constructor.
	 */
	public function __construct() {
		if(! defined('TIAT_VERSION')):
			define('TIAT_VERSION', self::TIAT_VERSION);
		endif;
		
		if(! defined('TIAT_VERSION_NAME')):
			define('TIAT_VERSION_NAME', self::TIAT_VERSION_NAME);
		endif;
		
		// Load Bootstrap
		require_once PATH_ROOT . 'Tiat' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Bootstrap.php';
		
		// Register shutdown
		register_shutdown_function(['\Tiat\Core\Bootstrap', 'destruct']);
	}
	
	/**
	 * @param    string    $path
	 * @param    array     $ini
	 */
	public function run(string $path = DIRECTORY_SEPARATOR, array $ini = []) : void {
		//
		$this->_bootstrap = new Bootstrap($ini ?? []);
		
		// Define path's
		$route = ['base' => '', 'core' => 'Tiat', 'library' => 'library'];
		
		// Register path constants & include_path
		$this->_bootstrap->setPath($path, $route);
		
		// Is this needed...maybe not. Some old version had this weird issue with this
		unset($route);
		
		return;
	}
	
	/**
	 *
	 */
	public function boot() : void {
		// Construct the Autoload & call the application
		if(TRUE === ( $status = $this->_bootstrap->init() )):
			// Call application MVC router
			$this->_bootstrap->factory();
		else:
			// Redirect user to ERROR page
			echo 'No application installed<br>';
		endif;
		
		return;
	}
}
