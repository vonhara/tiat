<?php
/**
 * Tiat Platform
 * LICENSE
 * This framework is using MIT licence
 *
 * @category     Platform
 * @package      Router
 * @author       Jan Theon <jan@jantheon.com>
 * @copyright    Copyright (c) 2005-2021 Jan Theon (All rights reserved)
 * @license      MIT. See also the license.txt
 */

/**
 * Bootstrap package for Tiat Platform
 *
 * @category     Platform
 * @package      Router
 * @copyright    Copyright (c) 2005-2021 Jan Theon
 * @license      MIT. See also the license.txt
 */
namespace Tiat\Router\Request;

//
use Exception;
use JetBrains\PhpStorm\Pure;
use Tiat\Error\Client;
use Tiat\Tools\Config;
use Tiat\Uri\Base;

use function ctype_alnum;
use function define;
use function defined;
use function explode;
use function implode;
use function is_object;
use function mb_strtolower;
use function strlen;
use function trim;
use function ucwords;
use function urldecode;

/**
 * Class Route
 *
 * @package Tiat\Router\Request
 */
class Route extends Request {

	//
	use Client;

	//
	public const defaultNamespace  = 'default';
	public const defaultModule     = 'default';
	public const defaultController = 'index';
	public const defaultAction     = 'index';

	// Name of
	protected string $_nameNamespace  = self::defaultNamespace;
	protected string $_nameModule     = self::defaultModule;
	protected string $_nameController = self::defaultController;
	protected string $_nameAction     = self::defaultAction;

	// Directories
	protected string $_dirNamespace;
	protected string $_dirModule;
	protected string $_dirController;
	protected string $_dirAction;

	// Filename
	protected string $_fileController;

	// Controller instance name
	protected string $_instance;

	// Config instance
	protected object $_config;

	// Router settings
	protected array $_settings       = [];
	protected array $_configSettings = [];

	// Common vars
	protected string $_handler;
	protected array  $_routerMap;
	protected array  $_serverMap;

	// Route map
	protected array $_map;

	/**
	 * @return bool
	 */
	public function factory() : bool {
		// Solve URI (URI and query)
		$uri = (new Base)->factory();

		// Connect URI to config
		$this->_getConfigObject();

		// Read settings config
		$this->_config->readConfig('settings', $_SERVER['SERVER_NAME']);

		// Get config ROUTER params
		$this->_settings = $this->_config->getConfig('router');

		//
		if(! defined('APPS_RUNMODE')):
			define('APPS_RUNMODE', 'production');
		endif;

		// Get server map
		if(! empty($map = $this->_config->getConfig('servers'))):
			$this->_serverMap = $map;
		endif;

		// Set application config for later use
		$this->_configSettings = $this->_config->getConfig();

		// Set handler if exists
		$this->_handler = (string)($this->_settings['handler'] ?? '');

		// Modify URI array key names for Router (Map)
		// Connect params to URI params & Resolve Routing MAP (example if SMART ROUTING is used)
		// Also resolve application namespace etc
		$this->_routerMap = $this->_setUri($uri, $this->_settings['namespace'] ?? self::defaultNamespace);

		//
		return TRUE;
	}

	/**
	 *
	 */
	private function _getConfigObject() : void {
		if(empty($this->_config)):
			$this->_config = new Config();
		endif;

		return;
	}

	/**
	 * @param    array     $uri
	 * @param    string    $ns
	 *
	 * @return  array
	 */
	final protected function _setUri(array &$uri, string $ns) : array {
		// If URI parameters defined OR namespace defined
		if(! empty($uri) || ! empty($ns)):
			// Define namespace name
			if($ns):
				$array['namespace'] = (string)trim($ns);
			else:
				$array['namespace'] = self::defaultNamespace;
			endif;

			// Re-index the uri array values
			// This is needed specially when manipulating the PATH example with INTL
			$uri = array_values($uri);

			// Define module name
			if(isset($uri[0]) && trim($uri[0]) !== ''):
				$array['module'] = (string)trim($uri[0]);
			else:
				$array['module'] = self::defaultModule;
			endif;

			// Define controller name
			if(isset($uri[1]) && trim($uri[1]) !== ''):
				$array['controller'] = (string)trim($uri[1]);
			else:
				$array['controller'] = self::defaultController;
			endif;

			// Define action name
			if(isset($uri[2]) && trim($uri[2]) !== ''):
				$array['action'] = (string)trim($uri[2]);
			else:
				$array['action'] = (string)self::defaultAction;
			endif;

			// Define page name
			// Notice! If not in URI params then this is NOT used
			if(isset($uri[3]) && trim($uri[3]) !== ''):
				$array['page'] = (string)trim($uri[3]);
			endif;

			return $array;
		else:
			// PAGE is optional so do NOT add it as default
			$uri['namespace']  = self::defaultNamespace;
			$uri['module']     = self::defaultModule;
			$uri['controller'] = self::defaultController;
			$uri['action']     = self::defaultAction;
		endif;

		return [];
	}

