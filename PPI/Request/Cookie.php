<?php
namespace PPI\Request;
class Cookie extends RequestAbstract {
	static public $expiration = null;
	static public $path       = null;
	static public $domain     = null;
	static public $secure     = null;
	static public $httponly   = null;

	protected $_expiration = null;
	protected $_path       = null;
	protected $_domain     = null;
	protected $_secure     = null;
	protected $_httponly   = null;

	/**
	 * Constructor
	 *
	 * Stores the given cookies or tries to fetch
	 * cookies if the given array is empty or not
	 * given
	 *
	 * @param array $cookies
	 */
	function __construct(array $cookies = null) {
		if($cookies !== null) {
			$this->_array       = $cookies;
			$this->_isCollected = false;
		} else {
			$this->_array = $_COOKIE;
		}

		$this->resetSettings();
	}

	/*
	 * Sync local settings with global settings
	 *
	 * @return void
	 */
	function resetSettings() {
		$this->_expiration = $this->expiration;
		$this->_path       = $this->path;
		$this->_domain     = $this->domain;
		$this->_secure     = $this->secure;
		$this->_httponly   = $this->httponly;
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
	function offsetSet($offset, $value) {
		if ($value === null) {
			return $this->offsetUnset($offset);
		}

		$this->_array[$offset] = $content;

		if ($this->_isCollected) {
			$this->_setcookie($offset, $content, $this->_expire, $this->_path, $this->_domain, $this->_secure, $this->_httponly);
		}
	}

	/**
	 * Unset an offset
	 *
	 * Required by ArrayAccess interface
	 *
	 * @param string $offset
	 *
	 * @return void
	 */
	function offsetUnset($offset) {
		$this->_array[$offset] = null;

		if ($this->_isCollected) {
			setcookie($offset, null, time() - 3600);
		}
	}

	private function _setcookie($name, $content, $expire, $path, $domain, $secure, $httponly) {
		setcookie($name, $content, $expire, $path, $domain, $secure, $httponly);
	}

}