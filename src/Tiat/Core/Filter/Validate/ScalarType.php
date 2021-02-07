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

//
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

/**
 * Class ScalarType
 *
 * @package Tiat\Core\Filter\Validate
 */
class ScalarType extends Base {

	/**
	 * ScalarType constructor.
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
		if(! $this->_isString($value)):
			return FALSE;
		endif;

		if(! $this->_isInt($value)):
			return FALSE;
		endif;

		if(! $this->_isFloat($value)):
			return FALSE;
		endif;

		if(! $this->_isBool($value)):
			return FALSE;
		endif;

		return TRUE;
	}

	/**
	 * @param    string    $value
	 *
	 * @return bool
	 */
	private function _isString(string $value) : bool {
		if(isset($this->_options) && $this->_options === 'string'):
			if(! is_string($value)):
				$this->_setError('Value is not string');

				return FALSE;
			endif;
		endif;

		return TRUE;
	}

	/**
	 * @param    int    $value
	 *
	 * @return  bool
	 */
	private function _isInt($value) : bool {
		if(isset($this->_options) && $this->_options === 'int'):
			if(! is_int($value)):
				$this->_setError('Value is not int');

				return FALSE;
			endif;
		endif;

		return TRUE;
	}

	/**
	 * @param    float    $value
	 *
	 * @return bool
	 */
	private function _isFloat(float $value) : bool {
		if(isset($this->_options) && $this->_options === 'float'):
			if(! is_float($value)):
				$this->_setError('Value is not float');

				return FALSE;
			endif;
		endif;

		return TRUE;
	}

	/**
	 * @param    bool    $value
	 *
	 * @return bool
	 */
	private function _isBool(bool $value) : bool {
		if(isset($this->_options) && $this->_options === 'bool'):
			if(! is_bool($value)):
				$this->_setError('Value is not boolean');

				return FALSE;
			endif;
		endif;

		return TRUE;
	}
}
