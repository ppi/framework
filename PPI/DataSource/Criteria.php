<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppi.io
 */
namespace PPI\DataSource;
use PPI\Core;
class Criteria {
	
	/**
	 * List of joins
	 * 
	 * @var array
	 */
	protected $_joins = array();
	
	/**
	 * The clause to filter by
	 * 
	 * @var null
	 */
	protected $_clause = null;
	
	/**
	 * The order clause
	 * 
	 * @var null
	 */
	protected $_order = null;
	
	/**
	 * The limit clause
	 * 
	 * @var null
	 */
	protected $_limit = null;
	
	/**
	 * The group statement
	 * 
	 * @var null
	 */
	protected $_group = null;
	
	/**
	 * The columns to use
	 * 
	 * @var null
	 */
	protected $_columns = null;
	
	/**
	 * Have columns been set
	 * 
	 * @return bool
	 */
	function hasColumns() {
		return $this->_columns !== null;
	}
	
	/**
	 * Get the columns to use
	 * 
	 * @return null
	 */
	function getColumns() {
		return $this->_columns;
	}
	
	/**
	 * Set the columns to use
	 * 
	 * @param string $columns
	 * @return void
	 */
	function columns($columns) {
		$this->_columns = $columns;
		return $this;
	}
	
	/**
	 * Has a where clause been set
	 * 
	 * @return bool
	 */
	function hasWhere() {
		return $this->_clause !== null;
	}
	
	/**
	 * Get the where clause
	 * 
	 * @return null|string
	 */
	function getWhere() {
		return $this->_clause;
	}
	
	/**
	 * Set the where clause
	 * 
	 * @param $where
	 * @return void
	 */
	function where($where) {
		$this->_clause = $where;
		return $this;
	}
	
	/**
	 * Has an order clause been set
	 * 
	 * @return bool
	 */
	function hasOrder() {
		return $this->_order !== null;
	}
	
	/**
	 * Get the order clause
	 * 
	 * @return null
	 */
	function getOrder() {
		return $this->_order;
	}
	
	/**
	 * Set the order clause
	 * 
	 * @param $order
	 * @return void
	 */
	function order($order) {
		$this->_order = $order;
		return $this;
	}
	
	/**
	 * Has a group clause been set
	 * 
	 * @return bool
	 */
	function hasGroup() {
		return $this->_group !== null;
	}
	
	/**
	 * Get the group clause
	 * 
	 * @return null
	 */
	function getGroup() {
		return $this->_group;
	}
	
	/**
	 * Set the group clause
	 * 
	 * @param string $group
	 * @return void
	 */
	function group($group) {
		$this->_group = $group;
		return $this;
	}
	
	/**
	 * Has a limit been set
	 * 
	 * @return bool
	 */
	function hasLimit() {
		return $this->_limit !== null;
	}
	
	/**
	 * Get the limit
	 * 
	 * @return null
	 */
	function getLimit() {
		return $this->_limit;
	}
	
	/**
	 * Set the limit
	 * 
	 * @param string $limit
	 * @return void
	 */
	function limit($limit) {
		$this->_limit = $limit;
		return $this;
	}
	
	/**
	 * Have joins been set
	 * 
	 * @return bool
	 */
	function hasJoins() {
		return !empty($this->_joins);
	}
	
	/**
	 * Get the list of joins
	 * 
	 * @return array
	 */
	function getJoins() {
		return $this->_joins;
	}
	
	/**
	 * Do a left join
	 * 
	 * @param array $joinData
	 * @return void
	 */
	function leftJoin(array $joinData = array()) {
		$joinData['type'] = 'left';
		$this->_joins[] = $joinData;
		return $this;
	}
	
	
	/**
	 * Do an outer join
	 * 
	 * @param array $joinData
	 * @return void
	 */
	function outerJoin(array $joinData = array()) {
		$joinData['type'] = 'outer';
		$this->_joins[] = $joinData;
		return $this;
	}
	
	/**
	 * Do an inner join
	 * 
	 * @param array $joinData
	 * @return void
	 */
	function innerJoin(array $joinData = array()) {
		$this->join($joinData);
		return $this;
	}

	/**
	 * Do an inner join
	 * 
	 * @param array $joinData
	 * @return $this
	 */
	function join(array $joinData = array()) {
		$joinData['type'] = 'inner';
		$this->_joins[] = $joinData;
		return $this;
	}
	
}