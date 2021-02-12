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
namespace Tiat\Core\Filter\Validate;

/**
 * Class Between
 *
 * @package Tiat\Core\Filter\Validate
 */
class Between extends Base {
	
	public function __construct($options = []) {
		$this->_setOptions($options);
	}
	
	/**
	 * @param    null    $value
	 *
	 * @return bool
	 */
	public function isValid($value = NULL) : bool {
		if(! $this->_checkMin($value)):
			return FALSE;
		endif;
		
		if(! $this->_checkMax($value)):
			return FALSE;
		endif;
		
		return TRUE;
	}
	
	/**
	 * @param    int    $value
	 *
	 * @return bool
	 */
	private function _checkMin(int $value) : bool {
		if(isset($this->_options['min'])):
			if($value < (integer)$this->_options['min']):
				$this->_setError('Between check: value is too short (' . $value . ')');
				
				return FALSE;
			endif;
		endif;
		
		return TRUE;
	}
	
	/**
	 * @param    int    $value
	 *
	 * @return bool
	 */
	private function _checkMax(int $value) : bool {
		if(isset($this->_options['max'])):
			if($value > (integer)$this->_options['max']):
				$this->_setError('Between check: value is too long (' . $value . ')');
				
				return FALSE;
			endif;
		endif;
		
		return TRUE;
	}
}
