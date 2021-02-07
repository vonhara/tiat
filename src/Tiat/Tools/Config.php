<?php
/**
 * Tiat Platform
 * LICENSE
 * This framework is using MIT licence
 *
 * @category     Platform
 * @package      Tools
 * @author       Jan Theon <jan@jantheon.com>
 * @copyright    Copyright (c) 2005-2021 Jan Theon (All rights reserved)
 * @license      MIT. See also the license.txt
 */

/**
 * Bootstrap package for Tiat Platform
 *
 * @category     Platform
 * @package      Tools
 * @copyright    Copyright (c) 2005-2021 Jan Theon
 * @license      MIT. See also the license.txt
 */
namespace Tiat\Tools;

//
use Tiat\Uri\Base;

use function count;
use function define;
use function defined;
use function file_exists;
use function ini_get;
use function ini_set;
use function is_scalar;
use function parse_ini_file;
use function strlen;
use function substr;

/**
 * Class Config
 *
 * @package Tiat\Tools
 */
class Config {

	//
	protected array $_config;    // Config values if already read at least once

	/**
	 * Config constructor.
	 *
	 * @param    null|string    $key
	 * @param    null|string    $server
	 * @param    null|string    $module
	 */
	public function __construct(string $key = NULL, string $server = NULL, string $module = NULL) {
		if(! empty($key)):
			//
			$this->readConfig($key, $server, $module);
		endif;

		return;
	}

	/**
	 * @param    null|string    $key
	 * @param    null|string    $server
	 * @param    null|string    $module
	 *
	 * @return array
	 */
	public function readConfig(string $key = NULL, string $server = NULL, string $module = NULL) : array {
		if(empty($this->_config)):
			// Check server var
			$server = ($server ?? $_SERVER['SERVER_NAME']);

			// Resolve config path from server name
			if(! empty($path = $this->_resolveConfigPath($server))):
				// Config file is in 'config' dir so add it to PATH
				$config = $path . 'config' . DIRECTORY_SEPARATOR;

				// Read config file & detect re-route if exists before anything else
				$conf = $this->_getIni($key, $config, TRUE, $module);

				// Re-route application
				if(isset($conf['router']['reroute']) && strlen($conf['router']['reroute']) > 1):
					//
					[$t1, $t2] = $this->_rerouteApplication($conf['router']['reroute']);
					// Check that new route is not root directory & if exists
					if(! empty($t1) && strlen($t1) > 1 && file_exists($t1)):
						$path   = $t1;
						$config = $t2;
					endif;
				endif;

				// Define application PATH & add it to include path
				if(! defined('PATH_APPS')):
					define('PATH_APPS', $path);
					// Add path apps to include path
					ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . PATH_APPS);
				endif;

				// If not defined config path then do it
				if(! defined('PATH_CONFIG')):
					define('PATH_CONFIG', $config);
				endif;

				// Read local config file
				$this->_config = $this->_getIni($key, $config, TRUE, $module);

				// Validate boolean values to boolean (not as string)
				$this->_config = $this->_checkConfig($this->_config);
			endif;
		endif;

