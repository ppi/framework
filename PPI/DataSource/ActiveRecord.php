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

	function __construct($id = null, array $options = array()) {

		parent::__construct($options);
		if($id !== null) {
			$this->_data       = $this->find($id);
			$this->_identifier = $id;
		}
	}

	function save() {
		return $this->update($this->_data, array($this->_primary => $this->_identifier));
	}

	function __set($key, $val) {
		if(isset($this->_data[$key])) {
			$this->_data[$key] = $val;
		}
	}

	function __get($key) {
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}

}