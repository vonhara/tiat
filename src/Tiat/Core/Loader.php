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
use JetBrains\PhpStorm\Pure;
use Tiat\Core\Filter\Validate\File;

use function array_merge;
use function array_unique;
use function dirname;
use function explode;
use function in_array;
use function ini_get;
use function str_replace;
use function substr;

/**
 * Class Loader
 *
 * @package Tiat\Core
 */
class Loader {
	
	private $_loaded    = [];
	private $_extension = [];
	
	/**
	 * @param    null|string    $class
	 * @param    null|mixed     $dirs
	 *
	 * @return bool
	 * @throws Exception
	 */
	final public function loadClass(string $class = NULL, mixed $dirs = NULL) : bool {
		if($dirs === NULL):
			$dirs = PATH_BASE;
		endif;
		
		// Set filename without extension
		$file = str_replace('_', DIRECTORY_SEPARATOR, $class);
		
		// Replace the namespace '\' with directory separator
		if(str_contains($file, '\\')):
			$file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
		endif;
		
		// Set dirs to array (add NULL if namespace not defined)
		// Include the include_path to end of the array (if file not found then try include_path)
		if(dirname($file) === '.'):
			$dirs = $this->_setDir($dirs);
			$dirs = array_merge($dirs, $this->_mergeIncludePath());
		else:
			$dirs = $this->_setDir($dirs, dirname($file));
			$dirs = array_merge($dirs, $this->_mergeIncludePath($file));
		endif;
		
		// Remove duplicates
		$dirs = array_unique($dirs);
		
		// Check filename
		$filename = PATH_CORE . 'Core' . DIRECTORY_SEPARATOR . 'Filter' . DIRECTORY_SEPARATOR . 'Validate' .
		            DIRECTORY_SEPARATOR . 'File.php';
		
		if($this->_isLoaded('\Tiat\Core\Filter\Validate\File') || require_once $filename):
			//
			$this->_setLoaded('\Tiat\Core\Filter\Validate\File', $filename);
			
			if(File::checkFilename($filename)):
				return $this->loadFile(basename($file), $dirs, TRUE);
			endif;
		endif;
		
		return FALSE;
	}
	
	/**
	 * @param    mixed     $dirs
	 * @param    string    $dirname
	 *
	 * @return  array
	 */
	private function _setDir($dirs = NULL, string $dirname = NULL) : array {
		// Define dirs
		if($dirs === NULL):
			// Get dirs from include_path
			$path = ini_get('include_path');
			
			// Remove point (.) if exists (usually does in *nix enviroment)
			if(str_starts_with($path, ".:")):
				$path = substr($path, 2);
			elseif($path[0] === "."):
				$path = substr($path, 1);
			endif;
			
			// Explode path
			$dirs = explode(':', $path);
		else:
			if(! is_array($dirs) && is_string($dirs)):
				$dirs = [$dirs];
			endif;
		endif;
		// Include dirname to each value if exists
		if($dirname && is_array($dirs)):
			foreach($dirs as $key => $val):
				// Set directory
				if(substr($val, -1) !== DIRECTORY_SEPARATOR):
					$val .= DIRECTORY_SEPARATOR;
				endif;
				
				// Set new directory name
				$dirs[$key] = $val . $dirname;
			endforeach;
		endif;
		
		// Return dirs in array
		return $dirs;
	}
	
	/**
	 * @param    null|string|array    $file
	 *
	 * @return array|string[]
	 */
	private function _mergeIncludePath(string|array $file = NULL) : array|string {
		if(isset($file[0])):
			// Remove the LAST SECTION from FILE
			// The last section is used to point to FILENAME with extension
			// Explode the name
			$exploded = explode(DIRECTORY_SEPARATOR, $file);
			
			// Remove last section
			if(is_array($exploded) && count($exploded) > 1):
				array_pop($exploded);
			endif;
			
			$file = implode(DIRECTORY_SEPARATOR, $exploded);
		endif;
		if(! empty($include = $this->_setDir())):
			foreach($include as $key => &$val):
				if($val[strlen($val) - 1] !== DIRECTORY_SEPARATOR):
					$val .= DIRECTORY_SEPARATOR;
				endif;
				
				$val .= $file;
			endforeach;
			
			return $include;
		endif;
		
		return [];
	}
	
	/**
	 * Test if class is already loaded
	 *
	 * @param    null|string    $class
	 * @param    null|string    $filename
	 *
	 * @return bool
	 */
	private function _isLoaded(string $class = NULL, string $filename = NULL) : bool {
		if(is_string($class) && ! empty($class)):
			return isset($this->_loaded[$this->_checkLoader($class)]);
		elseif(is_string($filename) && ! empty($filename)):
			return in_array($filename, $this->_loaded, TRUE);
		endif;
		
		return FALSE;
	}
	
	/**
	 * @param    null|string    $name
	 *
	 * @return string
	 */
	#[Pure] private function _checkLoader(string $name = NULL) : string {
		if(is_string($name) && strlen($name)):
			if($name[0] === '\\'):
				return substr($name, 1);
			endif;
		endif;
		
		return $name;
	}
	
	/**
	 * Try prevent double loading with this function with minimal cost
	 *
	 * @param    null|string    $class
	 * @param    null|string    $filename
	 */
	private function _setLoaded(string $class = NULL, string $filename = NULL) : void {
		// Autoload will push class name without first '\' char
		if(is_string($class) && ! empty($class)):
			$class                 = $this->_checkLoader($class);
			$this->_loaded[$class] = $filename;
		endif;
		
		return;
	}
	
	/**
	 * @param    string    $file
	 * @param    array     $dirs
	 * @param    bool      $once
	 *
	 * @return  bool
	 */
	final public function loadFile(string $file, array $dirs = [], bool $once = TRUE) : bool {
		// If file is already loaded then return status
		if(is_array($dirs) && count($dirs)):
			foreach($dirs as $val):
				if(substr($val, -1) !== DIRECTORY_SEPARATOR):
					$val .= DIRECTORY_SEPARATOR;
				endif;
				
				// Connect dir + filename
				$filename = $val . $file;
				
				// Try all file extensions
				foreach($this->_getFileExtension() as $extension):
					$loadfile = $filename . '.' . $extension;
					if(file_exists($loadfile)):
						if(! $this->_isLoaded(NULL, $loadfile)):
							if(! $once):
								require $loadfile;
							else:
								require_once $loadfile;
							endif;
						endif;
						
						// Return true
						return TRUE;
					endif;
				endforeach;
			endforeach;
		endif;
		
		return FALSE;
	}
	
	/**
	 * Get available file extension(s)
	 *
	 * @return array
	 */
	private function _getFileExtension() : array {
		if(count($this->_extension) < 1):
			$this->setFileExtension();
		endif;
		
		return $this->_extension;
	}
	
	/**
	 * Set file extension(s) for Autoload
	 * Notice! Keep this list as short as possible
	 *
	 * @param    null|array    $list
	 */
	final public function setFileExtension(array $list = NULL) : void {
		if(empty($list)):
			$this->_extension = ['php'];
		else:
			$this->_extension = $list;
		endif;
		
		return;
	}
}
