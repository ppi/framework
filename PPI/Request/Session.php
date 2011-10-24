<?php
namespace PPI\Request;
class Session extends RequestAbstract {
	/**
	 * Constructor
	 *
	 * Stores the given $_SESSION data or tries to fetch
	 * $_SESSION if the given array is empty or not given
	 *
	 * @param array $session
	 */
	public function __construct(array $session = null) {
		if ($session !== null) {
			$this->_array        = $session;
			$this->_isCollected  = false;
		} else {
			if(isset($_SESSION)) {
				$this->_array = $_SESSION;
			}
		}
	}

	/**
	 * Set an offset
	 *
	 * Required by ArrayAccess interface
	 *
	 * @param string $offset
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if ($value === null) {
			$this->offsetUnset($offset);
		}

		$this->_array[$offset] = $value;

		if ($this->_isCollected) {
			$_SESSION[$offset] = $value;
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
	public function offsetUnset($offset) {
		unset($this->_array[$offset]);

		if ($this->_isCollected) {
			unset($_SESSION[$offset]);
		}
	}
}