<?php
/**
 * Tiat Platform
 * LICENSE
 * This framework is using MIT licence
 *
 * @category     Platform
 * @package      Router
 * @author       Jan Theon <jan@jantheon.com>
 * @copyright    Copyright (c) 2005-2021 Jan Theon (All rights reserved)
 * @license      MIT. See also the license.txt
 */

/**
 * Bootstrap package for Tiat Platform
 *
 * @category     Platform
 * @package      Router
 * @copyright    Copyright (c) 2005-2021 Jan Theon
 * @license      MIT. See also the license.txt
 */
namespace Tiat\Router\Request;

//
use Exception;

/**
 * Class Dispatcher
 *
 * @package Tiat\Router\Request
 */
class Dispatcher extends Base {

	/**
	 * Dispatcher constructor.
	 *
	 * @param    object    $_controller
	 * @param    string    $_action
	 */
	public function __construct(protected object $_controller, protected string $_action = '') {
	}

	/**
	 * @throws Exception
	 */
	public function dispatch() : void {
		try {
			// Before the application
			$this->dispatchStart();
			$this->preDispatch();

			// Call application
			$this->_controller->dispatch($this->_action);

			// After the application
			$this->postDispatch();
			$this->dispatchEnd();
		} catch(Exception $e) {
			throw new Exception($e);
		}
	}

	/**
	 * Dispatch start
	 */
	public function dispatchStart() : void {
	}

	/**
	 * Pre-dispatch before action
	 */
	public function preDispatch() : void {
	}

	/**
	 * Post dispatch after action
	 */
	public function postDispatch() : void {
	}

	/**
	 * End dispatching
	 */
	public function dispatchEnd() : void {
	}

	/**
	 * Prevent cloning
	 */
	final protected function __clone() : void {
		return;
	}
}
