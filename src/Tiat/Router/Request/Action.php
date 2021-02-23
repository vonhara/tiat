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
use function method_exists;

/**
 * Class Action
 *
 * @package Tiat\Router\Request
 */
class Action extends Base {
	
	/**
	 * @param    null|string    $action
	 *
	 * @return  self
	 */
	public function dispatch(string $action = NULL) : self {
		// Call controller preDispatch
		if(method_exists($this, 'preDispatch')):
			$this->preDispatch();
		endif;
		
		// Call controller action
		$this->_dispatchAction($action);
		
		// Call controller postDispatch
		if(method_exists($this, 'postDispatch')):
			$this->postDispatch();
		endif;
		
		return $this;
	}
	
	/**
	 * This method is executed before the action
	 * Notice! This method can be overridden by Controller
	 *
	 * @return  void
	 */
	public function preDispatch() : void {
	}
	
	/**
	 * @param    string    $action
	 *
	 * @return  self
	 */
	private function _dispatchAction(string $action = NULL) : self {
		// Check that action method exists in controller
		if(! empty($action) && method_exists($this, $action)):
			$this->$action();
		endif;
		
		return $this;
	}
	
	/**
	 * This method is executed after the action
	 * Notice! This method can be overridden by Controller
	 *
	 * @return  void
	 */
	public function postDispatch() : void {
	}
	
	/**
	 * Prevent cloning
	 */
	final protected function __clone() : void {
		return;
	}
}
