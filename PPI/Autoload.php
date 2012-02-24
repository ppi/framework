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

/**
 * 
 * It is able to load classes that use either:
 *
 *  * The technical interoperability standards for PHP 5.3 namespaces and
 *    class names (https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md);
 *
 *  * The PEAR naming convention for classes (http://pear.php.net/).
 * 
 *  Example usage:
 * 
 *  PPI\Autoload::add('Symfony', PPI_VENDOR_PATH . 'Symfony')
 *  PPI\Autoload::register();
 * 
 */

class Autoload {

	/**
	 * The ClassLoader object
	 * 
	 * @var null|object
	 */
	static protected $_loader = null;

	/**
	 * Add a namespace to the autoloader path
	 * 
	 * @static
	 * @param string $key
	 * @param string $path
	 */
	static function add($key, $path) {
		self::$_loader === null && self::initLoader();
		self::$_loader->registerNamespace($key, $path);
	}
	
	/**
	 * Register the autoloader namespaces or prefixes thus far.
	 * 
	 * @static
	 * 
	 */
	public static function register() {
		self::$_loader === null && self::initLoader();
		self::$_loader->register();
	}
	
	/**
	 * Initialize the ClassLoader class responsible for doing the actual autoloading
	 * 
	 * @static
	 * 
	 */
	public static function initLoader() {
		$path = PPI_VENDOR_PATH . 'Symfony' . DS . 'Component' . DS . 'ClassLoader' . DS . 'UniversalClassLoader.php';
		require_once($path);
		self::$_loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
	}

}
