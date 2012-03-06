<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppi.io
 */
namespace PPI\DataSource;
class ActiveRecord extends ActiveQuery {
	
	protected $_identifier = null;

	protected $_data = array();

	/**
	 * 
	 * @todo maybe throw an exception if $id is passed but the record is blank
	 * @param null|integer $id
	 * @param array $options
	 */
	function __construct($id = null, array $options = array()) {

		parent::__construct($options);
		if($id !== null) {
			$this->_data       = $this->find($id);
			$this->_identifier = $id;
		}
	}

	/**
	 * Save the users record. If there is an identifier set, then it's update mode, else back to insert mode.
	 * 
	 * @return integer (If insert, the new insert ID, If update, the num of rows affected (should be 1));
	 */
	function save() {
		if($this->_identifier !== null) {
			return $this->update($this->_data, array($this->_primary => $this->_identifier));
		}
		return $this->insert($this->_data);
		
	}

	function __set($key, $val) {
		$this->_data[$key] = $val;
	}

	function __get($key) {
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}

}