	/**
	 * Launch the application
	 *
	 * @return Action|bool
	 */
	public function prepare() : Action|bool {
		// Prepare the Router
		if($this->_prepareRouter($this->_routerMap, ['lang' => ($_SESSION['lang'] ?? 'en')])):
			// Get the Controller
			if(is_object($c = $this->getController())):
				return $c;
			else:
				$this->_setError('Problem with controller: controller does not exists');
			endif;
		else:
			// Return FALSE
			$this->_setError('Problem with route');
		endif;

		return FALSE;
	}

	/**
	 * Set application ready for dispatcher
	 * Set & get application controllers, plugins etc
	 *
	 * @param    array    $map
	 * @param    array    $vars    Custom VARS for router (like LANG)
	 *
	 * @return  bool
	 */
	protected function _prepareRouter(array $map = [], array $vars = []) : bool {
		// Decode the Route Map
		$map = $this->_checkRoute($map);

		// Set Routing map
		// If even one phase is returning FALSE then application will not run
		// Using nested if-else is easy so error can be catch
		if($this->_setNamespace($map['namespace']) && $this->_setModule($map['module']) &&
		   $this->_setController($map['controller']) && $this->_setAction($map['action'])):
			// Write route map
			$this->_routeRewrite($map, $vars);

			// Return true
			return TRUE;
		else:
			$this->_setError('Something has gone wrong! (' . __CLASS__ . '::' . __FUNCTION__ . ')');
		endif;

		return FALSE;
	}

	/**
	 * UTF-8 encode route parts
	 *
	 * @return  array
	 */
	#[Pure] protected function _checkRoute(array $map = []) : array {
		if(count($map)):
			foreach($map as $key => $val):
				$map[$key] = urldecode($val);
			endforeach;
		endif;

