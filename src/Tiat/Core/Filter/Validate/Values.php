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
use function in_array;

/**
 * Class Values
 *
 * @package Tiat\Core\Filter\Validate
 */
class Values extends Base {

	/**
	 * Values constructor.
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
		return $this->_checkValues($value);
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function _checkValues($value) : bool {
		if(isset($this->_options['accept']) && count($this->_options['accept'])):
			if(! in_array($value, $this->_options['accept'], TRUE)):
				$this->_setError('Value not accepted (' . $value . ')');

				return FALSE;
			endif;
		endif;

		return TRUE;
	}
}
