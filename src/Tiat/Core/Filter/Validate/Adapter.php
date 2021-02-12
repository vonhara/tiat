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
 * Interface Adapter
 *
 * @package Tiat\Core\Filter\Validate
 */
interface Adapter {
	
	/**
	 * Validate
	 *
	 * @return bool
	 */
	public function isValid() : bool;
	
	/**
	 * Get messages from Validator
	 *
	 * @return mixed
	 */
	public function getMessages() : mixed;
	
	/**
	 * Get errors
	 *
	 * @return mixed
	 */
	public function getErrors() : mixed;
}
