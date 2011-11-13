<?php
/**
 * @author    Paul Dragoonis (dragoonis@php.net)
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Core
 * @link      wwww.ppi.io
 */
namespace PPI\Exception;
class Handler implements ExceptionInterface {
	
	/**
	 * The event listeners
	 * 
	 * @var array
	 */
	protected $_listeners = array();
	
	/**
	 * PPI Exception handler
	 * The try/catch block will prevent a fatal error if an exception is thrown within the handler itself
	 * 
	 * @param object $e Exception object
	 */
	public function handle(\Exception $e) {
		
		try {			
			$trace = $e->getTrace();
			
			// Execute each callback
			foreach($this->_listeners as $listener){
				$listener->handle($e);
			}
			
			require(SYSTEMPATH  . 'View' . DS . 'Exception.php');

		} catch(\Exception $e){
			require(SYSTEMPATH  . 'View' . DS . 'Exception.php');
		}
		exit;
	}
	
	/**
	 * Add an Exception callback
	 * 
	 * @param \PPI\Exception\Interface 
	 */
	public function addListener(\PPI\Exception\ExceptionInterface $listener) {
		
		$this->_listeners[] = $listener;
	}
}
