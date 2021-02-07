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
namespace Tiat\Core\Filter;

//
use Tiat\Core\Filter\Validate\Adapter;
use Tiat\Core\Filter\Validate\Base;

/**
 * Class Validate
 *
 * @package Tiat\Core\Filter
 */
class Validate extends Base {

	//
	private $_validators = NULL;        // Validators to be used

	/**
	 * Add new validator
	 *
	 * @param    Adapter    $instance
	 * @param    false      $breakOnFailure
	 *
	 * @return $this
	 */
	public function addValidator(Adapter $instance, $breakOnFailure = FALSE) : self {
		$this->_validators[] = ['instance' => $instance, 'breakOnFailure' => (boolean)$breakOnFailure,];

		return $this;
	}

	/**
	 * @param    mixed    $value
	 *
	 * @return  boolean
	 */
	public function isValid(mixed $value = NULL) : bool {
		// Clear all messages & set default result value
		$this->_messages = NULL;
		$this->_errors   = NULL;
		$result          = TRUE;

		foreach($this->_validators as $validator):
			if($validator['instance']->isValid($value)):
				continue;
			else:
				$this->_errors   = $validator['instance']->getErrors();
				$this->_messages = $validator['instance']->getMessages();
			endif;

			// Validator(s) not passed
			// Set return value to false
			$result = FALSE;

			// Get messages & break (if "Break-On-Failure" is true)
			if($validator['breakOnFailure']):
				break;
			endif;
		endforeach;

		return (bool)$result;
	}
}
