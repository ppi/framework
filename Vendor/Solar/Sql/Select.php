<?php
/**
 * 
 * Class for SQL select generation and results.
 * 
 * {{code: php
 *     $select = Solar::factory('Solar_Sql_Select');
 *     
 *     // select these columns from the 'contacts' table
 *     $select->from('contacts', array(
 *       'id',
 *         'n_last',
 *         'n_first',
 *         'adr_street',
 *         'adr_city',
 *         'adr_region AS state',
 *         'adr_postcode AS zip',
 *         'adr_country',
 *     ));
 *     
 *     // on these ANDed conditions
 *     $select->where('n_last = :lastname');
 *     $select->where('adr_city = :city');
 *     
 *     // reverse-ordered by first name
 *     $select->order('n_first DESC')
 *     
 *     // get 50 per page, when we limit by page
 *     $select->setPaging(50);
 *     
 *     // bind data into the query.
 *     // remember :lastname and :city in the where() calls above.
 *     $data = ('lastname' => 'Jones', 'city' => 'Memphis');
 *     $select->bind($data);
 *     
 *     // limit by which page of results we want
 *     $select->limitPage(1);
 *     
 *     // get a PDOStatement object
 *     $result = $select->fetchPdo();
 *     
 *     // alternatively, get an array of all rows
 *     $rows = $select->fetchAll();
 *     
 *     // or an array of one row
 *     $rows = $select->fetchOne();
 *     
 *     // find out the count of rows, and how many pages there are.
 *     // this comes back as an array('count' => ?, 'pages' => ?).
 *     $total = $select->countPages();
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Select.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_Sql_Select extends Solar_Base
{
    /**
     * 
     * A constant so we can find "ignored" params, to avoid func_num_args().
     * 
     * The md5() value of 'Solar_Sql_Select::IGNORE', so it should be unique.
     * 
     * Yes, this is hackery, and perhaps a micro-optimization at that.
     * 
     * @const
     * 
     */
    const IGNORE = '--5a333dc50d9341d8e73e56e2ba591b87';
    
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency sql A Solar_Sql dependency object.
     * 
     * @config int paging Number of rows per page.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Select = array(
        'sql'    => 'sql',
        'paging' => 10,
    );
    
    /**
     * 
     * Data to bind into the query as key => value pairs.
     * 
     * @var array
     * 
     */
    protected $_bind = array();
    
    /**
     * 
     * An array of parts for compound queries.
     * 
     * @var array
     * 
     */
    protected $_compound = array();
    
    /**
     * 
     * The compound phrase ('UNION', 'UNION ALL', etc) to use on the next
     * compound query.
     * 
     * @var array
     * 
     */
    protected $_compound_type = null;
    
    /**
     * 
     * The order to apply to the compound query (if any) as a whole.
     * 
     * @var array
     * 
     */
    protected $_compound_order = array();
    
    /**
     * 
     * The limit to apply to the compound query (if any) as a whole.
     * 
     * @var array
     * 
     */
    protected $_compound_limit = array(
        'count' => 0,
        'offset' => 0,
    );
    
    /**
     * 
     * The component parts of the current select statement.
     * 
     * @var array
     * 
     */
    protected $_parts = array(
        'distinct' => null,
        'cols'     => array(),
        'from'     => array(),
        'join'     => array(),
        'where'    => array(),
        'group'    => array(),
        'having'   => array(),
        'order'    => array(),
        'limit'    => array(
            'count'  => 0,
            'offset' => 0
        ),
    );
    
    /**
     * 
     * The number of rows per page.
     * 
     * @var int
     * 
     */
    protected $_paging = 10;
    
    /**
     * 
     * Column sources, typically "from", "select", and "join".
     * 
     * We use this for automated deconfliction of column names.
     * 
     * @var array
     * 
     */
    protected $_sources = array();
    
    /**
     * 
     * Internal Solar_Sql object.
     * 
     * @var Solar_Sql
     * 
     */
    protected $_sql;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // connect to the database with dependency injection
        $this->_sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // set up defaults
        $this->setPaging($this->_config['paging']);
    }
    
    /**
     * 
     * Returns this object as an SQL statement string.
     * 
     * @return string An SQL statement string.
     * 
     */
    
    public function __toString()
    {
        return $this->fetch('sql');
    }
    
    /**
     * 
     * Sets the number of rows per page.
     * 
     * @param int $rows The number of rows to page at.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function setPaging($rows)
    {
        // force a positive integer
        $rows = (int) $rows;
        if ($rows < 1) {
            $rows = 1;
        }
        $this->_paging = $rows;
        return $this;
    }
    
    /**
     * 
     * Gets the number of rows per page.
     * 
     * @return int The number of rows per page.
     * 
     */
    public function getPaging()
    {
        return $this->_paging;
    }
    
    /**
     * 
     * Makes the query SELECT DISTINCT.
     * 
     * @param bool $flag Whether or not the SELECT is DISTINCT (default
     * true).  If null, the current distinct setting is not changed.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function distinct($flag = true)
    {
        if ($flag !== null) {
            $this->_parts['distinct'] = (bool) $flag;
        }
        return $this;
    }
    
    /**
     * 
     * Adds 1 or more columns to the SELECT, without regard to a FROM or JOIN.
     * 
     * Multiple calls to cols() will append to the list of columns, not
     * overwrite the previous columns.
     * 
     * @param string|array $cols The column(s) to add to the SELECT.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function cols($cols)
    {
        // save in the sources list
        $this->_addSource(
            'cols',
            null,
            null,
            null,
            null,
            $cols
        );
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds a FROM table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Model object, the table
     * to select from; if a string, the table name to select from.
     * 
     * @param array|string $cols The columns to select from this table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function from($spec, $cols = null)
    {
        // get the table name and columns from the specifcation
        list($name, $cols) = $this->_nameCols($spec, $cols);
        
        // convert to an array with keys 'orig' and 'alias'
        $name = $this->_origAlias($name);
        
        // save in the sources list, overwriting previous values
        $this->_addSource(
            'from',
            $name['alias'],
            $name['orig'],
            null,
            null,
            $cols
        );
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds a sub-select and columns to the query.
     * 
     * The format is "FROM ($select) AS $name"; an alias name is
     * always required so we can deconflict columns properly.
     * 
     * @param string|Solar_Sql_Select $spec If a Solar_Sql_Select
     * object, use as the sub-select; if a string, the sub-select
     * command string.
     * 
     * @param string $name The alias name for the sub-select.
     * 
     * @param array|string $cols The columns to retrieve from the 
     * sub-select; by default, '*' (all columns).  This is unlike the
     * normal from() and join() methods, which by default select no
     * columns.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function fromSelect($spec, $name, $cols = '*')
    {
        $spec = $this->_prepareSubSelect($spec);
        
        // save in the sources list, overwriting previous values
        $this->_addSource(
            'select',
            $name,
            $spec,
            null,
            null,
            $cols
        );
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds a JOIN table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Model object, the table
     * to join to; if a string, the table name to join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function join($spec, $cond, $cols = null)
    {
        $this->_join(null, $spec, $cond, $cols);
        return $this;
    }
    
    /**
     * 
     * Adds multiple JOINs to the query.
     * 
     * @param array $list An array of joins, each with keys 'type' (inner, 
     * left, etc), 'name' (the table name), 'cond' (ON conditions), and
     * 'cols' (the columns to retrieve, if any).
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function multiJoin($list)
    {
        $base = array(
            'type' => null,
            'name' => null,
            'cond' => null,
            'cols' => null,
        );
        
        foreach ($list as $join) {
            $join = array_merge($base, (array) $join);
            $this->_join(
                $join['type'],
                $join['name'],
                $join['cond'],
                $join['cols']
            );
        }
        
        return $this;
    }
    
    /**
     * 
     * Adds a LEFT JOIN table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Model object, the table
     * to join to; if a string, the table name to join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function leftJoin($spec, $cond, $cols = null)
    {
        $this->_join('LEFT', $spec, $cond, $cols);
        return $this;
    }
    
    /**
     * 
     * Adds a LEFT JOIN sub-select and columns to the query.
     * 
     * @param string|Solar_Sql_Select $spec If a Solar_Sql_Select
     * object, use as the sub-select; if a string, the sub-select
     * command string.
     * 
     * @param string $name The alias name for the sub-select.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function leftJoinSelect($spec, $name, $cond, $cols = null)
    {
        $spec = $this->_prepareSubSelect($spec);
        $this->_join('LEFT', "($spec) AS $name", $cond, $cols);
        return $this;
    }
    
    /**
     * 
     * Adds an INNER JOIN table and columns to the query.
     * 
     * @param string|object $spec If a Solar_Sql_Model object, the table
     * to join to; if a string, the table name to join to.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function innerJoin($spec, $cond, $cols = null)
    {
        $this->_join('INNER', $spec, $cond, $cols);
        return $this;
    }
    
    /**
     * 
     * Adds an INNER JOIN sub-select and columns to the query.
     * 
     * @param string|Solar_Sql_Select $spec If a Solar_Sql_Select
     * object, use as the sub-select; if a string, the sub-select
     * command string.
     * 
     * @param string $name The alias name for the sub-select.
     * 
     * @param string $cond Join on this condition.
     * 
     * @param array|string $cols The columns to select from the joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function innerJoinSelect($spec, $name, $cond, $cols = null)
    {
        $spec = $this->_prepareSubSelect($spec);
        $this->_join('INNER', "($spec) AS $name", $cond, $cols);
        return $this;
    }
    
    /**
     * 
     * Prepares a select statement for use as a sub-select; returns strings
     * as they are, but converts Solar_Sql_Select objects to strings after
     * merging bind values.
     * 
     * @param string|Solar_Sql_Select $spec The select to prepare as a 
     * sub-select.
     * 
     * @return string
     * 
     */
    protected function _prepareSubSelect($spec)
    {
        if ($spec instanceof self) {
            // merge bound values, otherwise they won't follow through to
            // the sub-select
            if ($spec->_bind) {
                $this->_bind = array_merge($this->_bind, $spec->_bind);
            }
        
            // get the select object as a string.
            return $spec->__toString();
            
        } else {
            return $spec;
        }
    }
    
    /**
     * 
     * Adds a WHERE condition to the query by AND.
     * 
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears.
     * 
     * Array values are quoted and comma-separated.
     * 
     * {{code: php
     *     // simplest but non-secure
     *     $select->where("id = $id");
     *     
     *     // secure
     *     $select->where('id = ?', $id);
     *     
     *     // equivalent security with named binding
     *     $select->where('id = :id');
     *     $select->bind('id', $id);
     * }}
     * 
     * @param string $cond The WHERE condition.
     * 
     * @param string $val A value to quote into the condition.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function where($cond, $val = Solar_Sql_Select::IGNORE)
    {
        if (empty($cond)) {
            return $this;
        }
        
        $cond = $this->_sql->quoteNamesIn($cond);
        
        if ($val !== Solar_Sql_Select::IGNORE) {
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['where']) {
            $this->_parts['where'][] = "AND $cond";
        } else {
            $this->_parts['where'][] = $cond;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds a WHERE condition to the query by OR.
     * 
     * Otherwise identical to where().
     * 
     * @param string $cond The WHERE condition.
     * 
     * @param string $val A value to quote into the condition.
     * 
     * @return Solar_Sql_Select
     * 
     * @see where()
     * 
     */
    public function orWhere($cond, $val = Solar_Sql_Select::IGNORE)
    {
        if (empty($cond)) {
            return $this;
        }
        
        $cond = $this->_sql->quoteNamesIn($cond);
        
        if ($val !== Solar_Sql_Select::IGNORE) {
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['where']) {
            $this->_parts['where'][] = "OR $cond";
        } else {
            $this->_parts['where'][] = $cond;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds multiple WHERE conditions to the query.
     * 
     * @param array $list An array of WHERE conditions.  Conditions starting
     * with "OR" and "AND" are honored correctly.
     * 
     * @param string $op If a condition does not explicitly start with "AND"
     * or "OR", connect the condition with this operator.  Default "AND".
     * 
     * @return Solar_Sql_Select
     * 
     * @see _multiWhere()
     * 
     */
    public function multiWhere($list, $op = 'AND')
    {
        $op = strtoupper(trim($op));
        
        foreach ((array) $list as $key => $val) {
            if (is_int($key)) {
                // integer key means a literal condition
                // and no value to be quoted into it
                $this->_multiWhere($val, Solar_Sql_Select::IGNORE, $op);
            } else {
                // string $key means the key is a condition,
                // and the $val should be quoted into it.
                $this->_multiWhere($key, $val, $op);
            }
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Backend support for multiWhere().
     * 
     * @param string $cond The WHERE condition.
     * 
     * @param mixed $val A value (if any) to quote into the condition.
     * 
     * @param string $op The implicit operator to use for the condition, if
     * needed.
     * 
     * @see where()
     * 
     * @see orWhere()
     * 
     * @return void
     * 
     */
    protected function _multiWhere($cond, $val, $op)
    {
        if (strtoupper(substr($cond, 0, 3)) == 'OR ') {
            // explicit OR
            $cond = substr($cond, 3);
            $this->orWhere($cond, $val);
        } elseif (strtoupper(substr($cond, 0, 4)) == 'AND ') {
            // explicit AND
            $cond = substr($cond, 4);
            $this->where($cond, $val);
        } elseif ($op == 'OR') {
            // implicit OR
            $this->orWhere($cond, $val);
        } else {
            // implicit AND (the default)
            $this->where($cond, $val);
        }
    }
    
    /**
     * 
     * Adds grouping to the query.
     * 
     * @param string|array $spec The column(s) to group by.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function group($spec)
    {
        if (empty($spec)) {
            return $this;
        }
        
        if (is_string($spec)) {
            $spec = explode(',', $spec);
        } else {
            settype($spec, 'array');
        }
        
        $spec = $this->_sql->quoteName($spec);
        
        $this->_parts['group'] = array_merge($this->_parts['group'], $spec);
        return $this;
    }
    
    /**
     * 
     * Adds a HAVING condition to the query by AND.
     * 
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears.
     * 
     * Array values are quoted and comma-separated.
     * 
     * {{code: php
     *     // simplest but non-secure
     *     $select->having("COUNT(id) = $count");
     *     
     *     // secure
     *     $select->having('COUNT(id) = ?', $count);
     *     
     *     // equivalent security with named binding
     *     $select->having('COUNT(id) = :count');
     *     $select->bind('count', $count);
     * }}
     * 
     * @param string $cond The HAVING condition.
     * 
     * @param string $val A value to quote into the condition.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function having($cond, $val = Solar_Sql_Select::IGNORE)
    {
        if (empty($cond)) {
            return $this;
        }
        
        $cond = $this->_sql->quoteNamesIn($cond);
        
        if ($val !== Solar_Sql_Select::IGNORE) {
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        if ($this->_parts['having']) {
            $this->_parts['having'][] = "AND $cond";
        } else {
            $this->_parts['having'][] = $cond;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds a HAVING condition to the query by OR.
     * 
     * Otherwise identical to orHaving().
     * 
     * @param string $cond The HAVING condition.
     * 
     * @param string $val A value to quote into the condition.
     * 
     * @return Solar_Sql_Select
     * 
     * @see having()
     * 
     */
    public function orHaving($cond, $val = Solar_Sql_Select::IGNORE)
    {
        if (empty($cond)) {
            return $this;
        }
        
        if ($val !== Solar_Sql_Select::IGNORE) {
            $cond = $this->_sql->quoteInto($cond, $val);
        }
        
        $cond = $this->_sql->quoteNamesIn($cond);
        
        if ($this->_parts['having']) {
            $this->_parts['having'][] = "OR $cond";
        } else {
            $this->_parts['having'][] = $cond;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Adds multiple HAVING conditions to the query.
     * 
     * @param array $list An array of HAVING conditions.  Conditions starting
     * with "OR" and "AND" are honored correctly.
     * 
     * @param string $op If a condition does not explicitly start with "AND"
     * or "OR", connect the condition with this operator.  Default "AND".
     * 
     * @return Solar_Sql_Select
     * 
     * @see _multiHaving()
     * 
     */
    public function multiHaving($list, $op = 'AND')
    {
        $op = strtoupper(trim($op));
        
        foreach ((array) $list as $key => $val) {
            if (is_int($key)) {
                // integer key means a literal condition
                // and no value to be quoted into it
                $this->_multiHaving($val, Solar_Sql_Select::IGNORE, $op);
            } else {
                // string $key means the key is a condition,
                // and the $val should be quoted into it.
                $this->_multiHaving($key, $val, $op);
            }
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Backend support for multiHaving().
     * 
     * @param string $cond The HAVING condition.
     * 
     * @param mixed $val A value (if any) to quote into the condition.
     * 
     * @param string $op The implicit operator to use for the condition, if
     * needed.
     * 
     * @see having()
     * 
     * @see orHaving()
     * 
     * @return void
     * 
     */
    protected function _multiHaving($cond, $val, $op)
    {
        if (strtoupper(substr($cond, 0, 3)) == 'OR ') {
            // explicit OR
            $cond = substr($cond, 3);
            $this->orHaving($cond, $val);
        } elseif (strtoupper(substr($cond, 0, 4)) == 'AND ') {
            // explicit AND
            $cond = substr($cond, 4);
            $this->having($cond, $val);
        } elseif ($op == 'OR') {
            // implicit OR
            $this->orHaving($cond, $val);
        } else {
            // implicit AND (the default)
            $this->having($cond, $val);
        }
    }
    
    /**
     * 
     * Adds a row order to the query.
     * 
     * @param string|array $spec The column(s) and direction to order by.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function order($spec)
    {
        if (empty($spec)) {
            return $this;
        }
        
        if (is_string($spec)) {
            $spec = explode(',', $spec);
        } else {
            settype($spec, 'array');
        }
        
        $spec = $this->_sql->quoteNamesIn($spec);
        
        // force 'ASC' or 'DESC' on each order spec, default is ASC.
        foreach ($spec as $key => $val) {
            $asc  = (strtoupper(substr($val, -4)) == ' ASC');
            $desc = (strtoupper(substr($val, -5)) == ' DESC');
            if (! $asc && ! $desc) {
                $spec[$key] .= ' ASC';
            }
        }
        
        // merge them into the current order set
        $this->_parts['order'] = array_merge($this->_parts['order'], $spec);
        
        // done
        return $this;
    }
    
    /**
     * 
     * Sets a limit count and offset to the query.
     * 
     * @param int $count The number of rows to return.
     * 
     * @param int $offset Start returning after this many rows.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function limit($count = null, $offset = null)
    {
        $this->_parts['limit']['count']  = (int) $count;
        $this->_parts['limit']['offset'] = (int) $offset;
        return $this;
    }
    
    /**
     * 
     * Sets the limit and count by page number.
     * 
     * @param int $page Limit results to this page number.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function limitPage($page = null)
    {
        // reset the count and offset
        $this->_parts['limit']['count']  = 0;
        $this->_parts['limit']['offset'] = 0;
        
        // determine the count and offset from the page number
        $page = (int) $page;
        if ($page > 0) {
            $this->_parts['limit']['count']  = $this->_paging;
            $this->_parts['limit']['offset'] = $this->_paging * ($page - 1);
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Clears query properties and row sources.
     * 
     * @param string $part The property to clear; if empty, clears all
     * query properties.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function clear($part = null)
    {
        if (empty($part)) {
            $this->_clearParts();
            $this->_clearCompound();
            return $this;
        }
        
        $part = strtolower($part);
        switch ($part) {
        
        case 'compound':
            $this->_clearCompound();
            break;
        
        case 'distinct':
            $this->_parts['distinct'] = false;
            break;
        
        case 'from':
            $this->_parts['from'] = array();
            foreach ($this->_sources as $skey => $sval) {
                if ($sval['type'] == 'from' || $sval['type'] == 'select') {
                    unset($this->_sources[$skey]);
                }
            }
            break;
        
        case 'join':
            $this->_parts['join'] = array();
            foreach ($this->_sources as $skey => $sval) {
                if ($sval['type'] == 'join') {
                    unset($this->_sources[$skey]);
                }
            }
            break;
        
        case 'limit':
            $this->_parts['limit'] = array(
                'count'  => 0,
                'offset' => 0
            );
            break;
        
        case 'cols':
            $this->_parts['cols'] = array();
            foreach ($this->_sources as $skey => $sval) {
                $this->_sources[$skey]['cols'] = array();
            }
            break;
        
        case 'where':
        case 'group':
        case 'having':
        case 'order':
            $this->_parts[$part] = array();
            break;
        
        default:
            throw $this->_exception('ERR_UNKNOWN_PART', array(
                'part' => $part,
            ));
            break;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Clears only the current select properties and row sources, not 
     * compound elements.
     * 
     * @return void
     * 
     */
    protected function _clearParts()
    {
        // clear all parts
        $this->_parts = array(
            'distinct' => false,
            'cols'     => array(),
            'from'     => array(),
            'join'     => array(),
            'where'    => array(),
            'group'    => array(),
            'having'   => array(),
            'order'    => array(),
            'limit'    => array(
                'count'  => 0,
                'offset' => 0
            ),
        );
        
        // clear all table/join sources
        $this->_sources = array();
    }
    
    /**
     * 
     * Clears only the compound elements, not the current select properties 
     * and row sources.
     * 
     * @return void
     * 
     */
    protected function _clearCompound()
    {
        $this->_compound = array();
        $this->_compound_type = null;
        $this->_compound_order = array();
        $this->_compound_limit = array(
            'count'  => 0,
            'offset' => 0,
        );
    }
    
    /**
     * 
     * Adds data to bind into the query.
     * 
     * @param mixed $key The replacement key in the query.  If this is an
     * array or object, the $val parameter is ignored, and all the
     * key-value pairs in the array (or all properties of the object) are
     * added to the bind.
     * 
     * @param mixed $val The value to use for the replacement key.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function bind($key, $val = null)
    {
        if (is_array($key)) {
            $this->_bind = array_merge($this->_bind, $key);
        } elseif (is_object($key)) {
            $this->_bind = array_merge((array) $this->_bind, $key);
        } elseif (! empty($key)) {
            $this->_bind[$key] = $val;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Unsets bound data.
     * 
     * @param mixed $spec The key to unset.  If a string, unsets that one
     * bound value; if an array, unsets the list of values; if empty, unsets
     * all bound values (the default).
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function unbind($spec = null)
    {
        if (empty($spec)) {
            $this->_bind = array();
        } else {
            settype($spec, 'array');
            foreach ($spec as $key) {
                unset($this->_bind[$key]);
            }
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Takes the current select properties and prepares them for UNION with
     * the next set of select properties.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function union()
    {
        $this->_addCompound('UNION');
        return $this;
    }
    
    /**
     * 
     * Takes the current select properties and prepares them for UNION ALL
     * with the next set of select properties.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function unionAll()
    {
        $this->_addCompound('UNION ALL');
        return $this;
    }
    
    /**
     * 
     * Support method for adding compound ('UNION', 'UNION ALL') queries based
     * on the current object properties.
     * 
     * @param string $type The compound phrase.
     * 
     * @return Solar_Sql_Select
     * 
     */
    protected function _addCompound($type)
    {
        // build the current parts with the *previous* compound type
        $this->_compound[] = array(
            'type' => $this->_compound_type,
            'spec' => $this->_build($this->_parts, $this->_sources),
        );
        
        // retain the type for the *next* compound
        $this->_compound_type = strtoupper($type);
        
        // clear parts for the next compound
        $this->_clearParts();
    }
    
    /**
     * 
     * Adds a *compound* row order to the query; used only in UNION (etc)
     * queries.
     * 
     * @param string|array $spec The column(s) and direction to order by.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function compoundOrder($spec)
    {
        if (empty($spec)) {
            return $this;
        }
        
        if (is_string($spec)) {
            $spec = explode(',', $spec);
        } else {
            settype($spec, 'array');
        }
        
        $spec = $this->_sql->quoteNamesIn($spec);
        
        // force 'ASC' or 'DESC' on each order spec, default is ASC.
        foreach ($spec as $key => $val) {
            $asc  = (strtoupper(substr($val, -4)) == ' ASC');
            $desc = (strtoupper(substr($val, -5)) == ' DESC');
            if (! $asc && ! $desc) {
                $spec[$key] .= ' ASC';
            }
        }
        
        // merge them into the current order set
        $this->_compound_order = array_merge($this->_compound_order, $spec);
        
        // done
        return $this;
    }
    
    /**
     * 
     * Sets a *compound* limit count and offset to the query; used only in UNION (etc)
     * queries.
     * 
     * @param int $count The number of rows to return.
     * 
     * @param int $offset Start returning after this many rows.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function compoundLimit($count = null, $offset = null)
    {
        $this->_compound_limit['count']  = (int) $count;
        $this->_compound_limit['offset'] = (int) $offset;
        return $this;
    }
    
    /**
     * 
     * Sets the *compound* limit and count by page number; used only in UNION (etc)
     * queries.
     * 
     * @param int $page Limit results to this page number.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function compoundLimitPage($page = null)
    {
        // reset the count and offset
        $this->_compound_limit['count']  = 0;
        $this->_compound_limit['offset'] = 0;
        
        // determine the count and offset from the page number
        $page = (int) $page;
        if ($page > 0) {
            $this->_compound_limit['count']  = $this->_paging;
            $this->_compound_limit['offset'] = $this->_paging * ($page - 1);
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Fetch the results based on the current query properties.
     * 
     * @param string $type The type of fetch to perform (all, one, col,
     * etc).  Default is 'pdo'.
     * 
     * @return mixed The query results.
     * 
     */
    public function fetch($type = 'pdo')
    {
        // does the fetch-method exist? (this allows for extended
        // adapters  to define their own fetch methods)
        $fetch = 'fetch' . ucfirst($type);
        if (! method_exists($this->_sql, $fetch)) {
            throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
                'method' => $fetch
            ));
        }
        
        // is this a compound select?
        if ($this->_compound) {
            
            // build the parts for a compound select
            $parts = array(
                'compound' => $this->_compound,
                'order'    => $this->_compound_order,
                'limit'    => $this->_compound_limit,
            );
            
            // add the current parts as the last compound
            $parts['compound'][] = array(
                'type' => $this->_compound_type,
                'spec' => $this->_build($this->_parts, $this->_sources),
            );
            
        } else {
            
            // build the parts for a single select
            $parts = $this->_build($this->_parts, $this->_sources);
            
        }
        
        // return the fetch result
        return $this->_sql->$fetch($parts, $this->_bind);
    }
    
    /**
     * 
     * Support method for building corrected parts from sources.
     * 
     * @param array $parts An array of SELECT parts.
     * 
     * @param array $sources An array of sources for the SELECT.
     * 
     * @return array An array of corrected SELECT parts.
     * 
     */
    protected function _build($parts, $sources)
    {
        // build from scratch using the table and row sources.
        $parts['cols'] = array();
        $parts['from'] = array();
        $parts['join'] = array();
        
        // get a count of how many sources there are. if there's only 1, we
        // won't use column-name prefixes below. this will help soothe SQLite
        // on JOINs of sub-selects.
        // 
        // e.g., `JOIN (SELECT alias.col FROM tbl AS alias) ...`  won't work
        // right, SQLite needs `JOIN (SELECT col AS col FROM tbl AS alias)`.
        // 
        $count_sources = count($sources);
        
        // build from sources.
        foreach ($sources as $source) {
            
            // build the from and join parts.  note that we don't
            // build from 'cols' sources, since they are just named
            // columns without reference to a particular from or join.
            if ($source['type'] != 'cols') {
                $method = "_build" . ucfirst($source['type']);
                $this->$method(
                    $parts,
                    $source['name'],
                    $source['orig'],
                    $source['join'],
                    $source['cond']
                );
            }
            
            // determine a prefix for the columns from this source
            if ($source['type'] == 'select' ||
                $source['name'] != $source['orig']) {
                // use the alias name, not the original name,
                // and where aliases are explicitly named.
                $prefix = $source['name'];
            } else {
                // use the original name
                $prefix = $source['orig'];
            }
            
            // add each of the columns from the source, deconflicting
            // along the way.
            foreach ($source['cols'] as $col) {
                
                // does it use a function?  we don't care if it's the first
                // char, since a paren in the first position means there's no
                // function name before it.
                $parens = strpos($col, '(');
                
                // choose our column-name deconfliction strategy.
                // catches any existing AS in the name.
                if ($parens) {
                    // if there are parens in the name, it's a function.
                    $tmp = $this->_sql->quoteNamesIn($col);
                } elseif ($prefix == '' || $count_sources == 1) {
                    // if no prefix, that's a no-brainer.
                    // if there's only one source, deconfliction not needed.
                    $tmp = $this->_sql->quoteName($col);
                } else {
                    // auto deconfliction.
                    $tmp = $this->_sql->quoteName("$prefix.$col");
                }
                
                // force an "AS" if not already there, but only if the source
                // is not a manually-set column name, and the column is not a
                // literal star for all columns.
                if ($source['type'] != 'cols' && $col != '*') {
                    // force an AS if not already there. this is because
                    // sqlite returns col names as '"table"."col"' when there
                    // are 2 or more joins. so let's just standardize on
                    // always doing it.
                    // 
                    //  make sure there's no parens, or we get a bad col name
                    $pos = stripos($col, ' AS ');
                    if ($pos === false && ! $parens) {
                        $tmp .= " AS " . $this->_sql->quoteName($col);
                    }
                }
                
                // add to the parts
                $parts['cols'][] = $tmp;
            }
        }
        
        // done!
        return $parts;
    }
    
    /**
     * 
     * Fetches all rows from the database using sequential keys.
     * 
     * @return array
     * 
     */
    public function fetchAll()
    {
        return $this->fetch('all');
    }
    
    /**
     * 
     * Fetches all rows from the database using associative keys (defined by
     * the first column).
     * 
     * N.b.: if multiple rows have the same first column value, the last
     * row with that value will override earlier rows.
     * 
     * @return array
     * 
     */
    public function fetchAssoc()
    {
        return $this->fetch('assoc');
    }
    
    /**
     * 
     * Fetches the first column of all rows as a sequential array.
     * 
     * @return array
     * 
     */
    public function fetchCol()
    {
        return $this->fetch('col');
    }
    
    /**
     * 
     * Fetches the very first value (i.e., first column of the first row).
     * 
     * @return mixed
     * 
     */
    public function fetchValue()
    {
        return $this->fetch('value');
    }
    
    /**
     * 
     * Fetches an associative array of all rows as key-value pairs (first 
     * column is the key, second column is the value).
     * 
     * @return array
     * 
     */
    public function fetchPairs()
    {
        return $this->fetch('pairs');
    }
    
    /**
     * 
     * Fetches a PDOStatement result object.
     * 
     * @return PDOStatement
     * 
     */
    public function fetchPdo()
    {
        return $this->fetch('pdo');
    }
    
    /**
     * 
     * Fetches one row from the database.
     * 
     * @return array
     * 
     */
    public function fetchOne()
    {
        return $this->fetch('one');
    }
    
    /**
     * 
     * Builds the SQL statement and returns it as a string instead of 
     * executing it.  Useful for debugging.
     * 
     * @return string
     * 
     */
    public function fetchSql()
    {
        return $this->fetch('sql');
    }
    
    /**
     * 
     * Get the count of rows and number of pages for the current query.
     * 
     * @param string $col The column to COUNT() on.  Default is 'id'.
     * 
     * @return array An associative array with keys 'count' (the total number
     * of rows) and 'pages' (the number of pages based on $this->_paging).
     * 
     */
    public function countPages($col = 'id')
    {
        // prepare a copy of this object as a COUNT query
        $select = clone($this);
        
        // no limit, and no need to order rows
        $select->clear('limit');
        $select->clear('order');
        
        // clear all columns so there are no name conflicts
        foreach ($select->_sources as $key => $val) {
            $select->_sources[$key]['cols'] = array();
        }
        
        // look for a GROUP setting
        $has_grouping = (bool) $select->_parts['group'];
        
        // look in the WHERE and HAVING clauses for a `COUNT` condition
        $has_count_cond = $this->_hasCountCond($select->_parts['where']) ||
                          $this->_hasCountCond($select->_parts['having']);
        
        // is there a grouping or a count condition?
        if ($has_grouping || $has_count_cond) {
            
            // count on a sub-select instead.
            $count = $this->_countSubSelect($select, $col);
            
        } else {
            
            // track distinctness
            if ($select->_parts['distinct']) {
                $distinct = 'DISTINCT ';
                $select->distinct(false);
            } else {
                $distinct = '';
            }
        
            // "normal" case (no grouping, and no count condition in WHERE or
            // HAVING).  add the one column we're counting on...
            $select->_addSource(
                'cols',         // type
                null,           // name
                null,           // orig
                null,           // join
                null,           // cond
                "COUNT($distinct$col)"
            );
            
            // ... and do the count.
            $count = $select->fetchValue();
        }
        
        // calculate pages
        $pages = 0;
        if ($count > 0) {
            $pages = ceil($count / $this->_paging);
        }
        
        // done!
        return array(
            'count' => $count,
            'pages' => $pages,
        );
    }
    
    /**
     * 
     * Determines if there is a COUNT() in any of the condition snippets.
     * 
     * @param array $list The list of condition snippets.
     * 
     * @return bool True if a COUNT() is present anywhere, false if not.
     * 
     */
    protected function _hasCountCond($list)
    {
        foreach ($list as $key => $val) {
            if (is_int($key)) {
                // val is a literal condition
                $cond = strtoupper($val);
            } else {
                // key is a condition with a placeholder,
                // and val is the placeholder value.
                $cond = strtoupper($key);
            }
            // does the condition have COUNT in it?
            if (strpos($cond, 'COUNT') !== false) {
                return true;
            }
        }
        // no COUNT condition found
        return false;
    }
    
    /**
     * 
     * When doing a countPages(), count using a subselect.
     * 
     * @param Solar_Sql_Select $inner The inner subselect to use.
     * 
     * @param string $col Count on this column in the subselect.
     * 
     * @return int The count on the subselect column.
     * 
     */
    protected function _countSubSelect($inner, $col)
    {
        // add the one column we're counting on, to the inner subselect
        $inner->_addSource(
            'cols',         // type
            null,           // name
            null,           // orig
            null,           // join
            null,           // cond
            $col
        );
        
        // does the counting column have a dot in it?
        $pos = strpos($col, '.');
        if ($pos) {
            // alias the subselect to the same table name as the column
            $alias = substr($col, 0, $pos);
            $col   = substr($col, $pos + 1);
        } else {
            // default alias 'subselect' in lieu of an explicit alias
            $alias = 'subselect';
        }
        
        // quote the column name directly, as it won't have the alias on it
        $col = $this->_sql->quoteName($col);
        
        // track distinctness
        if ($inner->_parts['distinct']) {
            $distinct = 'DISTINCT ';
            $inner->distinct(false);
        } else {
            $distinct = '';
        }
        
        // build the outer select, which will do the actual count.
        // wrapping with an outer select lets us have all manner of weirdness
        // in the inner query, so that it doesn't conflict with the count.
        // don't alias the column itself, to soothe sqlite.
        $outer = clone($this);
        $outer->clear();
        $outer->fromSelect($inner, $alias, "COUNT($distinct$col)");
        
        // get the count
        return $outer->fetchValue();
    }
    
    /**
     * 
     * Safely quotes a value for an SQL statement.
     * 
     * @param mixed $val The value to quote.
     * 
     * @return string An SQL-safe quoted value (or a string of 
     * separated-and-quoted values).
     * 
     * @see Solar_Sql_Adapter::quote()
     * 
     */
    public function quote($val)
    {
        return $this->_sql->quote($val);
    }
    
    /**
     * 
     * Quotes a value and places into a piece of text at a placeholder.
     * 
     * @param string $txt The text with a placeholder.
     * 
     * @param mixed $val The value to quote.
     * 
     * @return mixed An SQL-safe quoted value (or string of separated values)
     * placed into the orignal text.
     * 
     * @see Solar_Sql_Adapter::quoteInto()
     * 
     */
    public function quoteInto($txt, $val)
    {
        return $this->_sql->quoteInto($txt, $val);
    }
    
    /**
     * 
     * Quote multiple text-and-value pieces.
     * 
     * @param array $list A series of key-value pairs where the key is
     * the placeholder text and the value is the value to be quoted into
     * it.  If the key is an integer, it is assumed that the value is
     * piece of literal text to be used and not quoted.
     * 
     * @param string $sep Return the list pieces separated with this string
     * (for example ' AND '), default null.
     * 
     * @return string An SQL-safe string composed of the list keys and
     * quoted values.
     * 
     * @see Solar_Sql_Adapter::quoteMulti()
     * 
     */
    public function quoteMulti($list, $sep = null)
    {
        return $this->_sql->quoteMulti($list, $sep);
    }
    
    // -----------------------------------------------------------------
    // 
    // Protected support functions
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns an identifier as an "original" name and an "alias".
     * 
     * Effectively splits the identifier at "AS", so that "foo AS bar"
     * becomes array('orig' => 'foo', 'alias' => 'bar').
     * 
     * @param string $name The string identifier.
     * 
     * @return array The $name string as an array with keys 'name' and
     * 'alias'.
     * 
     */
    protected function _origAlias($name)
    {
        // does the name have an "AS" alias? pick the right-most one near the
        // end of the string (note the "rr" in strripos).
        $pos = strripos($name, ' AS ');
        if ($pos !== false) {
            return array(
                'orig'  => trim(substr($name, 0, $pos)),
                'alias' => trim(substr($name, $pos + 4)),
            );
        } else {
            return array(
                'orig'  => trim($name),
                'alias' => trim($name),
            );
        }
    }
    
    /**
     * 
     * Support method for adding JOIN clauses.
     * 
     * @param string $type The type of join; empty for a plain JOIN, or
     * "LEFT", "INNER", etc.
     * 
     * @param string|Solar_Sql_Model $spec If a Solar_Sql_Model
     * object, the table to join to; if a string, the table name to
     * join to.
     * 
     * @param string|array $cond Condiiton(s) for the ON clause.
     * 
     * @param array|string $cols The columns to select from the
     * joined table.
     * 
     * @return Solar_Sql_Select
     * 
     */
    protected function _join($type, $spec, $cond, $cols)
    {
        // Add support for an array based $cond parameter
        if (is_array($cond)) {
            $on = array();
            foreach ((array) $cond as $key => $val) {
                if (is_int($key)) {
                    // integer key means a literal condition
                    // and no value to be quoted into it
                    $on[] = $val;
                } else {
                    // string $key means the key is a condition,
                    // and the $val should be quoted into it.
                    $on[] = $this->quoteInto($key, $val);
                }
            }
            $cond = implode($on, ' AND ');
        }
        
        // get the table name and columns from the specifcation
        list($name, $cols) = $this->_nameCols($spec, $cols);
        
        // convert to an array of orig and alias
        $name = $this->_origAlias($name);
        
        // save in the sources list, overwriting previous values
        $this->_addSource(
            'join',
            $name['alias'],
            $name['orig'],
            strtoupper($type),
            $cond,
            $cols
        );
        
        return $this;
    }
    
    /**
     * 
     * Support method for finding a table name and column names.
     * 
     * @param string|Solar_Sql_Model $spec The specification for the table
     * name; if a model object, returns the $table_name property.
     * 
     * @param string|array $cols The columns to use from the table; if '*' and
     * the $spec is a model object, returns an array of all columns for the
     * model's table.
     * 
     * @return array A sequential array where element 0 is the table name, and
     * element 1 is the table columns.
     * 
     */
    protected function _nameCols($spec, $cols)
    {
        // the $spec may be a model object, or a string
        if ($spec instanceof Solar_Sql_Model) {
            
            // get the table name
            $name = $spec->table_name;
            
            // add all columns?
            if ($cols == '*') {
                $cols = array_keys($spec->table_cols);
            }
            
        } else {
            $name = $spec;
        }
        
        return array($name, $cols);
    }
    
    /**
     * 
     * Adds a row source (from table, from select, or join) to the 
     * sources array.
     * 
     * @param string $type The source type: 'from', 'join', or 'select'.
     * 
     * @param string $name The alias name.
     * 
     * @param string $orig The source origin, either a table name or a 
     * sub-select statement.
     * 
     * @param string $join If $type is 'join', the type of join ('left',
     * 'inner', or null for a regular join).
     * 
     * @param string $cond If $type is 'join', the join conditions.
     * 
     * @param array $cols The columns to select from the source.
     * 
     * @return void
     * 
     */
    protected function _addSource($type, $name, $orig, $join, $cond, $cols)
    {
        if (is_string($cols)) {
            $cols = explode(',', $cols);
        }
        
        if ($cols) {
            settype($cols, 'array');
            foreach ($cols as $key => $val) {
                $cols[$key] = trim($val);
            }
        } else {
            $cols = array();
        }
        
        if ($type == 'cols') {
            $this->_sources[] = array(
                'type' => $type,
                'name' => $name,
                'orig' => $orig,
                'join' => $join,
                'cond' => $cond,
                'cols' => $cols,
            );
        } else {
            $this->_sources[$name] = array(
                'type' => $type,
                'name' => $name,
                'orig' => $orig,
                'join' => $join,
                'cond' => $cond,
                'cols' => $cols,
            );
        }
    }
    
    /**
     * 
     * Builds a part element **in place** using a 'from' source.
     * 
     * @param array &$parts The SELECT parts to build with.
     * 
     * @param string $name The table alias.
     * 
     * @param string $orig The original table name.
     * 
     * @return void
     * 
     */
    protected function _buildFrom(&$parts, $name, $orig)
    {
        if ($name == $orig) {
            $parts['from'][] = $this->_sql->quoteName($name);
        } else {
            $parts['from'][] = $this->_sql->quoteName($orig)
                             . ' '
                             . $this->_sql->quoteName($name);
        }
    }
    
    /**
     * 
     * Builds a part element **in place** using a 'join' source.
     * 
     * @param array &$parts The SELECT parts to build with.
     * 
     * @param string $name The table alias.
     * 
     * @param string $orig The original table name.
     * 
     * @param string $join The join type (null, 'left', 'inner', etc).
     * 
     * @param string $cond Join conditions.
     * 
     * @return void
     * 
     */
    protected function _buildJoin(&$parts, $name, $orig, $join, $cond)
    {
        $tmp = array(
            'type' => $join,
            'name' => null,
            'cond' => $this->_sql->quoteNamesIn($cond),
        );
        
        if ($name == $orig) {
            $tmp['name'] = $this->_sql->quoteName($name);
        } elseif ($orig[0] == '(') {
            $tmp['name'] = $orig
                         . ' '
                         . $this->_sql->quoteName($name);
        } else {
            $tmp['name'] = $this->_sql->quoteName($orig)
                         . ' '
                         . $this->_sql->quoteName($name);
        }
        
        $parts['join'][] = $tmp;
    }
    
    /**
     * 
     * Builds a part element **in place** using a 'select' source.
     * 
     * @param array &$parts The SELECT parts to build with.
     * 
     * @param string $name The subselect alias.
     * 
     * @param string $orig The subselect command string.
     * 
     * @return void
     * 
     */
    protected function _buildSelect(&$parts, $name, $orig)
    {
        $parts['from'][] = "($orig) " . $this->_sql->quoteName($name);
    }
}
