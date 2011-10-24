<?php

/**
 *
 * @author	 Paul Dragoonis <dragoonis@php.net>
 * @license  http://opensource.org/licenses/mit-license.php MIT
 * @link     http://www.ppiframework.com
 * @package  Cache
 */
namespace PPI\Cache;
class Apc implements CacheInterface {

	/**
	 * Get a value from cache
	 * @param string $key The Key
	 * @return mixed
	 */
	public function get($key) { return apc_fetch($key); }

	public function init() {}

	/**
	 * Set a value in the cache
	 * @param string $key The Key
	 * @param mixed $value The Value
	 * @param mixed $ttl The Time To Live. Integer or String (strtotime)
	 * @return boolean True on succes, False on failure.
	 */
	public function set($key, $value, $ttl = 0) {
		return apc_store($key, $value, (is_numeric($ttl) ? $ttl : strtotime($ttl)));
	}

	/**
	 * Check if a key exists in the cache
	 * @param string $key The Key
	 * @return boolean
	 */
	public function exists($key) { return apc_exists($key); }

	/**
	 * Remove a key from the cache
	 * @param string $key The Key
	 * @return boolean
	 */
	public function remove($key) { return apc_delete($key); }

	/**
	 * Wipe the cache contents
	 *
	 * @return unknown
	 */
	public function clear() { return apc_clear_cache('user'); }

	/**
	 * Increment a numerical value
	 *
	 * @param string $key The Key
	 * @param numeric $inc The incremental value
	 * @return numeric
	 */
	public function increment($key, $inc = 1) { return apc_inc($key, $inc); }

	/**
	 * Enter description here...
	 *
	 * @param string $key The Key
	 * @param numeric $dec The decremental value
	 * @return numeric
	 */
	public function decrement($key, $dec = 1) { return apc_dec($key, $dec); }

	/**
	 * Check if the APC extension has been loaded and is enabled in its configuration.
	 *
	 * @return boolean
	 */
	public function enabled() {
		return extension_loaded('apc') && ini_get('apc.enabled') && in_array(php_sapi_name(), array('fpm', 'cli', 'cgi'));
	}

}