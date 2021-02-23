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

/**
 * Class Request
 *
 * @package Tiat\Router\Request
 */
class Request {
	
	//
	private array $_privateParams;
	
	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	final public function __get($name) : mixed {
		return $this->_privateParams[$name] ?? NULL;
	}
	
	/**
	 * @param $key
	 * @param $value
	 *
	 * @throws Exception
	 */
	final public function __set($key, $value) : void {
		$this->_privateParams[$key] = $value;
		
		return;
	}
	
	/**
	 * @param $name
	 *
	 * @return bool
	 */
	final public function __isset($name) : bool {
		return isset($name);
	}
}
