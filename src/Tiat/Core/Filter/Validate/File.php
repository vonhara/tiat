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

use Exception;

/**
 * Class File
 *
 * @package Tiat\Core\Filter\Validate
 */
class File {

	/**
	 * @param    null    $value
	 *
	 * @return bool
	 */
	public function isValid($value = NULL) : bool {
		try {
			return self::checkFilename($value);
		} catch(Exception $e) {
			throw new Exception($e);
		}
	}

	/**
	 * @param    string    $filename
	 *
	 * @return bool
	 * @throws Exception
	 */
	final static public function checkFilename(string $filename) : bool {
		try {
			if(! preg_match('/[^a-z0-9\\/\\\\_.-]/i', $filename)):
				return TRUE;
			endif;
		} catch(Exception $e) {
			throw new Exception('Filename check: illegal charecter in filename (' . $filename . ') [' . $e . ']');
		}

		return FALSE;
	}
}
