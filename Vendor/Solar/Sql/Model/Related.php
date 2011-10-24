<?php
/**
 * 
 * Abstract class to represent the characteristics of a related model.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Jeff Moore <jeff@procata.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Related.php 4514 2010-03-15 15:06:16Z pmjones $
 * 
 */
abstract class Solar_Sql_Model_Related extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * @config int wherein_max The default value for the 'wherein_max'
     * setting.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Model_Related = array(
        'wherein_max' => 100,
    );
    
    /**
     * 
     * Indicates the strategy to use for merging joined rows; 'server' means
     * the database will do it via a single SELECT combined into the native 
     * fetch, whereas 'client' means PHP will do it, using one additional 
     * SELECT for the relationship.
     * 
     * @var string
     * 
     */
    public $merge;
    
    /**
     * 
     * The name of the relationship as defined by the original (native) model.
     * 
     * @var string
     * 
     */
    public $name;
    
    /**
     * 
     * The type of the relationship as defined by the original (native) model;
     * e.g., 'has_one', 'belongs_to', 'has_many'.
     * 
     * @var string
     * 
     */
    public $type;
    
    /**
     * 
     * The class of the native model.
     * 
     * @var string
     * 
     */
    public $native_class;
    
    /**
     * 
     * The alias for the native table.
     * 
     * @var string
     * 
     */
    public $native_alias;
    
    /**
     * 
     * The native column to match against the foreign primary column.
     * 
     * @var string
     * 
     */
    public $native_col;
    
    /**
     * 
     * The class name of the foreign model. Default is the first
     * matching class for the relationship name, as loaded from the parent
     * class stack.
     * 
     * 
     * @var string
     * 
     */
    public $foreign_class;
    
    /**
     * 
     * The name of the table for the foreign model. Default is the
     * table specified by the foreign model.
     * 
     * @var string
     * 
     */
    public $foreign_table;
    
    /**
     * 
     * Aliases the foreign table to this name. Default is the
     * relationship name.
     * 
     * @var string
     * 
     */
    public $foreign_alias;
    
    /**
     * 
     * The name of the column to join with in the *foreign* table.
     * This forms one-half of the relationship.  Default is per association
     * type.
     * 
     * @var string
     * 
     */
    public $foreign_col;
    
    /**
     * 
     * The name of the foreign primary column.
     * 
     * @var string
     * 
     */
    public $foreign_primary_col;
    
    /**
     * 
     * Fetch these columns for the related records.
     * 
     * @var string|array
     * 
     */
    public $cols;
    
    /**
     * 
     * Additional conditions when fetching related records.
     * 
     * @var string|array
     * 
     */
    public $conditions;
    
    /**
     * 
     * Additional ORDER clauses when fetching related records.
     * 
     * @var string|array
     * 
     */
    public $order;
    
    /**
     * 
     * The virtual element called `foreign_key` automatically
     * populates the `native_col` or `foreign_col` value for you, based on the
     * association type.  This will be used **only** when `native_col` **and**
     * `foreign_col` are not set.
     * 
     * @var string
     * 
     */
    public $foreign_key;
    
    /**
     * 
     * The virtual element called `foreign_name` automatically sets the
     * `foreign_class` by looking up the foreign_name in the model catalog.
     * The virtual element is used  only when foreign_class is not set.
     * 
     * @var string
     * 
     */
    public $foreign_name;
    
    /**
     * 
     * What strategy should be used for connecting to native records
     * when eager-fetching: 'wherein', meaning a "WHERE IN (...)" a list of
     * native IDs, or 'select', meaning a join against a sub-SELECT.
     * 
     * @var string
     * 
     */
    public $native_by;
    
    /**
     * 
     * When picking a native-by strategy, use 'wherein' for up to this many
     * record in the native result; after this point, use a 'select' strategy.
     * 
     * @var string
     * 
     */
    public $wherein_max;
    
    /**
     * 
     * An instance of the native (origin) model that defined this relationship.
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_native_model;
    
    /**
     * 
     * An instance of the foreign (related) model.
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_foreign_model;
    
    /**
     * 
     * The registered Solar_Inflect object.
     * 
     * @var Solar_Inflect
     * 
     */
    protected $_inflect;
    
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
        $this->_inflect = Solar_Registry::get('inflect');
    }
    
    /**
     * 
     * Sets the native (origin) model instance.
     * 
     * @param Solar_Sql_Model $model The native model instance.
     * 
     * @return void
     * 
     */
    public function setNativeModel($model)
    {
        $this->_native_model = $model;
        $this->native_class = $this->_native_model->class;
        $this->native_alias = $this->_native_model->model_name;
    }
    
    /**
     * 
     * Returns the related (foreign) model instance.
     * 
     * @return Solar_Sql_Model
     * 
     */
    public function getModel()
    {
        return $this->_foreign_model;
    }
    
    /**
     * 
     * Returns the relation characteristics as an array.
     * 
     * @return array
     * 
     */
    public function toArray()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $val) {
            if ($key[0] == '_') {
                unset($vars[$key]);
            }
        }
        return $vars;
    }
    
    /**
     * 
     * Loads this relationship object with user-defined characteristics
     * (options), and corrects them as needed.
     * 
     * @param array $opts The user-defined options for the relationship.
     * 
     * @return void
     * 
     */
    public function load($opts)
    {
        $this->name = $opts['name'];
        $this->_setType();
        $this->_setForeignClass($opts);
        $this->_setForeignModel($opts);
        $this->_setCols($opts);
        $this->_setConditions($opts);
        $this->_setOrder($opts);
        $this->_setMerge($opts);
        $this->_setNativeBy($opts);
        $this->_setWhereinMax($opts);
        
        // if the user has specified *neither* a foreign_col *nor* a native_col,
        // but *has* specified a foreign_key, use the foreign_key to define 
        // the foreign_col or native col (depending on relation type). 
        if (empty($opts['native_col']) && empty($opts['foreign_col'])) {
            
            // if a "virtual" foreign_key value is not set, define one
            if (empty($opts['foreign_key'])) {
                $this->_fixForeignKey($opts);
            }
            
            // retain the foreign key
            $this->foreign_key = $opts['foreign_key'];
            
            // now set the related column based on the foreign_key value
            $this->_fixRelatedCol($opts);
        }
        
        $this->_setRelated($opts);
    }
    
    /**
     * 
     * Convenience method for getting a dump the whole object, or one of its
     * properties, or an external variable.
     * 
     * @param mixed $var If null, dump $this; if a string, dump $this->$var;
     * otherwise, dump $var.
     * 
     * @param string $label Label the dump output with this string.
     * 
     * @return void
     * 
     */
    public function dump($var = null, $label = null)
    {
        if ($var) {
            return parent::dump($var, $label);
        }
        
        $clone = clone($this);
        unset($clone->_config);
        unset($clone->_native_model);
        unset($clone->_foreign_model);
        unset($clone->_inflect);
        return parent::dump($clone, $label);
    }
    
    /**
     * 
     * Is this related to one record?
     * 
     * @return bool
     * 
     */
    abstract public function isOne();
    
    /**
     * 
     * Is this related to many records?
     * 
     * @return bool
     * 
     */
    abstract public function isMany();
    
    /**
     * 
     * Packages foreign data as a record or collection object.
     * 
     * @param array $data The foreign Data
     * 
     * @return Solar_Sql_Model_Record|Solar_Sql_Model_Collection A record or 
     * collection object.
     * 
     */
    abstract public function newObject($data);
    
    /**
     * 
     * Returns an empty related value for an internal array result.
     * 
     * @return null
     * 
     */
    abstract protected function _getEmpty();
    
    /**
     * 
     * Fetches a new record or collection object.
     * 
     * @param array $data The data for the record or collection.
     * 
     * @return Solar_Sql_Model_Record|Solar_Sql_Model_Collection A record or 
     * collection object.
     * 
     */
    abstract public function fetchNew($data = null);
    
    /**
     * 
     * Returns a new empty value appropriate for a lazy- or eager-fetch;
     * this is different for each kind of related.
     * 
     * @return mixed
     * 
     */
    abstract public function fetchEmpty();
    
    /**
     * 
     * Sets the base name for the foreign class.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    abstract protected function _setForeignClass($opts);
    
    /**
     * 
     * Corrects the foreign_key value in the options.
     * 
     * @param array &$opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    abstract protected function _fixForeignKey(&$opts);
    
    /**
     * 
     * Sets the foreign model instance based on user-defined relationship
     * options.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setForeignModel($opts)
    {
        // get the foreign model from the catalog by its class name
        $catalog = $this->_native_model->catalog;
        $this->_foreign_model = $catalog->getModelByClass($this->foreign_class);
        
        // get its table name
        $this->foreign_table = $this->_foreign_model->table_name;
        
        // and its primary column
        $this->foreign_primary_col = $this->_foreign_model->primary_col;
        
        // set the foreign alias based on the relationship name
        $this->foreign_alias = $opts['name'];
    }
    
    /**
     * 
     * Sets the foreign columns to be selected based on user-defined 
     * relationship options.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setCols($opts)
    {
        // the list of foreign table cols to retrieve
        if (empty($opts['cols'])) {
            $this->cols = $this->_foreign_model->fetch_cols;
        } elseif (is_string($opts['cols'])) {
            $this->cols = explode(',', $opts['cols']);
        } else {
            $this->cols = (array) $opts['cols'];
        }
        
        // make sure we always retrieve the foreign primary key value,
        // if there is one.
        $primary = $this->_foreign_model->primary_col;
        if ($primary && ! in_array($primary, $this->cols)) {
            $this->cols[] = $primary;
        }
        
        // if inheritance is turned on for the foreign model,
        // make sure we always retrieve the foreign inheritance value.
        $inherit = $this->_foreign_model->inherit_col;
        if ($inherit && ! in_array($inherit, $this->cols)) {
            $this->cols[] = $inherit;
        }
        
    }
    
    /**
     * 
     * Sets additional conditions from the relationship definition; these are
     * used in the WHERE and/or JOIN ON conditions.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setConditions($opts)
    {
        if (empty($opts['conditions'])) {
            $this->conditions = array();
        } else {
            $this->conditions = (array) $opts['conditions'];
        }
    }
    
    /**
     * 
     * Sets default ORDER clause from the relationship definition.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _setOrder($opts)
    {
        if (empty($opts['order'])) {
            $this->order = array();
        } else {
            $this->order = (array) $opts['order'];
        }
    }
    
    /**
     * 
     * Sets the relationship type.
     * 
     * @return void
     * 
     */
    abstract protected function _setType();
    
    /**
     * 
     * Fixes the related column names in the user-defined options **in place**.
     * 
     * @param array $opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    abstract protected function _fixRelatedCol(&$opts);
    
    /**
     * 
     * Sets the characteristics for the related model, table, etc. based on
     * the user-defined relationship options.
     * 
     * @param array $opts The user-defined options for the relationship.
     * 
     * @return void
     * 
     */
    abstract protected function _setRelated($opts);
    
    /**
     * 
     * Sets the merge type.
     * 
     * @param array $opts The user-defined options for the relationship.
     * 
     * @return void
     * 
     */
    abstract protected function _setMerge($opts);
    
    /**
     * 
     * Fixes the native fetch params and eager params; then, if the join_flag
     * is set on the eager, calles _modEagerFetch() to modify the native fetch
     * params based on the eager params.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param Solar_Sql_Model_Params_Fetch $fetch The native fetch settings.
     * 
     * @return void
     * 
     * @see _modEagerFetch()
     * 
     */
    public function modEagerFetch($eager, $fetch)
    {
        $this->_fixFetchParams($fetch);
        $this->_fixEagerParams($eager);
        if ($eager['join_flag']) {
            $this->_modEagerFetch($eager, $fetch);
        }
    }
    
    /**
     * 
     * Fixes the native fetch params based on the settings for this related.
     * 
     * @param Solar_Sql_Model_Params_Fetch $fetch The native fetch settings.
     * 
     * @return void
     * 
     */
    protected function _fixFetchParams($fetch)
    {
        if (! $fetch['alias']) {
            $fetch->alias($this->native_alias);
        }
    }
    
    /**
     * 
     * Fixes the eager params based on the settings for this related.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @return void
     * 
     */
    protected function _fixEagerParams($eager)
    {
        // always need an alias
        if (! $eager['alias']) {
            $eager->alias($this->foreign_alias);
        }
        
        // always need a merge type
        if (! $eager['merge']) {
            $eager->merge($this->merge);
        }
        
        // if a condition is present, and no join type is specified, make it
        // an inner join. this is to mimic WHERE behavior.
        if ($eager['conditions'] && ! $eager['join_type']) {
            $eager->joinType('inner');
        }
        
        // always need a join type
        if (! $eager['join_type']) {
            $eager->joinType('left');
        }
        
        // for inner joins, always join to the main fetch
        if ($eager['join_type'] == 'inner') {
            $eager->joinFlag(true);
        }
        
        // which columns?
        if ($eager['join_only']) {
            // don't fetch cols when only joining ...
            $eager['cols'] = false;
            // ... and force the join
            $eager->joinFlag(true);
        } elseif ($eager['cols'] === array() || $eager['cols'] === null) {
            // empty array or null means "use default cols"
            $eager->cols($this->cols);
        }
        
        // native-by strategy (wherein or select)
        if (! $eager['native_by']) {
            $eager['native_by'] = $this->native_by;
        }
        
        // when to switch from array to select
        if ($eager['wherein_max'] === null) {
            $eager['wherein_max'] = $this->wherein_max;
        }
    }
    
    /**
     * 
     * Modifies the native fetch with an eager join so that the foreign table
     * is joined properly.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param Solar_Sql_Model_Params_Fetch $fetch The native fetch settings.
     * 
     * @return void
     * 
     * @see modEagerFetch()
     * 
     */
    abstract protected function _modEagerFetch($eager, $fetch);
    
    /**
     * 
     * Gets the foreign-model WHERE conditions and merges with the
     * WHERE conditions on this relationship.
     * 
     * @param string $alias The alias to use for the foreign table.
     * 
     * @return array An array of WHERE conditions.
     * 
     */
    public function getForeignConditions($alias)
    {
        $where = array_merge(
            $this->conditions,
            $this->_foreign_model->getConditions($alias)
        );
        return $where;
    }
    
    /**
     * 
     * Modifies the parent result array to add eager records.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param array &$result The parent result rows.
     * 
     * @param string $type The type of fetch performed (e.g., 'one', 'all', etc).
     * 
     * @param Solar_Sql_Model_Params_Fetch $fetch The native fetch settings.
     * 
     * @return void
     * 
     */
    abstract public function modEagerResult($eager, &$result, $type, $fetch);
    
    /**
     * 
     * Fetches the related record or collection for a native ID or record.
     * 
     * @param mixed $spec If a scalar, treated as the native primary key
     * value; if an array or record, retrieves the native primary key value
     * from it.
     * 
     * @return object The related record or collection object.
     * 
     */
    public function fetch($spec)
    {
        if ($spec instanceof Solar_Sql_Model_Record || is_array($spec)) {
            $native_id = $spec[$this->native_col];
        } else {
            $native_id = $spec;
        }
        
        $where = array();
        $cond  = "{$this->foreign_alias}.{$this->foreign_col} = ?";
        $where[$cond] = $native_id;
        
        $where = array_merge(
            $where,
            $this->getForeignConditions($this->foreign_alias)
        );
        
        $fetch = array(
            'alias' => $this->foreign_alias,
            'where' => $where,
            'order' => $this->order,
        );
        
        if ($this->isOne()) {
            $obj = $this->_foreign_model->fetchOne($fetch);
        } elseif ($this->isMany()) {
            $obj = $this->_foreign_model->fetchAll($fetch);
        } else {
            throw $this->_exception('ERR_NOT_ONE_OR_ALL', array(
                'class' => get_class($this),
            ));
        }
        
        if (! $obj) {
            $obj = $this->fetchEmpty();
        }
        
        return $obj;
    }
    
    /**
     * 
     * Given a results array, collates the results based on a key within each
     * result.
     * 
     * @param array $array The results array.
     * 
     * @param string $key The key to collate by.
     * 
     * @return array The collated array.
     * 
     */
    abstract protected function _collate($array, $key);
    
    /**
     * 
     * Sets the native-by strategy ('wherein' or 'select').
     * 
     * @param array $opts The user-defined options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _setNativeBy($opts)
    {
        // default to array
        if (empty($opts['native_by'])) {
            $this->native_by = 'wherein';
            return;
        }
        
        // check for 'wherein' or 'select'
        $opts['native_by'] = strtolower(trim($opts['native_by']));
        if ($opts['native_by'] == 'wherein' || $opts['native_by'] == 'select') {
            $this->wherein = $opts['native_by'];
        } else {
            throw $this->_exception('ERR_UNKNOWN_NATIVE_BY', array(
                'name' => $this->name,
                'native' => get_class($this->_native_model),
                'native_by' => $opts['native_by'],
                'known' => '"wherein" or "select"',
            ));
        }
    }
    
    /**
     * 
     * Sets the 'wherein_max' value (i.e., the number of records in the native
     * collection after which we should use a 'native-by select' strategy).
     * 
     * @param array $opts The user-defined options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _setWhereinMax($opts)
    {
        if (empty($opts['wherein_max'])) {
            $this->wherein_max = $this->_config['wherein_max'];
        } else {
            $this->wherein_max = (int) $opts['wherein_max'];
        }
    }
    
    /**
     * 
     * Fetches eager results into an existing single native array row.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param array &$array The existing native result row.
     * 
     * @return void
     * 
     */
    protected function _fetchIntoArrayOne($eager, &$array)
    {
        $where = array();
        
        $col = "{$eager['alias']}.{$this->foreign_col}";
        $where["$col = ?"] = $array[$this->native_col];
        
        $where = array_merge(
            $where,
            $this->getForeignConditions($eager['alias'])
        );
        
        $params = array(
            'alias' => $eager['alias'],
            'cols'  => $eager['cols'],
            'where' => $where,
            'order' => $this->order,
            'eager' => $eager['eager'],
        );
        
        if ($this->isOne()) {
            $data = $this->_foreign_model->fetchOneAsArray($params);
        } elseif ($this->isMany()) {
            $data = $this->_foreign_model->fetchAllAsArray($params);
        }
        
        $array[$this->name] = $data;
    }
    
    /**
     * 
     * Fetches eager results into an existing native array rowset.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param array &$array The existing native result row.
     * 
     * @param Solar_Sql_Model_Params_Fetch $fetch The native fetch settings.
     * 
     * @return void
     * 
     */
    protected function _fetchIntoArrayAll($eager, &$array, $fetch)
    {
        $col = "{$eager['alias']}.{$this->foreign_col}";
        
        $use_select = $eager['native_by'] == 'select'
                   || count($array) > $eager['wherein_max'];
        
        $join = null;
        $where = null;
        if ($use_select) {
            $join = $this->_getNativeBySelect($eager, $fetch, $col);
            $join['cond'] = array_merge(
                (array) $join['cond'],
                $this->getForeignConditions($eager['alias'])
            );
        } else {
            $where = array_merge(
                $this->_getNativeByWherein($eager, $array, $col),
                $this->getForeignConditions($eager['alias'])
            );
        }
        
        $params = array(
            'alias' => $eager['alias'],
            'cols'  => $eager['cols'],
            'join'  => $join,
            'where' => $where,
            'order' => $this->order,
            'eager' => $eager['eager'],
        );
        
        $data = $this->_foreign_model->fetchAllAsArray($params);
        $data = $this->_collate($data, $this->foreign_col);
        
        // now we have all the foreign rows for all-of-all of the native rows.
        // next is to tie each of those foreign sets to the appropriate
        // native result rows.
        foreach ($array as &$row) {
            $key = $row[$this->native_col];
            if (! empty($data[$key])) {
                $row[$this->name] = $data[$key];
            } else {
                $row[$this->name] = $this->_getEmpty();
            }
        }
    }
    
    /**
     * 
     * Returns an INNER JOIN specification for joining to the native table as
     * as sub-SELECT.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param Solar_Sql_Model_Params_Fetch $fetch The native fetch settings.
     * 
     * @param string $col The foreign column to join against.
     * 
     * @return array A join specification array.
     * 
     */
    protected function _getNativeBySelect($eager, $fetch, $col)
    {
        // get a *copy* of the fetch params; don't want to mess them up
        // for other eagers. only use the joins marked "keep".
        $clone = $fetch->cloneForKeeps();
        
        // reset the column list and get only the native column
        $clone['cols'] = array();
        $clone->cols($this->native_col);
        
        // for all sub-eagers, if they are joining to the top-level fetch,
        // make sure they are join_only ... otherwise, they'll add columns,
        // which will mess up our cols() from earlier.
        foreach ($clone['eager'] as $sub_eager) {
            if ($sub_eager['join_flag']) {
                $sub_eager['join_only'] = true;
            }
        }
        
        // don't waste time ordering the results
        $clone['order'] = false;
        
        // build a select and get it as a string
        $select = $this->_native_model->newSelect($clone);
        $string = $select->__toString();
        
        $join = array(
            'type' => "inner",
            'name' => "($string) AS {$fetch['alias']}",
            'cond' => "{$fetch['alias']}.{$this->native_col} = $col",
            'cols' => null,
        );
        
        // done!
        return $join;
    }
    
    /**
     * 
     * Returns an array of WHERE conditions for selecting only certain
     * native values from the foreign table using "WHERE IN (...)".
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param array $array The array of native results.
     * 
     * @param string $col The foreign column to use for the "WHERE IN (...)".
     * 
     * @return array An array of WHERE conditions.
     * 
     */
    protected function _getNativeByWherein($eager, $array, $col)
    {
        // get the list of IDs in the array
        $list = array();
        foreach ($array as $row) {
            $key = $row[$this->native_col];
            $list[$key] = true;
        }
        $list = array_keys($list);
        
        // tack it on to the end of the baseline relationship where clauses
        $where = array("$col IN (?)" => $list);
        
        // done!
        return $where;
    }
    
    /**
     * 
     * Pre-save hook for saving related records or collections from a native
     * record.
     * 
     * @param Solar_Sql_Model_Record $native The native record to save from.
     * 
     * @return void
     * 
     */
    public function preSave($native)
    {
        // at least for now, only belongs-to needs this
    }
    
    /**
     * 
     * Saves a related record or collection from a native record.
     * 
     * @param Solar_Sql_Model_Record $native The native record to save from.
     * 
     * @return void
     * 
     */
    abstract public function save($native);
    
    /**
     * 
     * Is the related record or collection valid?
     * 
     * @param Solar_Sql_Model_Record $native The native record to check from.
     * 
     * @return bool
     * 
     */
    public function isInvalid($native)
    {
        $foreign = $native->{$this->name};
        if ($foreign) {
            return $foreign->isInvalid();
        } else {
            // if it's not there, it can't be invalid
            return false;
        }
    }
}