		return $map ?? [];
	}

	/**
	 * @param    string    $key
	 *
	 * @return  bool
	 */
	protected function _setNamespace(string $key = NULL) : bool {
		if(isset($key[0]) && ctype_alpha($key)):
			// Convert namespace name
			$this->_nameNamespace = ucfirst(mb_strtolower(trim($key)));

			// Set namespace directory
			return $this->_setNamespaceDirectory(mb_strtolower($key));
		else:
			$this->_setError('Namespace is not correctly formatted (' . $key . ')');
		endif;

		return FALSE;
	}

	/**
	 * Define namespace directory
	 *
	 * @param    string    $key
	 *
	 * @return  bool
	 */
	protected function _setNamespaceDirectory(string $key) : bool {
		if(! empty($key)):
			// If HANDLER exists then try it
			if(! empty($this->_handler) && is_string($this->_handler)):
				$path = PATH_APPS . mb_strtolower($this->_handler) . DIRECTORY_SEPARATOR;
			elseif(strtolower($key) !== self::defaultNamespace):
				// Do NOT use namespace if it's a "default"
				$path = PATH_APPS . $key . DIRECTORY_SEPARATOR;
			else:
				$path = PATH_APPS;
			endif;

			// Set namespace dir
			$this->_dirNamespace = $path;

			// Define constants (even they don't exists)
			if(! defined('PATH_MODULES')):
				//
				define('PATH_MODULES', $this->_dirNamespace . 'modules' . DIRECTORY_SEPARATOR);

				// Set to include path (if exists)
				if(file_exists(PATH_MODULES)):
					ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . PATH_MODULES);
				endif;
			endif;

			return TRUE;
		endif;

		return FALSE;
	}

	/**
	 * @param    string    $key
	 *
	 * @return  bool
	 */
	protected function _setModule(string $key) : bool {
		if($this->_checkName($key)):
			// Set module name
			$this->_nameModule = str_replace(['-', ' '], '', mb_strtolower(trim($key)));

			// Set module directory
			return $this->_setModuleDirectory($key);
		else:
			if($this->_nameModule === self::defaultModule):
				return TRUE;
			endif;
		endif;

		return FALSE;
	}

	/**
	 * Check MVC var names
	 *
	 * @param    string    $unformatted
	 *
	 * @return bool
	 */
	protected function _checkName(string $unformatted) : bool {
		return isset($unformatted[0]) and
		       (ctype_alnum(str_replace('-', '', $unformatted)) || ctype_digit(str_replace('-', '', $unformatted)) ||
		        ctype_alnum(str_replace(' ', '', $unformatted)));
	}

	/**
	 * @param    string    $key
	 *
	 * @return  bool
	 */
	protected function _setModuleDirectory(string $key) : bool {
		if(! empty($key)):
			// Set module directory
			$this->_dirModule =
				$this->_dirNamespace . 'controllers' . DIRECTORY_SEPARATOR . $this->_nameModule . DIRECTORY_SEPARATOR;

			// Set include path
			ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . $this->_dirNamespace . 'controllers' .
			                        DIRECTORY_SEPARATOR);

			// Set path for TEMPLATES also
			if(! defined('PATH_TEMPLATES')):
				define('PATH_TEMPLATES', $this->_dirNamespace . 'templates' . DIRECTORY_SEPARATOR);
			endif;
		endif;

		return (boolean)$this->_dirModule;
	}

	/**
	 * Set controller
	 *
	 * @param    string    $key
	 *
	 * @return bool
	 */
	protected function _setController(string $key) : bool {
		if($this->_checkName($key)):
			//
			$this->_nameController = str_replace(['-', ' '], '', mb_strtolower(trim($key)));

			// Let's define the filename for controller
			return $this->_setControllerFilename($this->_nameController);
		endif;

		return FALSE;
	}

	/**
	 * Set standard controller filename
	 *
	 * @param    string    $key
	 *
	 * @return  bool
	 */
	protected function _setControllerFilename(string $key) : bool {
		if($key && $this->_dirModule):
			// First move extra chars if any
			$this->_fileController = $this->_dirModule . $this->_formatName($key) . 'Controller.php';
		endif;

		// Return file status
		return (bool)$this->_fileController;
	}

	/**
	 * Format to regular filename
	 *
	 * @param    string    $unformatted
	 *
	 * @return  string
	 */
	protected function _formatName(string $unformatted) : string {
		if(is_string($unformatted) && strlen(trim($unformatted))):
			// Explode name to segments if there is a path separator
			if(is_array($segments = explode(PATH_SEPARATOR, trim($unformatted)))):
				// Lowercase ALL characters
				foreach($segments as $key => $val):
					$segments[$key] = ucwords(preg_replace('/[^a-z0-9 ]/', '', mb_strtolower($val)));
				endforeach;

				//
				return implode('_', $segments);
			endif;
		endif;

		return '';
	}

	/**
	 * @param    string    $key
	 *
	 * @return  bool
	 */
	protected function _setAction(string $key = NULL) : bool {
		if($this->_checkName($key)):
			$this->_nameAction = str_replace(' ', '', $this->_formatName($key) . 'Action');

			return TRUE;
		elseif($this->_nameAction === self::defaultAction):
			return TRUE;
		endif;

		return FALSE;
	}

	/**
	 * Rewrite the Route MAP to internal var so it can be fetched for controller/action
	 * This is useful is you want to compare REQUESTED route in URI vs real route inside application
	 *
	 * @param    array    $map
	 * @param    array    $vars
	 *
	 * @return  bool
	 */
	protected function _routeRewrite(array $map, array $vars = []) : bool {
		$this->_map =
			['query' => ['module' => $map['module'], 'controller' => $map['controller'], 'action' => $map['action'],],
				'route' => ['module' => $this->_nameModule, 'controller' => $this->_nameController,
					'action' => mb_strtolower(substr($this->_nameAction, 0,
					                                 strlen($this->_nameAction) - strlen('action'))),],
				'vars' => $vars];

		return TRUE;
	}

	/**
	 * @return void|Action
	 */
	public function getController() : Action {
		if(! empty($this->_dirModule) && ! empty($this->_fileController)):
			if(file_exists($file = $this->_dirModule . $this->_fileController)):
				// Load controller file
				require_once($file);

				// Define controller
				$controller = new $this->_instance();

				//
				if(is_object($controller)):
					return $controller;
				endif;
			endif;
		endif;
	}

	/**
	 * @param    object    $controller
	 *
	 * @throws Exception
	 */
	public function execute(object $controller) : void {
		// Execute dispatcher
		if(is_object($controller)):
			// Get Dispatcher
			if(is_object($dispatcher = $this->getDispatcher($controller))):
				// Dispatch the Application
				$dispatcher->dispatch();
			endif;
		else:
			$this->_setError('Controller does not exists');
		endif;
	}

	/**
	 * @param    object    $controller
	 *
	 * @return  Dispatcher
	 */
	#[Pure] public function getDispatcher(object $controller) : Dispatcher {
		return new Dispatcher($controller, $this->_nameAction);
	}

	/**
	 * Set controller instance name
	 *
	 * @param    string    $key
	 *
	 * @return  bool
	 */
	protected function _setControllerInstanceName(string $key = NULL) : bool {
		// If module name is default module name then cut it away
		if($this->_nameModule === self::defaultModule):
			$name = $this->_formatName($key) . 'Controller';
		else:
			// Else include module name
			$name = ucfirst($this->_nameModule) . '_' . $this->_formatName($key) . 'Controller';
		endif;

		// Instance name MUST include namespace also
		// Without namespace use '\' (because namespaces are used already)
		if($name):
			if($this->_nameNamespace !== self::defaultNamespace):
				$this->_instance = '\\' . $this->_nameNamespace . '\\' . $name;
			else:
				$this->_instance = '\\' . $name;
			endif;
		endif;

		// Return instance name status
		return (bool)$this->_instance;
	}
}
