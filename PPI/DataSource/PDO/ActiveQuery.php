<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppiframework.com
 */
namespace PPI\DataSource\PDO;
class ActiveQuery {
	
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
	 * The meta data for this instantiation
	 *
	 * @var array
	*/
	protected $_meta = array(
		'conn'    => null,
		'table'   => null,
		'primary' => null
	);

	/**
	 * The options for this instantiation
	 *
	 * @var array
	*/
	protected $_options = array();
	
	function __construct(array $options = array()) {

		// Setup our connection from the key passed to meta['conn']
		if(isset($options['meta'])) {
			$this->_meta = $options['meta'];
		}
		
		$this->_options = $options;
	}
	
	function setConn($conn) {
		$this->_conn = $conn;
	}
	
	function fetchAll($criteria = null) {
		
		$columns = $joins = $clauses = $order = $group = $order = $limit = ''; 
		if($criteria !== null && $criteria instanceof \PPI\DataSource\Criteria) {
			$columns = $criteria->hasColumns() ? $criteria->getColumns()                     : '*';
			$joins   = $criteria->hasJoins()   ? $this->generateJoins($criteria->getJoins()) : '';
			$clauses = $criteria->hasWhere()   ? ' WHERE ' . $criteria->getWhere()           : '';
			$group   = $criteria->hasGroup()   ? ' GROUP BY ' . $criteria->getGroup()        : '';
			$order   = $criteria->hasOrder()   ? ' ORDER BY ' . $criteria->getOrder()        : '';
			$limit   = $criteria->hasLimit()   ? ' LIMIT ' . $criteria->getLimit()           : '';
		}
		$query = "SELECT $columns FROM {$this->_meta['table']} $joins $clauses $group $order $limit";
		die($query);
		return $this->_conn->query($query)->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	/**
	 * Generate SQL for Joins.
	 * 
	 * @param array $joins
	 * @return string
	 */
	protected function generateJoins(array $joins) {
		$sql = '';
		$joinTypes = array('inner', 'left', 'right', 'outer');
		foreach($joins as $join) {
			
			if(isset($joinTypes[$join['type']])) {
				$sql .= " {$join['']} JOIN {$join['table']} ON {$join['on']} ";
			}
			
		}
		return $sql;
	}
	
	/**
	 * Find a row by primary key
	 * 
	 * @param string $id
	 * @return 
	 */
	function find($id) {
		return $this->_conn->fetchAssoc("SELECT * FROM {$this->_meta['table']} WHERE {$this->_meta['primary']} = ?", array($id));
	}

	function fetch(array $where, array $params = array()) {
		die("SELECT * FROM {$this->_meta['table']} WHERE $where");
		return $this->_conn->fetchAssoc("SELECT * FROM {$this->_meta['table']} WHERE $where", $params);
	}

	function insert($data) {
		$this->_conn->insert($this->_meta['table'], $data);
		return $this->_conn->lastInsertId();
	}

	function delete($where) {
		return $this->_conn->delete($this->_meta['table'], $where);
	}
	
	function update($data, $where) {
		return $this->_conn->update($this->_meta['table'], $data, $where);
	}

}