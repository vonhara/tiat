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
namespace Tiat\Error;

/**
 * Trait Client
 *
 * @package Tiat\Error
 */
trait Client {
	
	//
	protected array $_errorMessages;
	
	/**
	 * @return array
	 */
	public function getErrors() : array {
		if(! empty($this->_errorMessages)):
			return $this->_errorMessages;
		endif;
		
		return [];
	}
	
	/**
	 * Set error message
	 *
	 * @param    array|string    $msg
	 * @param    int             $code
	 */
	protected function _setError(array|string $msg, int $code = 0) : void {
		if(! empty($msg)):
			$this->_errorMessages[] = ['msg' => $msg, 'code' => $code];
		endif;
		
		return;
	}
}
