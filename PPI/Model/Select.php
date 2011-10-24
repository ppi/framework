<?php
/**
 * @version   1.0
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @copyright Digiflex Development
 * @package   Model
 * @link      www.ppiframework.com
 */
class PPI_Model_Select {

    /**
     * The inner join
     */
    const INNER_JOIN     = 'INNER JOIN';

    /**
     * The left join
     */
    const LEFT_JOIN      = 'LEFT JOIN';

    /**
     * The right join
     */
    const RIGHT_JOIN     = 'RIGHT JOIN';

    /**
     * The full join
     */
    const FULL_JOIN      = 'FULL JOIN';

    /**
     * The ross join
     */
    const CROSS_JOIN     = 'CROSS JOIN';

    /**
     * The natural join
     */
    const NATURAL_JOIN   = 'NATURAL JOIN';

    /**
     * The SQL Wildcard
     */
    const SQL_WILDCARD   = '*';

    /**
     * The SQL Select
     */
    const SQL_SELECT     = 'SELECT';

    /**
     * The SQL Union
     */
    const SQL_UNION      = 'UNION';

    /**
     * The SQL Union All
     */
    const SQL_UNION_ALL  = 'UNION ALL';

    /**
     * The SQL From
     */
    const SQL_FROM       = 'FROM';

    /**
     * The SQL Where
     */
    const SQL_WHERE      = 'WHERE';

    /**
     * The SQL Distinct
     */
    const SQL_DISTINCT   = 'DISTINCT';

    /**
     * The SQL Group By
     */
    const SQL_GROUP_BY   = 'GROUP BY';

    /**
     * The SQL Limit
     */
	const SQL_LIMIT		 = 'LIMIT';

    /**
     * The SQL Order By
     */
    const SQL_ORDER_BY   = 'ORDER BY';

    /**
     * The SQL Having
     */
    const SQL_HAVING     = 'HAVING';

    /**
     * The SQL For Update
     */
    const SQL_FOR_UPDATE = 'FOR UPDATE';

    /**
     * The SQL AND
     */
    const SQL_AND        = 'AND';

    /**
     * The SQL AS
     */
    const SQL_AS         = 'AS';

    /**
     * The SQL or
     */
    const SQL_OR         = 'OR';

    /**
     * The SQL ON
     */
    const SQL_ON         = 'ON';

    /**
     * The SQL Desc
     */
    const SQL_ASC        = 'ASC';

    /**
     * The SQL Desc
     */
    const SQL_DESC       = 'DESC';

	protected $_connection;

    /**
     * @var The table in the FROM section of the SQL
     */
	protected $_name;

    /**
     * The array of where clauses
     *
     * @var array
     */
	protected $_where	= array();
    
    /**
     * The ORDER BY part of the SQL
     *
     * @var string
     */
	protected $_order = '';
	// this error when we try to assign it to self::$_name;

    /**
     * The table in the FROM section
     *
     * @var string
     */
	protected $_from = '';

    /**
     * The columns in the SELECt
     *
     * @var string
     */
	protected $_columns = self::SQL_WILDCARD;

    /**
     * The LIMIT string
     *
     * @var array
     */
	protected $_limit = array();

    /**
     * The SQL group by
     *
     * @var string
     */
	protected $_group = '';

    /**
     * The whole query
     *
     * @var string
     */
	protected $_query = '';

    /**
     * The current fetchmode
     *
     * @var string
     */
	protected $_fetchMode = 'assoc';

    /**
     * The list of inner joins
     *
     * @var array
     */
	protected $_innerJoin = array();

    /**
     * The order in which to generate the joins
     *
     * @var array
     */
	protected $_joinOrder = array();

    /**
     * The amount of joins
     *
     * @var int
     */
	protected $_joinCount = 0;

	protected $_queries = array();

    /**
     * The model that this class was initialised from
     *
     * @var object
     */
	protected $_model;


	function __construct($model) {
		$this->_model = $model;
	}

	/**
	 * Set the FROM table
     *
	 * @param string $name The Table Name
     * @return $this
	 */
	function from($name = '') {
		$this->_from = ($name != '') ? $name : $this->_name;
		return $this;
	}

	/**
	 * Perform an INNER JOIN
     *
	 * @param string $table The Table
	 * @param string $on The ON Clause
     * @return $this
	 */
	function join($table, $on) {
		$this->innerJoin($table, $on);
		return $this;
	}

	/**
	 * Perform an INNER JOIN
     *
	 * @param string $table The Table
	 * @param string $on The ON Clause
     * @return $this
	 */
	function innerJoin($table, $on) {
		$this->addJoin($table, $on, self::INNER_JOIN);
		return $this;
	}

