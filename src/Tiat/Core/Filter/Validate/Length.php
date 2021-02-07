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
 * Class Length
 *
 * @package Tiat\Core\Filter\Validate
 */
class Length extends Base {

	/**
	 * Length constructor.
	 *
	 * @param    array    $options
	 */
	public function __construct($options = []) {
		$this->_setOptions($options);
	}

	/**
	 * @param    null    $value
	 *
	 * @return bool
	 */
	public function isValid($value = NULL) : bool {
		if($this->_checkMin($value)):
			return $this->_checkMax($value);
		endif;

		return FALSE;
	}

	/**
	 * @param    string    $value
	 *
	 * @return bool
	 */
	private function _checkMin(string $value) : bool {
		if(isset($this->_options['min']) && strlen($value) < (integer)$this->_options['min']):
			$this->_setError('Length check: value is too short (' . $value . ')');

			return FALSE;
		endif;

		return TRUE;
	}

	/**
	 * @param    string    $value
	 *
	 * @return bool
	 */
	private function _checkMax(string $value) : bool {
		if(isset($this->_options['max']) && strlen($value) > (integer)$this->_options['max']):
			$this->_setError('Length check: value is too long (' . $value . ')');

			return FALSE;
		endif;

		return TRUE;
	}
}
