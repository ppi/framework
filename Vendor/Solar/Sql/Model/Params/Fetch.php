<?php
/**
 * 
 * A value-object to represent the various parameters for specifying a model
 * fetch() call.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Fetch.php 4498 2010-03-05 17:28:00Z pmjones $
 * 
 */
class Solar_Sql_Model_Params_Fetch extends Solar_Sql_Model_Params { 
    
    /**
     * 
     * Default data array.
     * 
     * @var array
     * 
     */
    protected $_data = array(
        'distinct'      => null,
        'cols'          => array(),
        'join'          => array(),
        'where'         => array(),
        'group'         => array(),
        'having'        => array(),
        'order'         => array(),
        'limit'         => array(),
        'paging'        => null,
        'page'          => null,
        'bind'          => array(),
        'count_pages'   => null,
        'cache'         => null,
        'alias'         => null,
        'cache_key'     => null,
        'eager'         => array(),
    );
    
    /**
     * 
     * Should the fetch use a SELECT DISTINCT?
     * 
     * @param bool $flag True to use DISTINCT; false, not to.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    public function distinct($flag)
    {
        $this->_data['distinct'] = (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * Adds a single arbitrary JOIN to the fetch.
     * 
     * For example, to left join to a table 'bar' on 'bar.id' and select
     * columns dib, zim, and gir from it:
     * 
     * {{code:php
     *     $fetch->join(array(
     *         'type' => 'left',
     *         'name' => 'bar',
     *         'cond' => 'bar.id = foo.id',
     *         'cols' => array('dib', 'zim', 'gir'),
     *     ));
     * }}
     * 
     * The type, if left null, defaults to an inner join; cols, if left
     * null, will select no columns.
     * 
     * @param array $spec An array with keys `type`, `name`, `cond`, and `cols`.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     * @see Solar_Sql_Select::multiJoin()
     * 
     */
    public function join($spec)
    {
        if (! is_int(key($spec))) {
            $spec = array($spec);
        }
        
        $base = array(
            'type' => null,
            'name' => null,
            'cond' => null,
            'cols' => null,
            'keep' => null,
        );
        
        foreach ($spec as $join) {
            $join = array_merge($base, $join);
        
            $this->_data['join'][] = array(
                'type' => $join['type'],
                'name' => $join['name'],
                'cond' => $join['cond'],
                'cols' => $join['cols'],
                'keep' => $join['keep'],
            );
        }
        
        return $this;
    }
    
    /**
     * 
     * Adds a WHERE condition to the fetch, optionally with a value to bind
     * to the condition.
     * 
     * @param string $cond The WHERE condition.
     * 
     * @param string $val A value to quote into the condition, replacing
     * question-mark placeholders.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     * @see Solar_Sql_Select::where()
     * 
     */
    public function where($cond, $val = Solar_Sql_Select::IGNORE)
    {
        // BC-helping logic
        if (is_int($cond) && is_string($val)) {
            $cond = $val;
            $val = Solar_Sql_Select::IGNORE;
        }
        
        // now the real logic. use triple-equals so that empties are honored.
        if ($val === Solar_Sql_Select::IGNORE) {
            $this->_data['where'][] = $cond;
        } else {
            $this->_data['where'][$cond] = $val;
        }
        return $this;
    }
    
    /**
     * 
     * Adds GROUP BY columns to the fetch.
     * 
     * @param array $list The columns to add to the GROUP BY clause.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     * @see Solar_Sql_Select::group()
     * 
     */
    public function group($list)
    {
        $list = array_merge(
            (array) $this->_data['group'],
            (array) $list
        );
        
        $this->_data['group'] = array_unique($list);
        return $this;
    }
    
    /**
     * 
     * Adds a HAVING condition to the fetch, optionally with a value to bind
     * to the condition.
     * 
     * @param string $cond The HAVING condition.
     * 
     * @param string $val A value to quote into the condition, replacing
     * question-mark placeholders.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     * @see Solar_Sql_Select::having()
     * 
     */
    public function having($cond, $val = Solar_Sql_Select::IGNORE)
    {
        // BC-helping logic
        if (is_int($cond) && is_string($val)) {
            $cond = $val;
            $val = Solar_Sql_Select::IGNORE;
        }
        
        // now the real logic. use triple-equals so that empties are honored.
        if ($val === Solar_Sql_Select::IGNORE) {
            $this->_data['having'][] = $cond;
        } else {
            $this->_data['having'][$cond] = $val;
        }
        return $this;
    }
    