	/**
	 * Perform a LEFT JOIN
     *
	 * @param string $table The Table
	 * @param string $on The ON Clause
     * @return $this
	 */
	function leftJoin($table, $on) {
		$this->addJoin($table, $on, self::LEFT_JOIN);
		return $this;
	}

	/**
	 * Perform a RIGHT JOIN
     *
	 * @param string $table The Table
	 * @param string $on The ON Clause
     * @return $this
	 */
	function rightJoin($table, $on) {
		$this->addJoin($table, $on, self::RIGHT_JOIN);
		return $this;
	}

	/**
	 * Add a join to the joinOrder
     *
	 * @param string $table The Table Name
	 * @param string $on The ON Clause
	 * @param string $type ('left', 'right', 'inner')
     * @return void
	 */
	function addJoin($table, $on, $type) {
		$this->_joinCount++;
		array_push($this->_joinOrder, array(
			'table' => $table,
			'on' 	=> $on,
			'type' 	=> $type
		));
	}

	/**
	 * Set the where clause(s)
     *
	 * @param mixed $clause The Clause(s)
     * @return $this
	 */
	function where($clause = null) {
		$this->_where = array_merge($this->_where, (array) $clause);
		return $this;
	}

	/**
	 * Set the ORDER BY
     *
	 * @param string $order The ORDER BY
     * @return $this
	 */
	function order($order = '') {
		$this->_order = $order;
		return $this;
	}

	/**
	 * Set the GROUP BY
     *
	 * @param string $group The GROUP BY
     * @return $this
	 */
	function group($group = '') {
		$this->_group = $group;
		return $this;
	}

	/**
	 * Set the LIMIT
     *
	 * @param string $limit
     * @return $this
	 */
	function limit($limit) {
		$this->_limit = $limit;
		return $this;
	}

	/**
	 * Set the columns on the SELECT
     *
	 * @param string $columns The Columns
     * @return $this
	 */
	function columns($columns) {
		$this->_columns = ($columns != '') ? $columns : self::SQL_WILDCARD;
		return $this;
	}

	/**
	 * Get the rows back from $this->query()
     *
	 * @return PPI_Model_Resultset
	 */
	function getList() {
		$this->generateQuery();
		return $this->query();
	}

	/**
	 * Magic string cast function, return the query name
     *
     * @return string
	 */
	function __toString() {
		if($this->_query == '') {
			$this->generateQuery();
		}
		return $this->_query;
	}

	/**
	 * Generate the query
     *
     * @return void
	 */
	protected function generateQuery() {
		$query = '';
		// Security Cleanup prior to query generation
		// select + from
		$query .= self::SQL_SELECT . ' ' . $this->_columns . ' ' . self::SQL_FROM . ' ' . $this->_from;

		if(count($this->_joinOrder) > 0) {
			foreach($this->_joinOrder as $join) {
				$query .= ' ' . $join['type'] . ' ' . $join['table'] . ' ' . self::SQL_ON . ' ' . $join['on'];
			}
		}
		// where
		if(!empty($this->_where)) {
			$query .= ' ' . self::SQL_WHERE . ' (' . implode(' ' . self::SQL_AND . ' ', $this->_where) . ')';
		}

		// group by
		if($this->_group != '') {
			$query .= ' ' . self::SQL_GROUP_BY . ' ' . $this->_group;
		}

		// order by
		if($this->_order != '') {
			$query .=  ' ' . self::SQL_ORDER_BY . ' ' . $this->_order;
		}

		// limit
		if(is_array($this->_limit) && count($this->_limit) > 0) {
			if(count($this->_limit) == 1) {
				$limit = ' ' . self::SQL_LIMIT . ' ' . $this->_limit[0];
				$query .= $limit;
			} elseif(count($this->_limit) == 2) {
				$limit = ' ' . self::SQL_LIMIT . ' ' . $this->_limit[0] . ', ' . $this->_limit[1];
				$query .= $limit;
			}
		} elseif(is_string($this->_limit) && $this->_limit != '') {
			$query .= ' ' . self::SQL_LIMIT . ' ' . $this->_limit;
		}
		$this->_query = $query;
	}

	/**
	 * Log the error in the database
     *
     * @throws PPI_Exception
     * @return void
	 */
	protected function logError() {
		// check if we need to email
		// product debugging information
		throw new PPI_Exception("SQL Error: " . mysql_error($this->_connection), $this->_queries);
	}

	/**
	 * Run the query
	 *
	 * @return PPI_Model_Resultset
	 */
	protected function query() {
		return $this->_model->query($this->_query);

	}
}
