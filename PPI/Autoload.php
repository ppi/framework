<?php
/**
 * The PPI Autoloader
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppiframework.com
 */
namespace PPI;
class Autoload {

	/**
	 * The cache list of classes
	 * 
	 * @var array
	 */
	protected static $_classes = array();
	
	/**
	 * The separator characters for namespacing
	 * 
	 * @var string
	 */
	protected static $_namespaceSeparator = '\\';

	/**
	 * The base list of libraries to check in the autoloader, these are the base two ones required
	 * for the framework and the skeleton app classes to be autoloaded
	 *
	 * @var array
	 */
	static protected $_libraries = array();


	function __construct() {}

	/**
	 * Register The PPI Autoload Function
	 *
	 * @return void
	 */
	static function register() {
		spl_autoload_register(array('self', 'loadClass'));
	}
	
	/**
	 * The actual autoloading function
	 *
	 * @param string $className The Class Name To Be Autoloaded
	 * @return boolean
	 */
	static function loadClass($className) {
		
		foreach(self::$_libraries as $options) {
			if ($options['namespace'] === null || 
				$options['namespace'] . self::$_namespaceSeparator === substr($className, 0, strlen($options['namespace'] . self::$_namespaceSeparator))) {

				$fileName = $namespace = '';
				if (false !== ($lastNsPos = strripos($className, self::$_namespaceSeparator))) {
					$namespace = substr($className, 0, $lastNsPos);
					$className = substr($className, $lastNsPos + 1);
					$fileName  = str_replace(self::$_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
					
				}
				$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
				$path = (isset($options['path']) ? $options['path'] . DIRECTORY_SEPARATOR : '') . $fileName;
				if(file_exists($path)) {
					require $path;
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Unregister The PPI Autoload Function
	 *
	 * @return void
	 */
	static function unregister() {
		spl_autoload_unregister(array('self', 'autoload'));
	}

	/**
	 * Add a library to the autoloader
	 *
	 * @example
	 * PPI_Autoload::add('Zend', array(
	 *     'path' => SYSTEMPATH . 'Vendor/',
	 * ));
	 *
	 * @param string $key The Key, This is used for exists() and remove()
	 * @param array $options
	 */
	static function add($key, array $options) {
		
		$options['namespace'] = $key;
		$options['path'] = isset($options['path']) ? $options['path'] : null;
		self::$_libraries[$key] = $options;
	}

	/**
	 * Remove a library from the autoloader
	 *
	 * @param string $p_sKey The key
	 * @return void
	 */
	static function remove($p_sKey) {
		unset(self::$_libraries[$p_sKey]);
	}

	/**
	 * Checks if a library has been added
	 *
	 * @param string $p_sKey The key
	 * @return boolean
	 */
	static function exists($p_sKey) {
		return isset(self::$_libraries[$p_sKey]);
	}
}
