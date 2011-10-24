<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Core
 * @link      www.ppiframework.com
 */
namespace PPI;
class Registry {

	/**
	 * Registry object provides storage for shared objects.
	 * @var object $_instance
	 */
	private static $_instance = null;

	/**
	 * The registrys internal data
	 *
	 * @var array
	 */
	protected static $_vars = array();

	/**
	 * @param array $array Initial data for the registry
	 */
	public function __construct() {}

	/**
	 * Retrieves the default instance of the registry, if it doesn't exist then we create it.
	 *
	 * @return PPI_Registry
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			self::legacyInit();
		}
		return self::$_instance;
	}

	/**
	 * Set the default registry instance to a specified instance.
	 *
	 * @param object $registry An object instance of type PPI_Registry
	 * @return void
	 * @throws PPI_Exception if registry is already initialized.
	 */
	public static function setInstance($registry) {
		if (self::$_instance !== null) {
			throw new PPI_Exception('Registry is already initialized');
		}
		self::$_instance = $registry;
	}

	/**
	 * Initialize the default registry instance.
	 *
	 * @return void
	 */
	protected static function legacyInit() {
		self::setInstance(new PPI\Registry\Legacy());
	}

	/**
	 * Initialisation function, currently used to set mock data
	 *
	 * @static
	 * @param array $options
	 * @return void
	 */
	public static function init(array $options = array()) {

		if(isset($options['data']) && !empty($options['data'])) {
			self::$_vars = $options['data'];
		}
	}

	/**
	 * Get a value from the registey
	 *
	 * @param string $index
	 * @return mixed
	 * @throws PPI_Exception if no entry is registerd for $index.
	 */
	public static function get($key, $default = null) {
		return isset(self::$_vars[$key]) ? self::$_vars[$key] : $default; 
	}

	/**
	 * Set a value in the registry
	 *
	 * @param string $index
	 * @param mixed $value The object to store in the ArrayObject.
	 * @return void
	 */
	public static function set($index, $value) {
		self::$_vars[$index] = $value;
	}

	/**
	 * Removes an offset from the registry
	 *
	 * @param string $index
	 * @return void
	 */
	public static function remove($index) {
		unset(self::$_vars[$index]);
	}

	/**
	 * Checks if an offset exists in the registry
	 *
	 * @param string $index
	 * @return mixed
	 */
	public static function exists($index) {
		return array_key_exists($index, self::$_vars);
	}
}
