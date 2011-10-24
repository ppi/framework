<?php
/**
 * 
 * An SQL-centric Model class based on TableDataGateway, using Collection and
 * Record objects for returns, with integrated caching of versioned result
 * data.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model An SQL-oriented ORM system using TableDataGateway 
 * and DataMapper patterns.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Jeff Moore <jeff@procata.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Model.php 4694 2010-09-06 14:33:18Z pmjones $
 * 
 */
abstract class Solar_Sql_Model extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency sql A Solar_Sql dependency.
     * 
     * @config dependency cache A Solar_Cache dependency for the 
     * Solar_Sql_Model_Cache object.
     * 
     * @config dependency catalog A Solar_Sql_Model_Catalog to find other 
     * models with.
     * 
     * @config bool table_scan Connect to the database and scan the table for 
     * its column descriptions, creating the table and indexes if not already 
     * present.
     * 
     * @config bool auto_cache Automatically maintain the data cache.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Model = array(
        'catalog' => 'model_catalog',
        'sql'   => 'sql',
        'cache' => array(
            'adapter' => 'Solar_Cache_Adapter_None',
        ),
        'table_scan' => true,
        'auto_cache' => false,
    );
    
    /**
     * 
     * The number of rows affected by the last INSERT, UPDATE, or DELETE.
     * 
     * @var int
     * 
     * @see getAffectedRows()
     * 
     */
    protected $_affected_rows;
    
    /**
     * 
     * A Solar_Sql_Model_Catalog dependency object.
     * 
     * @var Solar_Sql_Model_Catalog
     * 
     */
    protected $_catalog = null;
    
    /**
     * 
     * A Solar_Sql dependency object.
     * 
     * @var Solar_Sql_Adapter
     * 
     */
    protected $_sql = null;
    
    /**
     * 
     * A Solar_Sql_Model_Cache object.
     * 
     * @var Solar_Sql_Model_Cache
     * 
     */
    protected $_cache = null;
    
    /**
     * 
     * The model name is the short form of the class name; this is generally
     * a plural.
     * 
     * When inheritance is enabled, the default is the $_inherit_name value,
     * otherwise, the default is the $_table_name.
     * 
     * @var string
     * 
     */
    protected $_model_name;
    
    /**
     * 
     * When a record from this model is part of an form element array, use
     * this name as the array key for it; by default, this is the singular
     * of the model name.
     * 
     * @var string
     * 
     */
    protected $_array_name;
    
    // -----------------------------------------------------------------
    //
    // Classes
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * A Solar_Class_Stack object for fallback hierarchy.
     * 
     * @var Solar_Class_Stack
     * 
     */
    protected $_stack;
    
    /**
     * 
     * The results of get_class($this) so we don't call get_class() all the 
     * time.
     * 
     * @var string
     * 
     */
    protected $_class;
    
    /**
     * 
     * The final fallback class for an individual record.
     * 
     * Default is Solar_Sql_Model_Record.
     * 
     * @var string
     * 
     */
    protected $_record_class = 'Solar_Sql_Model_Record';
    
    /**
     * 
     * A blank instance of the Record class for this model.
     * 
     * We keep this so we don't keep looking for a record class once we know
     * what the proper class is.  Not used when inheritance is in effect.
     * 
     * @var Solar_Sql_Model_Record
     * 
     */
    protected $_record_prototype;
    
    /**
     * 
     * The final fallback class for collections of records.
     * 
     * Default is Solar_Sql_Model_Collection.
     * 
     * @var string
     * 
     */
    protected $_collection_class = 'Solar_Sql_Model_Collection';
    
    /**
     * 
     * A blank instance of the Collection class for this model.
     * 
     * We keep this so we don't keep looking for a collection class once we
     * know what the proper class is.
     * 
     * @var Solar_Sql_Model_Record
     * 
     */
    protected $_collection_prototype;
    
    /**
     * 
     * The class to use for building SELECT statements.
     * 
     * @var string
     * 
     */
    protected $_select_class = 'Solar_Sql_Select';
    
    /**
     * 
     * The class to use for filter chains.
     * 
     * @var string
     * 
     */
    protected $_filter_class = null;
    
    /**
     * 
     * The class to use for the cache object.
     * 
     * @var string
     * 
     * @see $_cache
     * 
     */
    protected $_cache_class = 'Solar_Sql_Model_Cache';
    
    // -----------------------------------------------------------------
    //
    // Table and index definition
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * The table name.
     * 
     * @var string
     * 
     */
    protected $_table_name = null;
    
    /**
     * 
     * The column specification array for all columns in this table.
     * 
     * Used in auto-creation, and for sync-checks.
     * 
     * Will be overridden by _fixTableCols() when it reads the table info, so 
     * you don't *have* to enter anything here ... but if it's empty, you 
     * won't get auto-creation.
     * 
     * Each element in this array looks like this...
     * 
     * {{code: php
     *     $_table_cols = array(
     *         'col_name' => array(
     *             'name'    => (string) the col_name, same as the key
     *             'type'    => (string) char, varchar, date, etc
     *             'size'    => (int) column size
     *             'scope'   => (int) decimal places
     *             'default' => (string) default value
     *             'require' => (bool) is this a required (non-null) column?
     *             'primary' => (bool) is this part of the primary key?
     *             'autoinc' => (bool) auto-incremented?
     *          ),
     *     );
     * }}
     * 
     * @var array
     * 
     */
    protected $_table_cols = array();
    
    /**
     * 
     * The index specification array for all indexes on this table.
     * 
     * Used only in auto-creation.
     * 
     * The array should be in this format ...
     * 
     * {{code: php
     *     // the index type: 'normal' or 'unique'
     *     $type = 'normal';
     * 
     *     // index on a single column:
     *     // CREATE INDEX idx_name ON table_name (col_name)
     *     $this->_index_info['idx_name'] = array(
     *         'type' => $type,
     *         'cols' => 'col_name'
     *     );
     * 
     *     // index on multiple columns:
     *     // CREATE INDEX idx_name ON table_name (col_1, col_2, ... col_N)
     *     $this->_index_info['idx_name'] = array(
     *         'type' => $type,
     *         'cols' => array('col_1', 'col_2', ..., 'col_N')
     *     );
     * 
     *     // easy shorthand for an index on a single column,
     *     // giving the index the same name as the column:
     *     // CREATE INDEX col_name ON table_name (col_name)
     *     $this->_index_info['col_name'] = $type;
     * }}
     * 
     * The $type may be 'normal' or 'unique'.
     * 
     * @var array
     * 
     */
    protected $_index_info = array();
    
    // -----------------------------------------------------------------
    //
    // Special columns and column behaviors
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * A list of column names that don't exist in the table, but should be
     * calculated by the model as-needed.
     * 
     * @var array
     * 
     */
    protected $_calculate_cols = array();
    
    /**
     * 
     * A list of column names that use sequence values.
     * 
     * When the column is present in a data array, but its value is null,
     * a sequence value will automatically be added.
     * 
     * @var array
     * 
     */
    protected $_sequence_cols = array();
    
    /**
     * 
     * A list of column names on which to apply serialize() and unserialize()
     * automatically.
     * 
     * Will be unserialized by the Record class as the values are loaded,
     * then re-serialized just before insert/update in the Model class.
     * 
     * @var array
     * 
     * @see [[php::serialize() | ]]
     * 
     * @see [[php::unserialize() | ]]
     * 
     */
    protected $_serialize_cols = array();
    
    /**
     * 
     * A list of column names storing XML strings to convert back and forth to
     * Solar_Struct_Xml objects.
     * 
     * @var array
     * 
     * @see $_xmlstruct_class
     * 
     * @see Solar_Struct_Xml
     * 
     */
    protected $_xmlstruct_cols = array();
    
    /**
     * 
     * The class to use for $_xmlstruct_cols conversion objects.
     * 
     * @var string
     * 
     * @var array
     * 
     * @see $_xmlstruct_cols
     * 
     */
    protected $_xmlstruct_class = 'Solar_Struct_Xml';
    
    /**
     * 
     * The column name for the primary key.
     * 
     * @var string
     * 
     */
    protected $_primary_col = null;
    
    /**
     * 
     * The column name for 'created' timestamps; default is 'created'.
     * 
     * @var string
     * 
     */
    protected $_created_col = 'created';
    
    /**
     * 
     * The column name for 'updated' timestamps; default is 'updated'.
     * 
     * @var string
     * 
     */
    protected $_updated_col = 'updated';
    
    /**
     * 
     * Other models that relate to this model should use this as the 
     * foreign-key column name.
     * 
     * @var string
     * 
     */
    protected $_foreign_col = null;
    
    // -----------------------------------------------------------------
    //
    // Other/misc
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Relationships to other Model classes.
     * 
     * Keyed on a "virtual" column name, which will be used as a property
     * name in returned records.
     * 
     * @var array
     * 
     */
    protected $_related = array();
    
    /**
     * 
     * Filters to validate and sanitize column data.
     * 
     * Default is to use validate*() and sanitize*() methods in the filter
     * class, but if the method exists locally, it will be used instead.
     * 
     * The filters apply only to Record objects from the model; if you use
     * the model insert() and update() methods directly, the filters are not
     * applied.
     * 
     * Example usage follows; note that "_validate" and "_sanitize" refer
     * to internal (protected) filtering methods that have access to the
     * entire data set being filtered.
     * 
     * {{code: php
     *     // filter 'col_1' to have only alpha chars, with a max length of
     *     // 32 chars
     *     $this->_filters['col_1'][] = 'sanitizeStringAlpha';
     *     $this->_filters['col_1'][] = array('validateMaxLength', 32);
     * 
     *     // filter 'col_2' to have only numeric chars, validate as an
     *     // integer, in a range of -10 to +10.
     *     $this->_filters['col_2'][] = 'sanitizeNumeric';
     *     $this->_filters['col_2'][] = 'validateInteger';
     *     $this->_filters['col_2'][] = array('validateRange', -10, +10);
     * 
     *     // filter 'handle' to have only alpha-numeric chars, with a length
     *     // of 6-14 chars, and unique in the table.
     *     $this->_filters['handle'][] = 'sanitizeStringAlnum';
     *     $this->_filters['handle'][] = array('validateRangeLength', 6, 14);
     *     $this->_filters['handle'][] = 'validateUnique';
     * 
     *     // filter 'email' to have only emails-allowed chars, validate as an
     *     // email address, and be unique in the table.
     *     $this->_filters['email'][] = 'sanitizeStringEmail';
     *     $this->_filters['email'][] = 'validateEmail';
     *     $this->_filters['email'][] = 'validateUnique';
     * 
     *     // filter 'passwd' to be not-blank, and should match any existing
     *     // 'passwd_confirm' value.
     *     $this->_filters['passwd'][] = 'validateNotBlank';
     *     $this->_filters['passwd'][] = 'validateConfirm';
     * }}
     * 
     * @var array
     * 
     * @see $_filter_class
     * 
     * @see _addFilter()
     * 
     */
    protected $_filters;
    
    // -----------------------------------------------------------------
    //
    // Single-table inheritance
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * The base model this class is inherited from, in single-table 
     * inheritance.
     * 
     * @var string
     * 
     */
    protected $_inherit_base = null;
    
    /**
     * 
     * When inheritance is turned on, the class name value for this class
     * in $_inherit_col.
     * 
     * @var string
     * 
     */
    protected $_inherit_name = false;
    
    /**
     * 
     * The column name that tracks single-table inheritance; default is
     * 'inherit'.
     * 
     * @var string
     * 
     */
    protected $_inherit_col = 'inherit';
    
    // -----------------------------------------------------------------
    //
    // Select options
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Only fetch these columns from the table.
     * 
     * @var array
     * 
     */
    protected $_fetch_cols;
    
    /**
     * 
     * By default, order by this column when fetching rows.
     * 
     * @var array
     * 
     */
    protected $_order;
    
    /**
     * 
     * The default number of rows per page when selecting.
     * 
     * @var int
     * 
     */
    protected $_paging = 10;
    
    /**
     * 
     * The registered Solar_Inflect object.
     * 
     * @var Solar_Inflect
     * 
     */
    protected $_inflect;
    
    // -----------------------------------------------------------------
    //
    // Constructor and magic methods
    //
    // -----------------------------------------------------------------
    
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
        
        // Establish the state of this object before _setup
        $this->_preSetup();        
        
        // user-defined setup
        $this->_setup();
        
        // Complete the setup of this model
        $this->_postSetup();
    }
    
    /**
     * 
     * Call this before you unset the instance so that you release the memory
     * from all the internal child objects.
     * 
     * @return void
     * 
     */
    public function free()
    {
        foreach ($this->_related as $key => $val) {
            unset($this->_related[$key]);
        }
        
        unset($this->_cache);
    }
    
    /**
     * 
     * User-defined setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    // -----------------------------------------------------------------
    //
    // Getters and setters
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Read-only access to protected model properties.
     * 
     * @param string $key The requested property; e.g., `'foo'` will read from
     * `$_foo`.
     * 
     * @return mixed
     * 
     */
    public function __get($key)
    {
        $var = "_$key";
        if (property_exists($this, $var)) {
            return $this->$var;
        } else {
            throw $this->_exception('ERR_NO_SUCH_PROPERTY', array(
                'class' => get_class($this),
                'property' => $key,
            ));
        }
    }
    
    /**
     * 
     * Gets the number of records per page.
     * 
     * @return int The number of records per page.
     * 
     */
    public function getPaging()
    {
        return $this->_paging;
    }
    
    /**
     * 
     * Sets the number of records per page.
     * 
     * @param int $paging The number of records per page.
     * 
     * @return void
     * 
     */
    public function setPaging($paging)
    {
        $this->_paging = (int) $paging;
    }
    
    /**
     * 
     * Returns the fully-qualified primary key name.
     * 
     * @return string
     * 
     */
    public function getPrimary()
    {
        return "{$this->_model_name}.{$this->_primary_col}";
    }
    
    /**
     * 
     * Returns the number of rows affected by the last INSERT, UPDATE, or
     * DELETE.
     * 
     * @return int
     * 
     */
    public function getAffectedRows()
    {
        return $this->_affected_rows;
    }
    
    // -----------------------------------------------------------------
    //
    // Fetch
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Magic call implements "fetchOneBy...()" and "fetchAllBy...()" for
     * columns listed in the method name.
     * 
     * You *have* to specify params for all of the named columns.
     * 
     * Optionally, you can pass a final array for the "extra" paramters to the
     * fetch ('order', 'group', 'having', etc.)
     * 
     * Example:
     * 
     * {{code: php
     *     // fetches one record by status
     *     $model->fetchOneByStatus('draft');
     * 
     *     // fetches all records by area_id and owner_handle
     *     $model->fetchAllByAreaIdAndOwnerHandle($area_id, $owner_handle);
     * 
     *     // fetches all records by area_id and owner_handle,
     *     // with ordering and page-limiting
     *     $extra = array('order' => 'area_id DESC', 'page' => 2);
     *     $model->fetchAllByAreaIdAndOwnerHandle($area_id, $owner_handle, $extra);
     * }}
     * 
     * @param string $method The virtual method name, composed of "fetchOneBy"
     * or "fetchAllBy", with a series of column names joined by "And".
     * 
     * @param array $params Parameters to pass to the method: one for each
     * column, plus an optional one for extra fetch parameters.
     * 
     * @return mixed
     * 
     * @todo Expand to cover assoc, col, pairs, and value.
     * 
     */
    public function __call($method, $params)
    {
        // fetch a record, or a collection?
        if (substr($method, 0, 7) == 'fetchBy') {
            // fetch a record
            $fetch = 'fetchOne';
            $method = substr($method, 7);
        } elseif (substr($method, 0, 10) == 'fetchOneBy') {
            // fetch a record
            $fetch = 'fetchOne';
            $method = substr($method, 10);
        } elseif (substr($method, 0, 10) == 'fetchAllBy') {
            // fetch a collection
            $fetch = 'fetchAll';
            $method = substr($method, 10);
        } else {
            throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
                'method' => $method,
            ));
        }
        
        // get the list of columns from the remainder of the method name
        // e.g., fetchAllByParentIdAndAreaId => ParentId, AreaId
        $list = explode('And', $method);
        
        // build the fetch params
        $where = array();
        foreach ($list as $key => $col) {
            // convert from ColName to col_name
            $col = strtolower(
                $this->_inflect->camelToUnder($col)
            );
            $where["{$this->_model_name}.$col = ?"] = $params[$key];
        }
        
        // add the last param after last column name as the "extra" settings
        // (order, group, having, page, paging, etc).
        $k = count($list);
        if (count($params) > $k) {
            $opts = (array) $params[$k];
        } else {
            $opts = array();
        }
        
        // merge the where with the base fetch params
        $opts = array_merge($opts, array(
            'where' => $where,
        ));
        
        // do the fetch
        return $this->$fetch($opts);
    }
    
    /**
     * 
     * Fetches a record or collection by primary key value(s).
     * 
     * @param int|array $spec The primary key value for a single record, or an
     * array of primary key values for a collection of records.
     * 
     * @param array $fetch An array of parameters for the fetch, with keys
     * for 'cols', 'group', 'having', 'order', etc.  Note that the 'where'
     * and 'order' elements are overridden and have no effect.
     * 
     * @return Solar_Sql_Model_Record|Solar_Sql_Model_Collection A record or
     * record-set object.
     * 
     */
    public function fetch($spec, $fetch = null)
    {
        $col = "{$this->_model_name}.{$this->_primary_col}";
        if (is_array($spec)) {
            $fetch['where'] = array("$col IN (?)" => $spec);
            $fetch['order'] = $col;
            return $this->fetchAll($fetch);
        } else {
            $fetch['where'] = array("$col = ?" => $spec);
            $fetch['order'] = $col;
            return $this->fetchOne($fetch);
        }
    }
    
    /**
     * 
     * Fetches a collection of all records by arbitrary parameters.
     * 
     * @param array|Solar_Sql_Model_Params_Fetch $fetch Parameters for the
     * fetch.
     * 
     * @return Solar_Sql_Model_Collection A collection object.
     * 
     */
    public function fetchAll($fetch = null)
    {
        // fetch the result array and select object
        $fetch = $this->_fixFetchParams($fetch);
        list($result, $select) = $this->_fetchResultSelect('all', $fetch);
        if (! $result) {
            return array();
        }
        
        // create a collection from the result
        $coll = $this->newCollection($result);
        
        // add pager-info to the collection
        if ($fetch['count_pages']) {
            $this->_setCollectionPagerInfo($coll, $fetch);
        }
        
        // done
        return $coll;
    }
    
    /**
     * 
     * Fetches an array of rows by arbitrary parameters.
     * 
     * @param array|Solar_Sql_Model_Params_Fetch $fetch Parameters for the
     * fetch.
     * 
     * @return array
     * 
     */
    public function fetchAllAsArray($fetch = null)
    {
        // fetch the result array and select object
        $fetch = $this->_fixFetchParams($fetch);
        list($result, $select) = $this->_fetchResultSelect('all', $fetch);
        if (! $result) {
            return array();
        } else {
            return $result;
        }
    }
    
    /**
     * 
     * The same as fetchAll(), except the record collection is keyed on the
     * first column of the results (instead of being a strictly sequential
     * array.)
     * 
     * @param array|Solar_Sql_Model_Params_Fetch $fetch Parameters for the
     * fetch.
     * 
     * @return Solar_Sql_Model_Collection A collection object.
     * 
     */
    public function fetchAssoc($fetch = null)
    {
        // fetch the result array and select object
        $fetch = $this->_fixFetchParams($fetch);
        list($result, $select) = $this->_fetchResultSelect('assoc', $fetch);
        if (! $result) {
            return array();
        }
        
        // create a collection from the result
        $coll = $this->newCollection($result);
        
        // add pager-info to the collection
        if ($fetch['count_pages']) {
            $this->_setCollectionPagerInfo($coll, $fetch);
        }
        
        // done
        return $coll;
    }
    
    /**
     * 
     * The same as fetchAssoc(), except it returns an array, not a collection.
     * 
     * @param array|Solar_Sql_Model_Params_Fetch $fetch Parameters for the
     * fetch.
     * 
     * @return array An array of rows.
     * 
     */
    public function fetchAssocAsArray($fetch = null)
    {
        // fetch the result array and select object
        $fetch = $this->_fixFetchParams($fetch);
        list($result, $select) = $this->_fetchResultSelect('assoc', $fetch);
        if (! $result) {
            return array();
        } else {
            return $result;
        }
    }
    
    /**
     * 
     * Sets the pager info in a collection, calling countPages() along the
     * way.
     * 
     * @param Solar_Sql_Model_Collection $coll The record collection to set
     * pager info on.
     * 
     * @param array $fetch The params for the original fetchAll() or
     * fetchAssoc().
     * 
     * @return void
     */
    protected function _setCollectionPagerInfo($coll, $fetch)
    {
        $total = $this->countPages($fetch);
        
        $info = array(
            'count'  => (int) $total['count'],
            'pages'  => (int) $total['pages'],
            'paging' => (int) $fetch['paging'],
        );
        
        if (! $info['count']) {
            $info['page']  = 0;
            $info['begin'] = 0;
            $info['end']   = 0;
        } elseif (! $fetch['page']) {
            $info['page']  = 1;
            $info['begin'] = 1;
            $info['end']   = $info['count'];
        } else {
            $start         = (int) ($fetch['page'] - 1) * $fetch['paging'];
            $info['page']  = $fetch['page'];
            $info['begin'] = $start + 1;
            $info['end']   = $start + $info['count'];
        }
        
        $info['is_first'] = (bool) ($info['page'] == 1);
        $info['is_last']  = (bool) ($info['end'] == $info['count']);
        
        $coll->setPagerInfo($info);
    }
    
    /**
     * 
     * Fetches one record by arbitrary parameters.
     * 
     * @param array|Solar_Sql_Model_Params_Fetch $fetch Parameters for the
     * fetch.
     * 
     * @return Solar_Sql_Model_Record A record object.
     * 
     */
    public function fetchOne($fetch = null)
    {
        // fetch the result array and select object
        $fetch = $this->_fixFetchParams($fetch);
        list($result, $select) = $this->_fetchResultSelect('one', $fetch);
        if (! $result) {
            return null;
        }
        
        // get the main record, which sets the to-one data
        $record = $this->newRecord($result);
        
        // done
        return $record;
    }
    
    /**
     * 
     * The same as fetchOne(), but returns an array instead of a record object.
     * 
     * @param array|Solar_Sql_Model_Params_Fetch $fetch Parameters for the
     * fetch.
     * 
     * @return array
     * 
     */
    public function fetchOneAsArray($fetch = null)
    {
        // fetch the result array and select object
        $fetch = $this->_fixFetchParams($fetch);
        list($result, $select) = $this->_fetchResultSelect('one', $fetch);
        if (! $result) {
            return array();
        } else {
            return $result;
        }
    }
    
    /**
     * 
     * Fetches a sequential array of values from the model, using only the
     * first column of the results.
     * 
     * @param array|Solar_Sql_Model_Params_Fetch $fetch Parameters for the
     * fetch.
     * 
     * @return array
     * 
     */
    public function fetchCol($fetch = null)
    {
        // fetch the result array and select object
        $fetch = $this->_fixFetchParams($fetch);
        list($result, $select) = $this->_fetchResultSelect('col', $fetch);
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }
    
    /**
     * 
     * Fetches an array of key-value pairs from the model, where the first
     * column is the key and the second column is the value.
     * 
     * @param array|Solar_Sql_Model_Params_Fetch $fetch Parameters for the
     * fetch.
     * 
     * @return array
     * 
     */
    public function fetchPairs($fetch = null)
    {
        // fetch the result array and select object
        $fetch = $this->_fixFetchParams($fetch);
        list($result, $select) = $this->_fetchResultSelect('pairs', $fetch);
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }
    
    /**
     * 
     * Fetches a single value from the model (i.e., the first column of the 
     * first record of the returned page set).
     * 
     * @param array|Solar_Sql_Model_Params_Fetch $fetch Parameters for the
     * fetch.
     * 
     * @return mixed The single value from the model query, or null.
     * 
     */
    public function fetchValue($fetch = null)
    {
        // fetch the result array and select object
        $fetch = $this->_fixFetchParams($fetch);
        list($result, $select) = $this->_fetchResultSelect('value', $fetch);
        return $result;
    }
    
    /**
     * 
     * Returns a data result and the select used to fetch the data.
     * 
     * If caching is turned on, this will fetch from the cache (if available)
     * and save the result back to the cache (if needed).
     * 
     * @param string $type The type of fetch to perform: 'all', 'one', etc.
     * 
     * @param Solar_Sql_Model_Params_Fetch $fetch The params for the fetch.
     * 
     * @return array An array of two elements; element 0 is the result data,
     * element 1 is the Solar_Sql_Select object used to fetch the data.
     * 
     */
    protected function _fetchResultSelect($type, Solar_Sql_Model_Params_Fetch $fetch)
    {
        $select = $this->newSelect($fetch);
        
        // attempt to fetch from cache?
        if ($fetch['cache']) {
            $key = $this->_cache->entry($fetch);
            $result = $this->_cache->fetch($key);
            if ($result !== false) {
                // found some data!
                return array($result, $select);
            }
        }
        
        // attempt to fetch from database
        $result = $select->fetch($type);
        
        // now process the results through the eagers
        foreach ($fetch['eager'] as $name => $eager) {
            $related = $this->getRelated($name);
            $related->modEagerResult($eager, $result, $type, $fetch);
        }
        
        // add to cache?
        if ($fetch['cache']) {
            $this->_cache->add($key, $result);
        }
        
        // done
        return array($result, $select);
    }
    
    /**
     * 
     * Returns a new record with default values.
     * 
     * @param array $spec An array of user-specified data to place into the
     * new record, if any.
     * 
     * @return Solar_Sql_Model_Record A record object.
     * 
     */
    public function fetchNew($spec = null)
    {
        $record = $this->_newRecord();
        $data   = $this->_fetchNewData($spec);
        $record->initNew($this, $data);
        return $record;
    }
    
    /**
     * 
     * Support method to generate the data for a new, blank record.
     * 
     * @param array $spec An array of user-specified data to place into the
     * new record, if any.
     * 
     * @return array An array of data for loading into a a new, blank record.
     * 
     */
    protected function _fetchNewData($spec = null)
    {
        // the user-specifed data
        settype($spec, 'array');
        
        // the array of data for the record
        $data = array();
        
        // loop through each table column and collect default data
        foreach ($this->_table_cols as $key => $val) {
            if (array_key_exists($key, $spec)) {
                // user-specified
                $data[$key] = $spec[$key];
            } else {
                // default value
                $data[$key] = $val['default'];
            }
        }
        
        // loop through each calculate column and collect default data
        foreach ($this->_calculate_cols as $key => $val) {
            if (array_key_exists($key, $spec)) {
                // user-specified
                $data[$key] = $spec[$key];
            } else {
                // default value
                $data[$key] = $val['default'];
            }
        }
        
        // add Solar_Xml_Struct objects
        foreach ($this->_xmlstruct_cols as $key) {
            $data[$key] = Solar::factory($this->_xmlstruct_class);
        }
        
        // if we have inheritance, set that too
        if ($this->_inherit_name) {
            $key = $this->_inherit_col;
            $data[$key] = $this->_inherit_name;
        }
        
        // done
        return $data;
    }
    
    /**
     * 
     * Fetches count and pages of available records.
     * 
     * @param array $fetch An array of clauses for the SELECT COUNT()
     * statement, including 'where', 'group, and 'having'.
     * 
     * @return array An array with keys 'count' and 'pages'; 'count' is the
     * number of records, 'pages' is the number of pages.
     * 
     */
    public function countPages($fetch = null)
    {
        // fix up the parameters
        $fetch = $this->_fixFetchParams($fetch);
        
        // add a fake param called 'count' to make this different from the
        // orginating query (for cache deconfliction).
        $fetch['__count__'] = true;
        
        // check the cache
        if ($fetch['cache']) {
            $key = $this->_cache->entry($fetch);
            $result = $this->_cache->fetch($key);
            if ($result !== false) {
                // cache hit
                return $result;
            }
        }
        
        // clone the fetch for only the "keep" joins
        $clone = $fetch->cloneForKeeps();
        
        // get the base select
        $select = $this->newSelect($clone);
        
        // count on the primary column
        $col = "{$this->_model_name}.{$this->_primary_col}";
        $result = $select->countPages($col);
        
        // save in cache?
        if ($fetch['cache']) {
            $this->_cache->add($key, $result);
        }
        
        // done
        return $result;
    }
    
    // -----------------------------------------------------------------
    //
    // Select
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Converts and cleans-up fetch params from arrays to instances of
     * Solar_Sql_Model_Params_Fetch.
     * 
     * @param array $spec The parameters for the fetch.
     * 
     * @return Solar_Sql_Model_Params_Fetch
     * 
     */
    protected function _fixFetchParams($spec)
    {
        if ($spec instanceof Solar_Sql_Model_Params_Fetch) {
            // already a params object, pre-empt further modification
            return $spec;
        }
        
        // baseline object
        $fetch = Solar::factory('Solar_Sql_Model_Params_Fetch');
        
        // defaults
        $fetch->load(array(
            'cache'  => $this->_config['auto_cache'],
            'paging' => $this->_paging,
            'alias'  => $this->_model_name,
        ));
        
        // user specification
        $fetch->load($spec);
        
        // add columns if none already specified
        if (! $fetch['cols']) {
            $fetch->cols($this->_fetch_cols);
        }
        
        // done
        return $fetch;
    }
    
    /**
     * 
     * Returns a WHERE clause array of conditions to use when fetching
     * from this model; e.g., single-table inheritance.
     * 
     * @param array $where The WHERE array being modified.
     * 
     * @param string $alias The current name of the table for this model
     * in the query being constructed; defaults to the model name.
     *
     * @return array The modified WHERE array.
     *
     */   
    public function getConditions($alias = null)
    {
        // default to the model name for the alias
        if (! $alias) {
            $alias = $this->_model_name;
        }
        
        // the array of where clauses
        $where = array();
        
        // is inheritance on?
        if ($this->isInherit()) {
            $key = "{$alias}.{$this->_inherit_col} = ?";
            $val = $this->_inherit_name;
            $where = array($key => $val);
        }
        
        // done!
        return $where;
    }
    
    /**
     * 
     * Returns a new Solar_Sql_Select tool, with the proper SQL object
     * injected automatically.
     * 
     * @param Solar_Sql_Model_Params_Fetch|array $fetch Parameters for the
     * fetch.
     * 
     * @return Solar_Sql_Select
     * 
     */
    public function newSelect($fetch = null)
    {
        $fetch = $this->_fixFetchParams($fetch);
        
        if (! $fetch['alias']) {
            $fetch->alias($this->_model_name);
        }
        
        foreach ($fetch['eager'] as $name => $eager) {
            $related = $this->getRelated($name);
            $related->modEagerFetch($eager, $fetch);
        }
        
        $use_default_order = ! $fetch['order'] && $fetch['order'] !== false;
        if ($use_default_order && $this->_order) {
            $fetch->order("{$fetch['alias']}.{$this->_order}");
        };
        
        // get the select object
        $select = Solar::factory(
            $this->_select_class,
            array('sql' => $this->_sql)
        );
        
        // add the explicitly asked-for columns before the eager-join cols.
        // this is to make sure the fetchPairs() method works right, because
        // adding the eager columns first will mess that up.
        $select->from(
            "{$this->_table_name} AS {$fetch['alias']}",
            $fetch['cols']
        );
        
        $select->multiWhere($this->getConditions($fetch['alias']));
        
        // all the other pieces
        $select->distinct($fetch['distinct'])
               ->multiJoin($fetch['join'])
               ->multiWhere($fetch['where'])
               ->group($fetch['group'])
               ->multiHaving($fetch['having'])
               ->order($fetch['order'])
               ->setPaging($fetch['paging'])
               ->bind($fetch['bind']);
        
        // limit by count/offset, or by page?
        if ($fetch['limit']) {
            list($count, $offset) = $fetch['limit'];
            $select->limit($count, $offset);
        } else {
            $select->limitPage($fetch['page']);
        }
        
        // done!
        return $select;
    }
    
    // -----------------------------------------------------------------
    //
    // Record and Collection factories
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns the appropriate record object, honoring inheritance.
     * 
     * @param array $data The data to load into the record.
     * 
     * @return Solar_Sql_Model_Record A record object.
     * 
     */
    public function newRecord($data)
    {
        // the record to return, eventually
        $record = null;
        
        // look for an inheritance in relation to $data
        $inherit = null;
        if ($this->_inherit_col && ! empty($data[$this->_inherit_col])) {
            // inheritance is available, and a value is set for the
            // inheritance column in the data
            $inherit = trim($data[$this->_inherit_col]);
        }
        
        // did we find an inheritance value?
        if ($inherit) {
            // try to find a model class based on inheritance, going up the
            // stack as needed. this checks for Current_Model_Type,
            // Parent_Model_Type, Grandparent_Model_Type, etc.
            // 
            // blow up if we can't find it, since this is explicitly noted
            // as the inheritance class.
            $inherit_class = $this->_catalog->getClass($inherit);
            
            // if different from the current class, reset the model object.
            if ($inherit_class != $this->_class) {
                // use the inherited model class, it's different from the
                // current model. if it's not different, fall through, leaving
                // $record == null.  that will invoke the logic below.
                $model = $this->_catalog->getModelByClass($inherit_class);
                $record = $model->newRecord($data);
            }
        }
        
        // do we have a record yet?
        if (! $record) {
            // no, because an inheritance model was not specified, or was of 
            // the same class as this class.
            $record = $this->_newRecord();
            $record->init($this, $data);
        }
        
        return $record;
    }
    
    /**
     * 
     * Returns a new record object for this model only.
     * 
     * @return Solar_Sql_Model_Record A record object.
     * 
     */
    protected function _newRecord()
    {
        if (empty($this->_record_prototype)) {
            // find the record class
            $record_class = $this->_stack->load('Record', false);
            if (! $record_class) {
                // use the default record class
                $record_class = $this->_record_class;
            }
            $this->_record_prototype = Solar::factory($record_class);
        }
        $record = clone $this->_record_prototype;
        return $record;
    }    
    
    /**
     * 
     * Returns the appropriate collection object for this model.
     * 
     * @param array $data The data to load into the collection, if any.
     * 
     * @return Solar_Sql_Model_Collection A collection object.
     * 
     */
    public function newCollection($data = null)
    {
        $collection = $this->_newCollection();
        $collection->setModel($this);
        $collection->load($data);
        return $collection;
    }
    
    /**
     * 
     * Returns a new collection object for this model only.
     * 
     * @return Solar_Sql_Model_Collection A collection object.
     * 
     */
    protected function _newCollection()
    {
        if (empty($this->_collection_prototype)) {
            // find the collection class
            $collection_class = $this->_stack->load('Collection', false);
            if (! $collection_class) {
                // use the default collection class
                $collection_class = $this->_collection_class;
            }
            $this->_collection_prototype = Solar::factory($collection_class);
        }
        $collection = clone $this->_collection_prototype;
        return $collection;
    }
    
    // -----------------------------------------------------------------
    //
    // Insert, update, or delete rows in the model.
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Inserts one row to the model table and deletes cache entries.
     * 
     * @param array $data The row data to insert.
     * 
     * @return int|bool On success, the last inserted ID if there is an
     * auto-increment column on the model (otherwise boolean true). On failure
     * an exception from PDO bubbles up.
     * 
     * @throws Solar_Sql_Exception on failure of any sort.
     * 
     * @see Solar_Sql_Model_Cache::deleteAll()
     * 
     */
    public function insert($data)
    {
        if (! is_array($data)) {
            throw $this->_exception('ERR_DATA_NOT_ARRAY', array(
                'method' => 'insert',
            ));
        }
        
        // reset affected rows
        $this->_affected_rows;
        
        // remove non-existent table columns from the data
        foreach ($data as $key => $val) {
            if (empty($this->_table_cols[$key])) {
                unset($data[$key]);
                // not in the table, so no need to check for autoinc
                continue;
            }
            
            // remove empty autoinc columns to soothe postgres, which won't
            // take explicit NULLs in SERIAL cols.
            if ($this->_table_cols[$key]['autoinc'] && empty($val)) {
                unset($data[$key]);
            }
        }
        
        // perform the insert and track affected rows
        $this->_affected_rows = $this->_sql->insert(
            $this->_table_name,
            $data
        );
                
        // does the table have an autoincrement column?
        $autoinc = null;
        foreach ($this->_table_cols as $name => $info) {
            if ($info['autoinc']) {
                $autoinc = $name;
                break;
            }
        }
        
        // return the last insert id, or just "true" ?
        if ($autoinc) {
            $id = $this->_sql->lastInsertId($this->_table_name, $autoinc);
        } 

        // clear the cache for this model and related models
        $this->_cache->deleteAll();

        if ($autoinc) {
            return $id;
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Updates rows in the model table and deletes cache entries.
     * 
     * @param array $data The row data to insert.
     * 
     * @param string|array $where The WHERE clause to identify which rows to 
     * update.
     * 
     * @return int The number of rows affected.
     * 
     * @throws Solar_Sql_Exception on failure of any sort.
     * 
     * @see Solar_Sql_Model_Cache::deleteAll()
     * 
     */
    public function update($data, $where)
    {
        if (! is_array($data)) {
            throw $this->_exception('ERR_DATA_NOT_ARRAY', array(
                'method' => 'update',
            ));
        }
        
        // reset affected rows
        $this->_affected_rows = null;
        
        // don't update the primary key
        unset($data[$this->_primary_col]);
        
        // remove non-existent table columns from the data
        foreach ($data as $key => $val) {
            if (empty($this->_table_cols[$key])) {
                unset($data[$key]);
            }
        }
        
        // perform the update and track affected rows
        $this->_affected_rows = $this->_sql->update(
            $this->_table_name,
            $data,
            $where
        );
        
        // clear the cache for this model and related models
        $this->_cache->deleteAll();
        
        // done!
        return $this->_affected_rows;
    }
    
    /**
     * 
     * Deletes rows from the model table and deletes cache entries.
     * 
     * @param string|array $where The WHERE clause to identify which rows to 
     * delete.
     * 
     * @return int The number of rows affected.
     * 
     * @see Solar_Sql_Model_Cache::deleteAll()
     * 
     */
    public function delete($where)
    {
        // perform the deletion and track affected rows
        $this->_affected_rows = $this->_sql->delete(
            $this->_table_name,
            $where
        );
        
        // clear the cache for this model and related models
        $this->_cache->deleteAll();
        
        // done!
        return $this->_affected_rows;
    }
    
    /**
     * 
     * Serializes data values in-place based on $this->_serialize_cols and
     * $this->_xmlstruct_cols.
     * 
     * Does not attempt to serialize null values.
     * 
     * If serializing fails, stores 'null' in the data.
     * 
     * @param array &$data Record data.
     * 
     * @return void
     * 
     */
    public function serializeCols(&$data)
    {
        foreach ($this->_serialize_cols as $key) {

            // Don't process columns not in $data
            if (! array_key_exists($key, $data)) {
                continue;
            }

            // don't work on empty cols
            if (empty($data[$key])) {
                // Any empty value is canonicalized as null
                $data[$key] = null;
                continue;
            }
            
            $data[$key] = serialize($data[$key]);
            if (! $data[$key]) {
                // serializing failed
                $data[$key] = null;
            }
        }
        
        foreach ($this->_xmlstruct_cols as $key) {

            // Don't process columns not in $data
            if (! array_key_exists($key, $data)) {
                continue;
            }

            // don't work on empty cols
            if (empty($data[$key])) {
                // Any empty value is canonicalized as null
                $data[$key] = null;
                continue;
            }
            
            // convert to string representations, and nullify non-structs
            if ($data[$key] instanceof Solar_Struct_Xml) {
                $struct = $data[$key];
                $data[$key] = $struct->toString();
            } else {
                $data[$key] = null;
            }
        }
    }
    
    /**
     * 
     * Un-serializes data values in-place based on $this->_serialize_cols and
     * $this->_xmlstruct_cols.
     * 
     * Does not attempt to un-serialize null values.
     * 
     * If un-serializing fails, stores 'null' in the data.
     * 
     * @param array &$data Record data.
     * 
     * @return void
     * 
     */
    public function unserializeCols(&$data)
    {
        // unseralize columns as-needed
        foreach ($this->_serialize_cols as $key) {

            // Don't process columns not in $data
            if (! array_key_exists($key, $data)) {
                continue;
            }

            // only unserialize if a non-empty string
            if (empty($data[$key])) {
                // Any empty value is canonicalized as null
                $data[$key] = null;
            } else {
                if (is_string($data[$key])) {
                    $data[$key] = unserialize($data[$key]);
                    if (! $data[$key]) {
                        // unserializing failed
                        $data[$key] = null;
                    }
                }
            }
        }
        
        foreach ($this->_xmlstruct_cols as $key) {

            // Don't process columns not in $data
            if (! array_key_exists($key, $data)) {
                continue;
            }

            if (empty($data[$key])) {
                // create a new struct if there is no serialized data
                // in the column to begin with
                $struct = Solar::factory($this->_xmlstruct_class);
                $struct->load(array($key => array()));
                $data[$key] = $struct;
            } else {
                if (is_string($data[$key])) {
                    $struct = Solar::factory($this->_xmlstruct_class);
                    $struct->load($data[$key]);
                    $data[$key] = $struct;
                }
            }
        }
    }
    
    /**
     * 
     * Does this model have single-table inheritance values?
     * 
     * @return bool
     * 
     */
    public function isInherit()
    {
        return $this->_inherit_col && $this->_inherit_name;
    }
    
    /**
     * 
     * Adds a column filter.
     * 
     * This can be a "real" (table) or "virtual" (calculate) column.
     * 
     * Remember, filters are applied only to Record object data.
     * 
     * @param string $col The column name to filter.
     * 
     * @param string $method The filter method name, e.g. 'validateUnique'.
     * 
     * @args Remaining arguments are passed to the filter method.
     * 
     * @return void
     * 
     */
    protected function _addFilter($col, $method)
    {
        $args = func_get_args();
        array_shift($args); // the first param is $col
        $this->_filters[$col][] = $args;
    }
    
    /**
     * 
     * Adds a named has-one relationship.
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _hasOne($name, $opts = null)
    {
        settype($opts, 'array');
        if (empty($opts['class'])) {
            $opts['class'] = 'Solar_Sql_Model_Related_HasOne';
        }
        $this->_addRelated($name, $opts);
    }
    
    /**
     * 
     * Adds a named has-one-or-none relationship.
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _hasOneOrNull($name, $opts = null)
    {
        settype($opts, 'array');
        if (empty($opts['class'])) {
            $opts['class'] = 'Solar_Sql_Model_Related_HasOneOrNull';
        }
        $this->_addRelated($name, $opts);
    }
    
    /**
     * 
     * Adds a named belongs-to relationship.
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _belongsTo($name, $opts = null)
    {
        settype($opts, 'array');
        if (empty($opts['class'])) {
            $opts['class'] = 'Solar_Sql_Model_Related_BelongsTo';
        }
        $this->_addRelated($name, $opts);
    }
    
    /**
     * 
     * Adds a named has-many relationship.
     * 
     * Note that you can get "has-and-belongs-to-many" using "has-many"
     * with a "through" option ("has-many-through").
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _hasMany($name, $opts = null)
    {
        settype($opts, 'array');
        
        // maintain backwards-compat for has-many with 'through' option
        if (! empty($opts['through'])) {
            return $this->_hasManyThrough($name, $opts['through'], $opts);
        }
        
        if (empty($opts['class'])) {
            $opts['class'] = 'Solar_Sql_Model_Related_HasMany';
        }
        
        $this->_addRelated($name, $opts);
    }
    
    /**
     * 
     * Adds a named has-many through relationship.
     * 
     * Note that you can get "has-and-belongs-to-many" using "has-many"
     * with a "through" option ("has-many-through").
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param string $through The relationship name that acts as the "through"
     * model (i.e., the mapping model).
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _hasManyThrough($name, $through, $opts = null)
    {
        settype($opts, 'array');
        
        if (empty($opts['class'])) {
            $opts['class'] = 'Solar_Sql_Model_Related_HasManyThrough';
        }
        
        $opts['through'] = $through;
        
        $this->_addRelated($name, $opts);
    }
    
    /**
     * 
     * Support method for adding relations.
     * 
     * @param string $name The relationship name, which will double as a
     * property when records are fetched from the model.
     * 
     * @param array $opts Additional options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _addRelated($name, $opts)
    {
        // is the related name already a column name?
        if (array_key_exists($name, $this->_table_cols)) {
            throw $this->_exception('ERR_RELATED_CONFLICT', array(
                'name'  => $name,
                'class' => $this->_class,
            ));
        }
        
        // is the related name already in use?
        if (array_key_exists($name, $this->_related)) {
            throw $this->_exception('ERR_RELATED_EXISTS', array(
                'name'  => $name,
                'class' => $this->_class,
            ));
        }
        
        // keep it!
        $opts['name'] = $name;
        $this->_related[$name] = $opts;
    }
    
    /**
     * 
     * Gets the control object for a named relationship.
     * 
     * @param string $name The related name.
     * 
     * @return Solar_Sql_Model_Related The relationship control object.
     * 
     */
    public function getRelated($name)
    {
        if (! array_key_exists($name, $this->_related)) {
            throw $this->_exception('ERR_NO_SUCH_RELATED', array(
                'name'  => $name,
                'class' => $this->_class,
            ));
        }
        
        if (is_array($this->_related[$name])) {
            $opts = $this->_related[$name];
            $this->_related[$name] = Solar::factory($opts['class']);
            unset($opts['class']);
            $this->_related[$name]->setNativeModel($this);
            $this->_related[$name]->load($opts);
        }
        
        return $this->_related[$name];
    }

    /**
     * 
     * Establish state of this object prior to _setup().
     * 
     * @return void
     * 
     */
    protected function _preSetup()
    {
        // inflection reference
        $this->_inflect = Solar_Registry::get('inflect');
        
        // our class name so that we don't call get_class() all the time
        $this->_class = get_class($this);
        
        // get the catalog injection
        $this->_catalog = Solar::dependency(
            'Solar_Sql_Model_Catalog',
            $this->_config['catalog']
        );
        
        // connect to the database
        $this->_sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
    }

    /**
     * 
     * Complete the setup of this model.
     * 
     * @return void
     * 
     */
    protected function _postSetup()
    {
        // follow-on cleanup of critical user-defined values
        $this->_fixStack();
        $this->_fixTableName();
        $this->_fixModelName();
        $this->_fixArrayName();
        $this->_fixIndexInfo();
        $this->_fixTableCols(); // also creates table if needed
        $this->_fixPrimaryCol();
        $this->_fixPropertyCols();
        $this->_fixCalculateCols();
        $this->_fixFilterClass();
        $this->_fixFilters();
        $this->_fixCache(); // including cache class
        
        // create the cache object and set its model
        $this->_cache = Solar::factory($this->_cache_class, array(
            'cache'  => $this->_config['cache'],
        ));
        $this->_cache->setModel($this);
    }
    
    /**
     * 
     * Fixes the stack of parent classes for the model.
     * 
     * @return void
     * 
     */
    protected function _fixStack()
    {
        $this->_stack = Solar::factory('Solar_Class_Stack');
        $this->_stack->setByParents($this, 'Model');
    }
    
    /**
     * 
     * Loads table name into $this->_table_name, and pre-sets the value of
     * $this->_inherit_name based on the class name.
     * 
     * @return void
     * 
     */
    protected function _fixTableName()
    {
        /**
         * Pre-set the value of $_inherit_name.  Will be modified one
         * more time in _fixPropertyCols().
         */
        // find the closest base called *_Model.  we do this so that
        // we can honor the top-level table name with inherited models.
        // *do not* use the class stack, as Solar_Sql_Model has been
        // removed from it.
        $base_class = null;
        $base_name  = null;
        $parents = array_reverse(Solar_Class::parents($this->_class, true));
        foreach ($parents as $key => $val) {
            if (substr($val, -6) == '_Model') {
                // $key is now the value of the closest "_Model" class. -1 to
                // get the first class below that (e.g., *_Model_Nodes).
                // $base_class is then the class name that represents the
                // base of the model-inheritance hierarchy (which may not be
                // the immediate base in some cases).
                $base_class = $parents[$key - 1];
                
                // the base model name (e.g., Nodes).
                $pos = strrpos($base_class, '_Model_');
                if ($pos !== false) {
                    // the part after "*_Model_"
                    $base_name = substr($base_class, $pos + 7);
                } else {
                    // the whole class name
                    $base_name = $base_class;
                }
                
                break;
            }
        }
        
        // find the current model name (the part after "*_Model_")
        $pos = strrpos($this->_class, '_Model_');
        if ($pos !== false) {
            $curr_name = substr($this->_class, $pos + 7);
        } else {
            $curr_name = $this->_class;
        }
        
        // compare base model name to the current model name.
        // if they are different, consider this class an inherited one.
        if ($curr_name != $base_name) {
            
            // Solar_Model_Bookmarks and Solar_Model_Nodes_Bookmarks
            // both result in "bookmarks".
            $len = strlen($base_name);
            if (substr($curr_name, 0, $len + 1) == "{$base_name}_") {
                $this->_inherit_name = substr($curr_name, $len + 1);
            } else {
                $this->_inherit_name = $curr_name;
            }
            
            // set the base-class for inheritance
            $this->_inherit_base = $base_class;
        }
        
        /**
         * Auto-set the table name, if needed; leave it alone if already
         * user-specified.
         */
        if (empty($this->_table_name)) {
            // auto-define the table name.
            // change TableName to table_name.
            $this->_table_name = strtolower(
                $this->_inflect->camelToUnder($base_name)
            );
        }
    }
    
    /**
     * 
     * Fixes $this->_index_info listings.
     * 
     * @return void
     * 
     */
    protected function _fixIndexInfo()
    {
        // baseline index definition
        $baseidx = array(
            'name'    => null,
            'type'    => 'normal',
            'cols'    => null,
        );
        
        // fix up each index to have a full set of info
        foreach ($this->_index_info as $key => $val) {
            
            if (is_int($key) && is_string($val)) {
                // array('col')
                $info = array(
                    'name' => $val,
                    'type' => 'normal',
                    'cols' => array($val),
                );
            } elseif (is_string($key) && is_string($val)) {
                // array('col' => 'unique')
                $info = array(
                    'name' => $key,
                    'type' => $val,
                    'cols' => array($key),
                );
            } else {
                // array('alt' => array('type' => 'normal', 'cols' => array(...)))
                $info = array_merge($baseidx, (array) $val);
                $info['name'] = (string) $key;
                settype($info['cols'], 'array');
            }
            
            $this->_index_info[$key] = $info;
        }
    }
    
    /**
     * 
     * Fixes table column definitions in $_table_cols.
     * 
     * @return void
     * 
     */
    protected function _fixTableCols()
    {
        // should we scan the table cols at the database?
        if (! $this->_config['table_scan']) {
        
            // table scans turned off. assume that $_table_cols is mostly 
            // correct and force population of the minimum key set.
            $cols = $this->_fixCols($this->_table_cols);
            
        } else {
            
            // scan the database table for column descriptions
            try {
                
                // get the column descriptions from the database
                $cols = $this->_sql->fetchTableCols($this->_table_name);
                
            } catch (Solar_Sql_Adapter_Exception $e) {
                
                // does the table exist in the database?
                $list = $this->_sql->fetchTableList();
                if (! in_array($this->_table_name, $list)) {
                    
                    // no, try to create it ...
                    $this->_createTableAndIndexes();
                    
                    // ... and get the column descriptions
                    $cols = $this->_sql->fetchTableCols($this->_table_name);
                    
                } else {
                    
                    // found the table, must have been something else wrong
                    throw $e;
                    
                }
            }
            
            // @todo add a "sync" check to see if column data in the class
            // matches column data in the database, and throw an exception
            // if they don't match pretty closely.
            
        }
        
        // reset to the fixed columns
        $this->_table_cols = $cols;
    }
    
    /**
     * 
     * Fixes column info arrays to have a base set of keys.
     * 
     * @param array $cols The column descriptions to fix.
     * 
     * @return array The fixed column descriptions.
     * 
     */
    protected function _fixCols($cols)
    {
        $base = array(
            'name'    => null,
            'type'    => null,
            'size'    => null,
            'scope'   => null,
            'default' => null,
            'require' => false,
            'primary' => false,
            'autoinc' => false,
        );
        
        foreach ($cols as $name => $info) {
            $info['name'] = $name;
            $cols[$name] = array_merge($base, $info);
        }
        
        return $cols;
    }
    
    /**
     * 
     * Sets $_primary_col if not already set.
     * 
     * @return void
     * 
     */
    protected function _fixPrimaryCol()
    {
        if ($this->_primary_col) {
            return;
        }
        
        // use the first primary key; ignore later primary keys
        foreach ($this->_table_cols as $key => $val) {
            if ($val['primary']) {
                // found one!
                $this->_primary_col = $key;
                return;
            }
        }
    }
    
    /**
     * 
     * Fixes the model-name and table-alias for user input to this model.
     * 
     * @return void
     * 
     */
    protected function _fixModelName()
    {
        if (! $this->_model_name) {
            if ($this->_inherit_name) {
                $this->_model_name = $this->_inherit_name;
            } else {
                // get the part after the last Model_ portion
                $pos = strpos($this->_class, 'Model_');
                if ($pos) {
                    $this->_model_name = substr($this->_class, $pos+6);
                } else {
                    $this->_model_name = $this->_class;
                }
            }
            
            // convert FooBar to foo_bar
            $this->_model_name = strtolower(
                $this->_inflect->camelToUnder($this->_model_name)
            );
        }
    }
    
    /**
     * 
     * Fixes the array-name for this model.
     * 
     * @return void
     * 
     */
    protected function _fixArrayName()
    {
        if (! $this->_array_name) {
            $this->_array_name = $this->_inflect->toSingular(
                $this->_model_name
            );
        }
    }
    
    /**
     * 
     * Fixes up special column indicator properties, and post-sets the
     * $_inherit_name value based on the existence of the inheritance column.
     * 
     * @return void
     * 
     */
    protected function _fixPropertyCols()
    {
        // make sure these actually exist in the table, otherwise unset them
        $list = array(
            '_created_col',
            '_updated_col',
            '_primary_col',
            '_inherit_col',
        );
        
        foreach ($list as $col) {
            if (trim($this->$col) == '' ||
                ! array_key_exists($this->$col, $this->_table_cols)) {
                // doesn't exist in the table
                $this->$col = null;
            }
        }
        
        // post-set the inheritance model value
        if (! $this->_inherit_col) {
            $this->_inherit_name = null;
            $this->_inherit_base = null;
        }
        
        // set up the fetch-cols list
        settype($this->_fetch_cols, 'array');
        if (! $this->_fetch_cols) {
            $this->_fetch_cols = array_keys($this->_table_cols);
        }
        
        // simply force to array
        settype($this->_serialize_cols, 'array');
        settype($this->_xmlstruct_cols, 'array');
        
        // the "sequence" columns.  make sure they point to a sequence name.
        // e.g., string 'col' becomes 'col' => 'col'.
        $tmp = array();
        foreach ((array) $this->_sequence_cols as $key => $val) {
            if (is_int($key)) {
                $tmp[$val] = $val;
            } else {
                $tmp[$key] = $val;
            }
        }
        $this->_sequence_cols = $tmp;
        
        // make sure we have a hint to foreign models as to what colname
        // to use when referring to this model
        if (empty($this->_foreign_col)) {
            if (! $this->_inherit_name) {
                // not inherited
                $prefix = $this->_inflect->toSingular($this->_model_name);
                $this->_foreign_col = strtolower($prefix)
                                     . '_' . $this->_primary_col;
            } else {
                // inherited, can't just use the model name as a column name.
                // need to find base model foreign_col value.
                $base = Solar::factory($this->_inherit_base, array(
                    'sql' => $this->_sql
                ));
                $this->_foreign_col = $base->foreign_col;
                unset($base);
            }
        }
    }
    
    /**
     * 
     * Fix $_calculate_cols to make it look like $_table_cols.
     * 
     * @return void
     * 
     */
    protected function _fixCalculateCols()
    {
        // first, make sure they're keyed properly
        $cols = array();
        foreach ($this->_calculate_cols as $key => $val) {
            if (is_int($key)) {
                // old: is just a column name. key on the name, and set a
                // basic array value.
                $cols[$val] = array('name' => $val);
            } else {
                // new: is an array of column info.
                $cols[$key] = (array) $val;
            }
        }
        
        // fix them up
        $cols = $this->_fixCols($cols);
        
        // done!
        $this->_calculate_cols = $cols;
    }
    
    /**
     * 
     * Fix the $_filter_class property.
     * 
     * @return void
     * 
     */
    protected function _fixFilterClass()
    {
        if ($this->_filter_class) {
            return;
        }
        
        // use a special stack of vendors only
        $stack = Solar::factory('Solar_Class_Stack');
        $stack->setByVendors($this);
        
        // find the filter class
        $this->_filter_class = $stack->load('Filter');
    }
    
    /**
     * 
     * Fixes the $_filters array property.
     * 
     * @return void
     * 
     */
    protected function _fixFilters()
    {
        // make sure filters are an array
        settype($this->_filters, 'array');
        
        // make sure that strings are converted
        // to arrays so that _applyFilters() works properly.
        foreach ($this->_filters as $col => $list) {
            foreach ($list as $key => $val) {
                if (is_string($val)) {
                    $this->_filters[$col][$key] = array($val);
                }
            }
        }
        
        // add final fallback filters on all columns
        $this->_fixFilterCols($this->_table_cols);
        $this->_fixFilterCols($this->_calculate_cols);
    }
    
    /**
     * 
     * Adds filters for a given set of columns.
     * 
     * @param array $cols A set of column descriptions, typically from
     * $_table_cols or $_calculate_cols.
     * 
     * @return void
     * 
     */
    protected function _fixFilterCols($cols)
    {
        // low and high range values for integer filters
        $range = array(
            'smallint' => array(pow(-2, 15), pow(+2, 15) - 1),
            'int'      => array(pow(-2, 31), pow(+2, 31) - 1),
            'bigint'   => array(pow(-2, 63), pow(+2, 63) - 1)
        );
        
        // add filters based on data type
        foreach ($cols as $name => $info) {
            
            $type = $info['type'];
            switch ($type) {
            case 'bool':
                $this->_filters[$name][] = array('validateBool');
                $this->_filters[$name][] = array('sanitizeBool');
                break;
            
            case 'char':
            case 'varchar':
                // only add filters if not serializing or structing
                $skip = in_array($name, $this->_serialize_cols)
                     || in_array($name, $this->_xmlstruct_cols);
                      
                if (! $skip) {
                    $this->_filters[$name][] = array('validateString');
                    $this->_filters[$name][] = array('validateMaxLength',
                        $info['size']);
                    $this->_filters[$name][] = array('sanitizeString');
                }
                
                break;
            
            case 'smallint':
            case 'int':
            case 'bigint':
                $this->_filters[$name][] = array('validateInt');
                $this->_filters[$name][] = array('validateRange',
                    $range[$type][0], $range[$type][1]);
                $this->_filters[$name][] = array('sanitizeInt');
                break;
            
            case 'numeric':
                $this->_filters[$name][] = array('validateNumeric');
                $this->_filters[$name][] = array('validateSizeScope',
                    $info['size'], $info['scope']);
                $this->_filters[$name][] = array('sanitizeNumeric');
                break;
            
            case 'float':
                $this->_filters[$name][] = array('validateFloat');
                $this->_filters[$name][] = array('sanitizeFloat');
                break;
            
            case 'clob':
                // no filters, clobs are pretty generic
                break;
            
            case 'date':
                $this->_filters[$name][] = array('validateIsoDate');
                $this->_filters[$name][] = array('sanitizeIsoDate');
                break;
            
            case 'time':
                $this->_filters[$name][] = array('validateIsoTime');
                $this->_filters[$name][] = array('sanitizeIsoTime');
                break;
            
            case 'timestamp':
                $this->_filters[$name][] = array('validateIsoTimestamp');
                $this->_filters[$name][] = array('sanitizeIsoTimestamp');
                break;
            }
        }
    }
    
    /**
     * 
     * Fixes the cache class name.
     * 
     * @return void
     * 
     */
    protected function _fixCache()
    {
        // make sure we have a cache class
        if (empty($this->_cache_class)) {
            $class = $this->_stack->load('Cache', false);
            if (! $class) {
                $class = 'Solar_Sql_Model_Cache';
            }
            $this->_cache_class = $class;
        }
    }
    
    /**
     * 
     * Creates the table and indexes in the database using $this->_table_cols
     * and $this->_index_info.
     * 
     * @return void
     * 
     */
    protected function _createTableAndIndexes()
    {
        /**
         * Create the table.
         */
        $this->_sql->createTable(
            $this->_table_name,
            $this->_table_cols
        );
        
        /**
         * Create the indexes.
         */
        foreach ($this->_index_info as $name => $info) {
            try {
                // create this index
                $this->_sql->createIndex(
                    $this->_table_name,
                    $info['name'],
                    $info['type'] == 'unique',
                    $info['cols']
                );
            } catch (Exception $e) {
                // cancel the whole deal.
                $this->_sql->dropTable($this->_table_name);
                throw $e;
            }
        }
    }
}
