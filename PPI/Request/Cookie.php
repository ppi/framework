<?php
namespace PPI\Request;
class Cookie extends RequestAbstract {

	protected $_defaults = array(
		'expire'   => 0,
		'path'     => null,
		'domain'   => null,
		'secure'   => false,
		'httponly' => false
	);

	/**
	 * Constructor
	 *
	 * Stores the given cookies or tries to fetch
	 * cookies if the given array is empty or not
	 * given
	 *
	 * @param array $cookies
	 */
	function __construct(array $cookies = array()) {
		
		if(!empty($cookies)) {
			$this->_array       = $cookies;
			$this->_isCollected = false;
		} else {
			$this->_array = $_COOKIE;
		}

	}

	/*
	 * Sync local settings with global settings
	 *
	 * @return void
	 */
	protected function resetSettings() {
		$this->_expire   = self::$expire;
		$this->_path     = self::$path;
		$this->_domain   = self::$domain;
		$this->_secure   = self::$secure;
		$this->_httponly = self::$httponly;
	}

	/**
	 * Changes object settings
	 *
	 * @param string $option Option to update
	 * @param mixed  $value  Value to set option
	 *
	 * @return void
	 */
	public function setSetting($option, $value = null) {
		if (is_array($option)) {
			foreach ($option as $key => $value) {
				$this->setSetting($key, $value);
			}
			return;
		}

		if(array_key_exists($option, $this->_defaults)) {
			$this->_defaults[$option] = $value;
		}
	}

	/**
	 * Set an offset
	 *
	 * Required by ArrayAccess interface
	 *
	 * @param string $offset
	 * @param string $value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if ($value === null) {
			return $this->offsetUnset($offset);
		}

		$this->setCookie($offset, array('content' => $value));
	}

	/**
	 * Unset an offset
	 *
	 * Reqired by ArrayAccess interface
	 *
	 * @param string $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->setcookie($offset, array(
			'content' => null,
			'expire'  => time() - 3600,
		));
		unset($this->_array[$offset]); // FIXME I think setCookie is currently too 'smart'
	}

	/**
	 * Fully usable setCookie function.
	 *
	 * @todo review the override order. 
	 * @param string $key
	 * @param array $options
	 * @return boolean
	 */
	public function setCookie($key, array $options = array()) {

		$options = array_merge($this->_defaults, $options);

		$this->_array[$key] = $options['content'];

		if(!$this->_isCollected) {
			return true;
		}

		return setcookie(
			$key, $options['content'], $options['expire'], 
			$options['path'], $options['domain'], $options['secure'], $options['httponly']
		);
	}

	/**
	 * Cookie getter to return the raw array of cookie data
	 *
	 * @param string $key
	 * @return array
	 */
	public function getCookie($key) {
		$options            = $this->_defaults;
		$options['name']    = $key;
		$options['content'] = $this->offsetGet($key);
		return $options;
	}

}