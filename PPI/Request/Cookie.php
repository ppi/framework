<?php
namespace PPI\Request;
class Cookie extends RequestAbstract {
	
	
	static public $expire   = 0;
	static public $path     = null;
	static public $domain   = null;
	static public $secure   = false;
	static public $httponly = false;

	protected $_expire   = 0;
	protected $_path     = null;
	protected $_domain   = null;
	protected $_secure   = false	;
	protected $_httponly = false;

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

		$this->resetSettings();
	}

	/*
	 * Sync local settings with global settings
	 *
	 * @return void
	 */
	function resetSettings() {
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
	function setSetting($option, $value) {
		
		if(property_exists($this, '_' . $option)) {
			$this->{'_' . $option} = $value;
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
	function offsetSet($offset, $value) {
		if ($value === null) {
			$this->offsetUnset($offset);
		}

		$this->_array[$offset] = $value;

		if ($this->_isCollected) {
			$this->_setcookie($offset, $value, $this->_expire, $this->_path, $this->_domain, $this->_secure, $this->_httponly);
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

	/**
	 * Set the cookie, this is an alias to php.net/setcookie()
	 * 
	 * @param string $name
	 * @param string $content
	 * @param integer $expire
	 * @param string $path
	 * @param string $domain
	 * @param boolean $secure
	 * @param boolean $httponly
	 * @return void
	 */
	protected function _setcookie($name, $content, $expire, $path, $domain, $secure, $httponly) {
		setcookie($name, $content, $expire, $path, $domain, $secure, $httponly);
	}
	
	/**
	 * Function to return all the current set cookie data.
	 * Useful for debugging and testing. Could potentially be removed later.
	 * 
	 * @return array
	 */
	public function getAll() {
		return $this->_array;
	}

}