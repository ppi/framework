<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Helper
 * @link      www.ppiframework.com
 */
namespace PPI;
class Helper {

	/**
	 * Function to recursively trim strings
	 *
	 * @param mixed $input The input to be trimmed
	 * @return mixed
	 */
	function arrayTrim($input){
		if (!is_array($input)) {
			return trim($input);
		}
		return array_map(array($this, 'arrayTrim'), $input);
	}
	
	/**
	 * Get the router object
	 *
	 * @return object
	 */
	static function getRouter() {
		return self::getObjectFromRegistry('PPI_Router');
	}

	/**
	 * Get the router object
	 *
	 * @return object
	 */
	static function getSecurity() {
		return self::getObjectFromRegistry('PPI_Security');
	}

	static function getObjectFromRegistry($class) {
		if(!PPI_Registry::exists($class)) {
			$oClass = new $class();
			PPI_Registry::set($class, $oClass);
			return $oClass;
		}
		return PPI_Registry::get($class);
	}

	/**
	 * Get the PPI_Request object cached from the registry
	 *
	 * @static
	 * @return mixed
	 */
	static function getRequest() {
		return self::getObjectFromRegistry('PPI_Request');

	}

	/**
	 * Get the PPI_Response object cached from the registry
	 *
	 * @static
	 * @return mixed
	 */
	static function getResponse() {
		return self::getObjectFromRegistry('PPI_Response');

	}

}
