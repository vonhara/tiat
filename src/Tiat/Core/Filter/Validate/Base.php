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
 * Class Base
 *
 * @package Tiat\Core\Filter\Validate
 */
abstract class Base implements Adapter {

	//
	protected $_value    = NULL;
	protected $_errors   = [];
	protected $_messages = [];
	protected $_options  = NULL;

	/**
	 * @param    null    $value
	 *
	 * @return bool
	 */
	public function isValid($value = NULL) : bool {
		return TRUE;
	}

	/**
	 * @return mixed
	 */
	public function getMessages() : mixed {
		return $this->_messages;
	}

	/**
	 * @return mixed
	 */
	public function getErrors() : mixed {
		return $this->_errors;
	}

	/**
	 * Set options for the validator
	 *
	 * @param    string    $options
	 *
	 * @return  void
	 */
	protected function _setOptions($options = NULL) : void {
		if($options !== NULL):
			$this->_options = $options;
		endif;

		return;
	}

	/**
	 * Sets the value to be validated and clears the messages and errors arrays
	 *
	 * @param    mixed    $value
	 *
	 * @return void
	 */
	protected function _setValue($value) {
		$this->_value    = $value;
		$this->_errors   = [];
		$this->_messages = [];
	}

	/**
	 * Set error
	 *
	 * @param    string    $error
	 *
	 * @return  self
	 */
	protected function _setError($error = NULL) : self {
		$this->_errors[]   = $error;
		$this->_messages[] = $error;

		return $this;
	}

	/**
	 * Set error message
	 *
	 * @param    string    $msg
	 *
	 * @return  self
	 */
	protected function _setMessage($msg = NULL) : self {
		$this->_messages[] = $msg;

		return $this;
	}
}
