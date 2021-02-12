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
use JetBrains\PhpStorm\Pure;

use function ctype_alnum;

/**
 * Class Alnum
 *
 * @package Tiat\Core\Filter\Validate
 */
class Alnum extends Base {
	
	public function __construct($options = []) {
		$this->_setOptions($options);
	}
	
	/**
	 * @param    null    $value
	 *
	 * @return bool
	 */
	#[Pure] public function isValid($value = NULL) : bool {
		return ctype_alnum($value);
	}
}
