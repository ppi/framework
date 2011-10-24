<?php

/**
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Cache
 * @link	  http://www.ppiframework.com
 */
namespace PPI\Cache;
interface CacheInterface {

	/**
	 * Get cache contents
	 *
	 * @abstract
	 * @param  string $key The Key
	 * @return mixed
	 */
	function get($key);

	/**
	 * Perform any initialisation steps on the driver. (such as ->connect())
	 *
	 * @abstract
	 * @return void
	 */
	function init();

	/**
	 * Set cache contents
	 *
	 * @abstract
	 * @param string $key The Key
	 * @param  mixed $data The Data
	 * @param int $ttl The TTL (Time to live)
	 * @return boolean
	 */
	function set($key, $data, $ttl = 0);

	/**
	 * Check if cache contents exists
	 *
	 * @abstract
	 * @param mixed $key The Key(s)
	 * @return boolean
	 */
	function exists($key);

	/**
	 * Remove cache content
	 *
	 * @abstract
	 * @param  $key
	 * @return boolean
	 */
	function remove($key);

	/**
	 * Increment the cache value
	 *
	 * @abstract
	 * @param string $key The Key
	 * @param numeric $inc The Incremental Value
	 * @return int
	 */
	function increment($key, $inc);

	/**
	 * Decrement the cache value
	 *
	 * @abstract
	 * @param string $key The Key
	 * @param mixed $dec The Decremental Value
	 * @return int
	 */
	function decrement($key, $dec);

	/**
	 * Check if a cache driver is enabled
	 *
	 * @abstract
	 * @return boolean
	 */
	function enabled();

}