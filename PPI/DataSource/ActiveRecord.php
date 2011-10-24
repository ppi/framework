<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppiframework.com
 */
namespace PPI\DataSource;
class ActiveRecord {
	
	/**
	 * The table name
	 * 
	 * @var null
	 */
	protected $_table = null;
	
	/**
	 * The primary key
	 * 
	 * @var null
	 */
	protected $_primary = null;
	
	/**
	 * The datasource connection
	 * 
	 * @var null
	 */
	protected $_conn = null;
	
	/**
	 * Find a single record by value
	 * 
	 * @param integer $val
	 * @return void
	 */
	function find($val) {

		if($this->_table === null || $this->_primary) {
			throw new PPI_Exception('You need to specify a table name and primary key');
		}
		$stmt = $this->_conn->prepare("SELECT * FROM {$this->_table} WHERE {$this->_primary} = :val");
		$stmt->execute(array('val' => $val));
		$row = $stmt->fetch();
		$stmt->closeCursor();
		$this->_conn->close();
		return $row;
	}
	
	/**
	 * Fetch rows from the current table based on some conditions
	 * 
	 * @param $identifier
	 * @return void
	 */
	function fetchAll($identifier) {
	}

}