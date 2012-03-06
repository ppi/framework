<?php
namespace PPI\Request;
abstract class RequestAbstract implements \ArrayAccess, \Iterator, \Countable {

	/**
	 * The actual data we're dealing with
	 *
	 * @var array
	 */
	protected $_array = array();

	/**
	 * Has the data for this instantiated class been provided? or automatically collected
	 *
	 * @var bool
	 */
	protected $_isCollected = true;

	/**
	 * Checks if the data was collected or manual set
	 *
	 * Returns true if all data is collected
	 * from php's environment, false if the current
	 * data is set manuals
	 *
	 * @return bool
	 */
	public function isCollected() {
		return $this->_isCollected;
	}

	/**
	 * ArrayAccess implementation for offsetExists
	 *
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->_array[$offset]);
	}

	/**
	 * ArrayAccess implementation for offsetGet
	 *
	 * @param string $offset
	 *
	 * @return mixed
	 */
	public function offsetGet($offset) {
		if ($this->offsetExists($offset)) {
			return $this->_array[$offset];
		}
		return null;
	}

	/**
	 * ArrayAccess implementation for offsetSet
	 *
	 * @param string $offset
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if ($value === null) {
			return $this->offsetUnset($offset);
		}

		$this->_array[$offset] = $value;
	}

	/**
	 * ArrayAccess implementation for offsetUnset
	 *
	 * @param string $offset
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->_array[$offset]);
	}

	/**
	 * Iterator implementation for current
	 *
	 * @return mixed
	 */
	public function current() {
		return current($this->_array);
	}

	/**
	 * Iterator implementation for key
	 *
	 * @return scalar
	 */
	public function key() {
		return key($this->_array);
	}

	/**
	 * Iterator implementation for next
	 *
	 * @return void
	 */
	public function next() {
		return next($this->_array);
	}

	/**
	 * Iterator implementation for rewind
	 *
	 * @return void
	 */
	public function rewind() {
		return reset($this->_array);
	}

	/**
	 * Iterator implementation for valid
	 *
	 * @return bool
	 */
	public function valid() {
		return key($this->_array) !== null;
	}

	/**
	 * Countable implementation for count
	 *
	 * @return int
	 */
	public function count() {
		return count($this->_array);
	}

	/**
	 * Return all the array elements
	 *
	 * @return array
	 */
	public function all() {
		return $this->_array;
	}
}