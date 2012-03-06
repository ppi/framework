<?php
/**
* PPI Cache handler
*
* @package   Cache
* @author    Paul Dragoonis <dragoonis@php.net>
* @license   http://opensource.org/licenses/mit-license.php MIT
* @link      http://www.ppi.io
*/
namespace PPI;
class Cache {

	/**
	 * Defaults for the handler
	 *
	 * @var array
	 */
	protected $_defaults = array(
		'handler'  => 'disk'
	);

	/**
	 * The handler in use
	 *
	 * @var null|PPI\Cache\CacheInterface
	 */
	protected $_handler = null;

	/**
	 * The options to the cache layer. This can be an array of options
	 * or a string of the driver name eg: new PPI_Cache('apc');
	 *
	 * @param array|string $options
	 */
	function __construct(array $options = array()) {

		// We now let you specify the handler as a string for quickness.
		if(is_string($options)) {
			$options = array('handler' => $options);
		}

		if(isset($options['handler'])) {
			// If it's a pre instantiated cache handler then use that
			if(!is_string($options['handler']) && $options['handler'] instanceof \PPI\Cache\CacheInterface) {
				$this->_handler = $options['handler'];
				unset($options['handler']);
			}
		}

		$this->_defaults = ($options + $this->_defaults);

		// If no handler was passed in, then we setup that handler now by the string name: i.e: 'disk'
		if($this->_handler === null) {
			$this->setupHandler($this->_defaults['handler']);
		}
	}

	/**
	 * Initialise the cache handler
	 *
	 * @param string $handler The handler name
	 * @return void
	 * @throws \PPI\Core\CoreException
	 */
	function setupHandler($handler) {
		$handler = strtolower($handler);
		$handler = 'PPI\\Cache\\' . ucfirst($handler);
		$this->_handler = new $handler($this->_defaults);
		if($this->_handler->enabled() === false) {
			throw new \PPI\Core\CoreException('The cache driver ' . $handler . ' is currently disabled.');
		}
		$this->_handler->init();
	}

	/**
	 * Get a key value from the cache
	 *
	 * @param string $key The Key
	 * @return mixed
	 */
	function get($key) {
		return $this->_handler->get($key);
	}

	/**
	 * Set a value in the cache
	 *
	 * @param string $key The Key
	 * @param mixed $value The Value
	 * @param integer $ttl The TTL
	 * @return boolean
	 */
	function set($key, $value, $ttl = 0) {
		return $this->_handler->set($key, $value, $ttl);
	}

	/**
	 * Check if a key exists in the cache
	 *
	 * @param string $key The Key
	 * @return boolean
	 */
	function exists($key) {
		return $this->_handler->exists($key);
	}

	/**
	 * Remove a value from the cache by key
	 *
	 * @param string $key The Key
	 * @return boolean
	 */
	function remove($key) {
		return $this->_handler->remove($key);
	}

	/**
	 * Get the current registered handler
	 *
	 * @return null|PPI_Cache_Interface
	 */
	function getHandler() {
		return $this->_handler;
	}

	/**
	 * Set the current handler.
	 *
	 * @throws \PPI\Core\CoreException
	 * @param PPI\Cache\CacheInterface $handler
	 * @return void
	 */
	function setHandler(PPI\Cache\CacheInterface $handler) {
		$this->_handler = $handler;
		if($this->_handler->enabled() === false) {
			throw new \PPI\Core\CoreException('The cache driver ' . get_class($handler) . ' is currently disabled.');
		}
		$this->_handler->init();
	}

}