		return $this->_config;
	}

	/**
	 * @param    string    $server
	 *
	 * @return string
	 */
	private function _resolveConfigPath(string $server) : string {
		if(! defined('PATH_APPS') && $server):
			//
			$tld = $this->_getDomainTld($server);

			// Resolve path with domain name without TLD
			$server = substr($server, 0, strlen($server) - strlen($tld));
			$params = array_reverse(explode('.', strtolower($server)));

			// Modify TLD (remove points)
			$tld = str_replace('.', '', $tld);

			// If there is only domain name (no server name like 'www') then use TLD value without points
			// domain.com = /path/to/your/apps/domain/com/
			if(count($params) === 1):
				$path = PATH_BASE . 'apps' . DIRECTORY_SEPARATOR . $params[0] . DIRECTORY_SEPARATOR . $tld .
				        DIRECTORY_SEPARATOR;
			elseif(count($params) > 1):
				// Support for multiple domain settings
				$counter = count($params);
				do {
					$path = PATH_BASE . 'apps' . DIRECTORY_SEPARATOR . $this->_resolvePath($params, $counter);

					// Add TLD to $path when counter is 1 (only the domain name exists)
					if($counter <= 1):
						$path .= ($tld ?? '') . DIRECTORY_SEPARATOR;
					endif;

					// 'config' dir must exists for application
					// Otherwise with multiple application subdirectory will always reroute to "no application"
					if(file_exists($path . 'config' . DIRECTORY_SEPARATOR)):
						break;
					endif;

					$counter--;
				} while($counter >= 1);
			endif;
			// Test that path exists OR use default APPS
			if(! file_exists($path)):
				$path = PATH_BASE . 'apps' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR;
			endif;

			//
			return $path;
		endif;

		return PATH_APPS ?? '';
	}

	/**
	 * @param    string    $server
	 *
	 * @return string
	 */
	protected function _getDomainTld(string $server) : string {
		// Get URI & resolve TLD
		return (string)(new Base())->getDomainTld($server);
	}

	/**
	 * Sub-function for _resolveConfigPath()
	 *
	 * @param    array      $array
	 * @param    integer    $loops
	 *
	 * @return  string
	 */
	private function _resolvePath(array $array, int $loops = 0) {
		if(! empty($array) && $loops):
			$counter = 0;
			$path    = '';

			foreach($array as $val):
				$path .= $val . DIRECTORY_SEPARATOR;
				$counter++;

				if($counter >= $loops):
					break;
				endif;
			endforeach;

			return $path;
		endif;

		return '';
	}

	/**
	 * @param    string    $filename
	 * @param    string    $path
	 * @param    bool      $sections
	 * @param    string    $module
	 *
	 * @return  array
	 */
	private function _getIni(string $filename = NULL, string $path = NULL, bool $sections = TRUE, string $module = NULL) : array {
		if($filename && $path):
			// If module is defined then use it
			if(is_string($module) && ! empty($module)):
				$file = $path . $module . DIRECTORY_SEPARATOR . $filename . '.ini';
			else:
				$file = $path . $filename . '.ini';
			endif;

			if(file_exists($file)):
				return parse_ini_file($file, $sections, INI_SCANNER_TYPED);
			endif;
		endif;

		return [];
	}

	/**
	 * Re-route application to other application source
	 *
	 * @param    string    $name
	 *
	 * @return  array
	 */
	private function _rerouteApplication(string $name = NULL) : array {
		if(! empty($name)):
			// Trim directory separator from both sides of string
			if($name[0] === DIRECTORY_SEPARATOR):
				$name = substr($name, 1);
			endif;

			if(substr($name, -1) === DIRECTORY_SEPARATOR):
				$name = substr($name, 0, (strlen($name) - 1));
			endif;

			// Set path
			$path = PATH_BASE . 'apps' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;

			// Return both PATH_APPS & PATH_CONFIG variables
			if(file_exists($path)):
				return [$path, $path . 'config' . DIRECTORY_SEPARATOR];
			endif;
		endif;

		//
		return ['', ''];
	}

	/**
	 * Check config boolean values (true/false strings)
	 *
	 * @param    array    $params
	 *
	 * @return  array
	 */
	private function _checkConfig(array $params = []) : array {
		if(count($params)):
			foreach($params as $key => $val):
				if(is_scalar($val)):
					if(strtolower($val) === 'true'):
						$params[$key] = TRUE;
					elseif(strtolower($val) === 'false'):
						$params[$key] = FALSE;
					endif;
				elseif(is_array($val)):
					$params[$key] = $this->_checkConfig($val);
				endif;
			endforeach;
		endif;

		return $params;
	}

	/**
	 * Get param(s) from config
	 *
	 * @param    null|string    $key
	 * @param    null|string    $value
	 */
	final public function getConfig(string $key = NULL, string $value = NULL) {
		// Return specified key from array with value (OR whole config array IF the $key is null)
		if($this->readConfig()):
			if($key !== NULL && isset($this->_config[$key])):
				if(is_scalar($value) && isset($this->_config[$key][$value])):
					return $this->_config[$key][$value];
				else:
					return $this->_config[$key];
				endif;
			elseif($key === NULL && $this->_config !== NULL):
				return $this->_config;
			endif;
		endif;
	}
}
