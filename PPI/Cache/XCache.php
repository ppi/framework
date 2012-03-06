<?php
/**
 *
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Digiflex Development
 * @package   Cache
 */
namespace PPI\Cache;
class XCache implements CacheInterface {

	protected $_defaults = array(
		'server' => '127.0.0.1:6379',
		'expiry' => 0 // Never
	);

	/**
	 * @param array $options The options that override the default
	 */
	function __construct(array $options = array()) {
		$this->_defaults = ($options + $this->_defaults);
	}

	/**
	 * Get a value from cache
	 * @param string $key The Key
	 * @return mixed
	 */
	function get($key) { return xcache_get($key); }

	function init() {}

	/**
	 * Set a value in the cache
	 * @param string $key The Key
	 * @param mixed $data The Data
	 * @param mixed $ttl The Time To Live. Integer or String (strtotime)
	 * @return boolean True on succes, False on failure.
	 */
	function set($key, $data, $ttl = 0) {
		return xcache_set($key, $data, (is_numeric($ttl) ? $ttl : strtotime($ttl)));
	}

	/**
	 * Check if a key exists in the cache
	 * @param string $key The Key
	 * @return boolean
	 */
	function exists($key) { return xcache_isset($key); }

	/**
	 * Remove a key from the cache
	 * @param string $key The Key
	 * @return boolean
	 */
	function remove($key) { return xcache_unset($key); }

	/**
	 * Wipe the cache contents
	 * @todo This uses some kind of authentication - must look into it more.
	 * @return unknown
	 */
	function clear() {
		$enableAuth = ini_get('xcache.admin.enable_auth') === 'On';
//		if($enableAuth && (!isset() || )) {

//		}
	}

	/**
	 * Increment a numerical value
	 *
	 * @param string $key The Key
	 * @param numeric $inc The incremental value
	 * @return numeric
	 */
	function increment($key, $inc = 1) { return xcache_inc($key, $inc); }

	/**
	 * Enter description here...
	 *
	 * @param string $key The Key
	 * @param numeric $dec The decremental value
	 * @return numeric
	 */
	function decrement($key, $dec = 1) { return xcache_dec($key, $dec); }

	/**
	 * Check if the APC extension has been loaded and is enabled in its configuration.
	 *
	 * @return boolean
	 */
	function enabled() { return extension_loaded('xcache'); }

}