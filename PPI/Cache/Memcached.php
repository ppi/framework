<?php
/**
 *
 * @version   1.0
 * @author	Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Cache
 */
namespace PPI\Cache;
class Memcached implements CacheInterface {

	/**
	 * The memcache(d) driver
	 *
	 * @var Memcache|Memcached
	 */
	protected $_handler;

	/**
	 * If a server has been added. Default is false
	 *
	 * @var bool
	 */
	protected $_serverAdded = false;

	/**
	 * The PPI_Cache_Memcached constructor
	 *
	 * @todo use $this->enabled()
	 * @todo Investigate if we can use memcache(d) or just one (with API usage complying to the interface)
	 * @throws PPI_Exception
	 *
	 */
	function __construct() {
		if(extension_loaded('Memcached')) {
			$this->_handler = new Memcached();
		} elseif(extension_loaded('Memcache')) {
			$this->_handler = new Memcache();
		} else {
			throw new PPI_Exception('Unable to use Memcache. Extension not loaded.');
		}
	}

	function init() {}

	/**
	 * Get a value from cache
	 *
	 * @param string $key The Key
	 * @return mixed
	 */
	function get($key) {
		if(false === $this->_serverAdded) {
			$this->addServer('localhost');
		}
		return $this->_handler->get($key);
	}

	/**
	 * Set a value in the cache
	 *
	 * @param string $key The Key
	 * @param mixed $data The Data
	 * @param integer $ttl The Time To Live
	 * @return boolean
	 */
	function set($key, $data, $ttl = 0) {
		if(false === $this->_serverAdded) {
			$this->addServer('localhost');
		}
		return $this->_handler->set($key, $data, (is_numeric($ttl) ? $ttl : strtotime($ttl)));
	}

	/**
	 * Increment a cache value
	 *
	 * @param string $key The Key
	 * @param numeric $inc The incremental value
	 * @return numeric
	 */
	function increment($key, $inc) {
		return $this->_handler->increment($key, $inc);
	}

	/**
	 * Decrement a cache value
	 *
	 * @param string $key The Key
	 * @param numeric $dec The Decremental Value
	 * @return numeric
	 */
	function decrement($key, $dec) {
		return $this->_handler->decrement($key, $dec);
	}

	/**
	 * Clear the cache
	 *
	 * @return boolean
	 */
	function clear() { $this->_handler->flush(); }

	/**
	 * Check if a key exists in the cache
	 *
	 * @param string $key The Key
	 * @return boolean
	 */
	function exists($key) {}

	/**
	 * Remove a key from the cache
	 *
	 * @param string $key The Key
	 * @return boolean
	 */
	function remove($key) {
		return $this->_handler->delete($key);
	}

	/**
	 * Add a memcached server to connect to
	 *
	 * @param string $host The Hostname
	 * @param integer $port The Port
	 * @param integer $weight The Weight
	 */
	function addServer($host, $port = 11211, $weight = 10) {

		$this->_serverAdded = true;
		$this->_handler->addServer($host, $port, $weight);
	}

	/**
	 * Check if the memcached extension is loaded.
	 *
	 * @return boolean
	 */
	function enabled() { return extension_loaded('memcached'); }

}
