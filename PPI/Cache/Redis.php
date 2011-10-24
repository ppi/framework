<?php

/**
 * @author	  Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Cache
 * @link      http://www.ppiframework.com/docs/cache.html
 * @link      https://github.com/nicolasff/phpredis
 */
namespace PPI\Cache;
class Redis implements CacheInterface {

	protected $_defaults = array(
		'server' => '127.0.0.1:6379',
		'expiry' => 0 // Never
	);

	/**
	 * The Redis handler
	 *
	 * @var null|Redis
	 */
	protected $_handler = null;

	/**
	 * @param array $options The options that override the default
	 */
	function __construct(array $options = array()) {
		$this->_defaults = ($options + $this->_defaults);
	}

	function init() {
		list($ip, $port) = explode(':', $this->_defaults['server']);
		$this->_handler = new Redis();
		$this->_handler->connect($ip, $port);
//		$this->_handler->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
	}

	/**
	 * Get a value from cache
	 * @param mixed $key The Key(s)
	 * @return mixed
	 */
	function get($key) {
		return is_array($key) ? $this->_handler->getMultiple($key) : $this->_handler->get($key);
	}

	/**
	 * Set a value in the cache
	 * @param string $key The Key
	 * @param mixed $data The Data
	 * @param mixed $ttl The Time To Live. Integer or String (strtotime)
	 * @return boolean True on succes, False on failure.
	 */
	function set($key, $data, $ttl = null) {
		if(null !== $ttl && is_string($data)) {
			$ttl = strtotime($ttl);
		}
		return $this->_handler->set($key, $data, $ttl !== null ? $ttl : $this->_defaults['expiry']);
	}

	/**
	 * Check if a key exists in the cache
	 * @param string $key The Key
	 * @return boolean
	 */
	function exists($key) { return $this->_handler->exists($key); }

	/**
	 * Remove a key from the cacheincre
	 * @param string $key The Key
	 * @return boolean
	 */
	function remove($key) { return $this->_handler->delete($key); }

	/**
	 * Wipe the cache contents
	 *
	 * @return boolean
	 */
	function clear() { return $this->_handler->flushdb(); }

	/**
	 * Increment a numerical value
	 *
	 * @param string $key The Key
	 * @param numeric $inc The incremental value
	 * @return numeric
	 */
	function increment($key, $inc = 1) { return $this->_handler->incr($key, $inc); }

	/**
	 * Enter description here...
	 *
	 * @param string $key The Key
	 * @param numeric $dec The decremental value
	 * @return numeric
	 */
	function decrement($key, $dec = 1) { return $this->_handler->decr($key, $dec); }

	/**
	 * Check if the Redis extension has been loaded and is enabled in its configuration.
	 *
	 * @return boolean
	 */
	function enabled() { return extension_loaded('redis'); }

}
