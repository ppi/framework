<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppiframework.com
 */
namespace PPI\DataSource;
class ActiveQuery {
	
	/**
	 * The table name
	 * 
	 * @var null
	 */
	protected $_handler = null;

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
		if(isset($this->_meta['conn'])) {
			
			$dsConfig = \PPI\Core::getDataSource()->getConnectionConfig($this->_meta['conn']);
			$connType = $dsConfig['type'];
			if($connType === 'mongo') {
				$this->_handler = new \PPI\DataSource\Mongo\ActiveQuery(array(
					'meta' => $this->_meta
				));
			} elseif(substr($connType, 0, 3) === 'pdo') {
				$this->_handler = new \PPI\DataSource\PDO\ActiveQuery(array(
					'meta' => $this->_meta
				));
			}
			$this->_conn = \PPI\Core::getDataSourceConnection($this->_meta['conn']);
			$this->_handler->setConn($this->_conn);
		}

		$this->_options = $options;
	}
	
	function fetchAll($criteria = null) {
		return $this->_handler->fetchAll($criteria);
	}

	function find($id) {
		return $this->_handler->find($id);
	}

	function fetch(array $where, array $params = array()) {
		return $this->_handler->fetch($where, $params);
	}

	function insert($data) {
		return $this->_handler->insert($data);
	}

	function delete($where) {
		return $this->_handler->delete($where);
	}
	
	function update($data, $where) {
		return $this->_handler->update($data, $where);
	}

}