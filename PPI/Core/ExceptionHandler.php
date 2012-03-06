<?php
/**
 * @author    Paul Dragoonis (dragoonis@php.net)
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Core
 * @link      wwww.ppi.io
 */
namespace PPI\Core;
class ExceptionHandler {
	
	/**
	 * PPI Exception handler
	 * The try/catch block will prevent a fatal error if an exception is thrown within the handler itself
	 * 
	 * @param object $e Exception object
	 */
	public static function handle($e) {
		
		try {
			// We can log exceptions, email admins etc as required here
			$trace = $e->getTrace();
			require(SYSTEMPATH  . 'View' . DS . 'Exception.php');

		} catch(Exception $e){
			require(SYSTEMPATH  . 'View' . DS . 'Exception.php');
		}
		exit;
	}
}