    /**
     * 
     * Adds ORDER BY columns to the fetch.
     * 
     * @param array $list The columns to add to the ORDER BY clause.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     * @see Solar_Sql_Select::order()
     * 
     */
    public function order($list)
    {
        $list = array_merge(
            (array) $this->_data['order'],
            (array) $list
        );
        
        $this->_data['order'] = array_unique($list);
        return $this;
    }
    
    /**
     * 
     * Sets a LIMIT COUNT and OFFSET on the fetch.
     * 
     * @param int|array $spec A LIMIT COUNT value if an int; or, if an array,
     * the first element is the LIMIT COUNT value, and the second is the
     * LIMIT OFFSET value.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    public function limit($spec)
    {
        if (! $spec) {
            $this->_data['limit'] = null;
        } else {
            $spec = array_pad((array) $spec, 2, null);
            $this->_data['limit'][0] = (int) $spec[0];
            $this->_data['limit'][1] = (int) $spec[1];
        }
        return $this;
    }
    
    /**
     * 
     * Sets the number of rows-per-page on the fetch.
     * 
     * @param int $val The number of rows per page.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    public function paging($val)
    {
        $this->_data['paging'] = (int) $val;
        return $this;
    }
    
    /**
     * 
     * Sets which page number of records the fetch should return.
     * 
     * @param int $val The page number to return.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    public function page($val)
    {
        $this->_data['page'] = (int) $val;
        return $this;
    }
    
    /**
     * 
     * Adds named-placeholder values to bind to the resulting fetch query.
     * 
     * @param array $list An array of key-value pairs where the key is the
     * placeholder name, and the value is the placeholder value.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    public function bind($list)
    {
        $this->_data['bind'] = array_merge(
            (array) $this->_data['bind'],
            (array) $list
        );
        
        return $this;
    }
    
    /**
     * 
     * Should the fetch issue a followup query to count the total number of
     * records and pages?
     * 
     * @param bool $flag True to issue the followup "count pages" query,
     * false not to.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    public function countPages($flag)
    {
        $this->_data['count_pages'] = (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * Returns a clone with only the joins we keep for native selects (i.e., 
     * for page counts and for the native-by-select strategy in relateds).
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    public function cloneForKeeps()
    {
        $clone = clone($this);
        
        // drop all eagers so they don't get re-added
        $clone->_data['eager'] = array();
        
        // drop joins not needed for keeps
        foreach ($clone->_data['join'] as $key => $join) {
            $keep = $join['keep'] === true
                 || $join['keep'] === null && $join['type'] != 'left';
            if (! $keep) {
                unset($clone->_data['join'][$key]);
            }
        }
        
        // done
        return $clone;
    }
    
    /**
     * 
     * Should the fetch attempt use cached results when available?
     * 
     * @param bool $flag True to use the cache, false to avoid it.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    public function cache($flag)
    {
        $this->_data['cache'] = ($flag === null) ? null : (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * When fetching from and saving to the cache, what key should be used?
     * 
     * @param string $val The cache key to use.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    public function cacheKey($val)
    {
        $this->_data['cache_key'] = ($val === null) ? null: (string) $val;
        return $this;
    }
    
    /**
     * 
     * Returns the cache key being used for this fetch.
     * 
     * @return string
     * 
     */
    public function getCacheKey()
    {
        if ($this->_data['cache_key']) {
            return $this->_data['cache_key'];
        } else {
            return hash('md5', serialize($this->_data));
        }
    }
    
    /**
     * 
     * Returns the cache key being used for "count pages" on this fetch.
     * 
     * @return string
     * 
     */
    public function getCacheKeyForCount()
    {
        return $this->getCacheKey() . ':__count__';
    }
    
    /**
     * 
     * Loads this params object with an array of data using support methods.
     * 
     * @param array $data The data to load.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     * @see _loadOne()
     * 
     * @see _loadTwo()
     * 
     */
    protected function _load($data)
    {
        parent::_load($data);
        
        $this->_loadOne($data, array(
            'cache',
            'cache_key' => 'cacheKey',
            'count_pages' => 'countPages',
            'distinct',
            'group',
            'order',
            'page',
            'paging',
        ));
        
        $this->_loadTwo($data, array(
            'having',
            'where',
        ));
        
        // limit() is a special case
        if (! empty($data['limit'])) {
            $this->limit($data['limit']);
        }
        
        // join() is a special case
        if (! empty($data['join'])) {
            $this->join($data['join']);
        }
        
        // bind() is a special case too
        if (! empty($data['bind'])) {
            $this->bind($data['bind']);
        }
    }
}
