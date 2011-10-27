<?php
/**
 * The PPI Cache Component For Using The Hard Disk
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Cache
 */
namespace PPI\Cache;
use PPI\Core\CoreException;
class Disk implements \PPI\Cache\CacheInterface {

	/**
	 * The folder where the cache contents will be placed
	 *
	 * @var string
	 */
	protected $_cacheDir = null;

	/**
	 * The options passed in upon instantiation
	 *
	 * @var array
	 */
	protected $_options = array();

	public function __construct(array $options = array()) {
		$this->_options = $options;
		$this->_cacheDir = isset($options['cache_dir']) ? $options['cache_dir'] : APPFOLDER . 'Cache/Disk/';
	}

	public function init() {}

	/**
	 * Remove a key from the cache
	 * @param string $key The Key
	 * @param bool $exists flag if we know of the existence
	 * @return boolean
	 */
	public function remove($key, $exists = false) {

		$path = $this->getKeyCachePath($key);

		if ($exists || $this->exists($path)) {
			unlink($path);
			unlink($this->getKeyMetaCachePath($key));
			return true;
		}
		return false;
	}

	/**
	 * Get the full path to a cache item
	 * @param string $key
	 * @return string
	 */
	protected function getKeyCachePath($key) {
		// @TODO robert: add leveled key paths to avoid slow disk seeks
		return $this->_cacheDir . 'default--' . $key;
	}

	/**
	 * Get the full path to a cache item's metadata file
	 *
	 * @param string $key
	 * @return string
	 */
	protected function getKeyMetaCachePath($key) {
		return $this->getKeyCachePath($key) . '.metadata';
	}

	/**
	 * Check if a key exists in the cache
	 * @param string $key The Key(s)
	 * @return boolean
	 */
	public function exists($key) {
		$path = $this->getKeyCachePath($key);
		if(false === file_exists($path)) {
			return false;
		}
		$meta = unserialize(file_get_contents($this->getKeyMetaCachePath($key)));
		// See if the item has a ttl and if it has expired then we delete it.
		if(is_array($meta) && $meta['ttl'] > 0 && $meta['expire_time'] < time()) {
			// Remove the cache item and its metadata file.
			$this->remove($key, true); // if we don't expect the existence, we could get an endless loop!
			return false;
		}
		return true;
	}

	/**
	 * Set a value in the cache
	 *
	 * @param string $key The Key
	 * @param mixed $data The Data
	 * @param integer $ttl The Time To Live
	 * @return boolean
	 */
	public function set($key, $data, $ttl = 0) {

		$path = $this->getKeyCachePath($key);
		$this->remove($key);
		$cacheDir = dirname($path);
		if(!is_dir($cacheDir)) {
			try {
			   mkdir($cacheDir);
			} catch(CoreException $e) {
				throw new CoreException('Unable to create directory:<br>(' . $cacheDir . ')');
			}
		}
		if(false === is_writeable($cacheDir)) {
			$fileInfo = pathinfo(dirname($path));
			@chmod($cacheDir, 775);
			if(false === is_writable($cacheDir)) {
				throw new CoreException('Unable to create cache file: ' . $key. '. Cache directory not writeable.<br>(' . $this->_cacheDir . ')<br>Current permissions: ');
			}
		}

		$meta = array(
			'expire_time' => time() + (int) $ttl,
			'ttl'         => $ttl,
			'serialized'  => false
		);
		if(!is_scalar($data)) {
			$meta['serialized'] = true;
			$data = serialize($data);
		}


		return file_put_contents($path, $data, LOCK_EX) > 0
			&& file_put_contents($this->getKeyMetaCachePath($key), serialize($meta), LOCK_EX) > 0;
	}

	/**
	 * Get a value from cache
	 *
	 * @param string $key The Key
	 * @param mixed $default The Default Value
	 * @return mixed
	 */
	public function get($key, $default = null) {
		if(false === $this->exists($key)) {
			return $default;
		}
		$metaData = unserialize(file_get_contents($this->getKeyMetaCachePath($key)));
		$content = file_get_contents($this->getKeyCachePath($key));
		return $metaData['serialized'] ? unserialize($content) : $content;
	}

	/**
	 * Check if this adapter is enabled or not.
	 *
	 * @return boolean
	 */
	public function enabled() { return true; }

	/**
	 * Increment the value in the cache
	 *
	 * @param  $key The key
	 * @param  $inc The value to increment by
	 * @return void
	 */
	public function increment($key, $inc) { }

	/**
	 * Decrement the value in the cache
	 *
	 * @param  $key The Key
	 * @param  $dec The value to decrement by
	 * @return void
	 */
	public function decrement($key, $dec) { }

}