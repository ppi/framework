<?php

/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     Core
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Exception;

class Handler {
	
	/**
	 * The exception handlers 
	 * 
	 * @var array
	 */
	protected $_handlers = array();
	
	/**
	 * Handler statuses
	 * 
	 * @var array
	 */
	protected $_handlerStatus = array();
	
	/**
	 * PPI Exception handler
	 * The try/catch block will prevent a fatal error if an exception is thrown within the handler itself
	 * 
	 * @param object $e Exception object
	 */
	public function handle(\Exception $e) {
		
        $trace = $e->getTrace();
        
		try {
			// Execute each callback
			foreach($this->_handlers as $handler) {
				$this->_handlerStatus[] = array(
					'object'   => get_class($handler),
					'response' => $handler->handle($e)
				);
			}
            
            require(__DIR__ . '/templates/fatal.php');
            

		} catch(\Exception $e){
            require(__DIR__ . '/templates/fatal.php');
		}
		exit;
	}
	
	/**
	 * Add an Exception callback
	 * 
	 * @param \PPI\Exception\HandlerInterface 
	 */
	public function addHandler(\PPI\Exception\HandlerInterface $handler) {
		$this->_handlers[] = $handler;
	}
}
