<?php
/**
 * 
 * Represents the characteristics of a relationship where a native model
 * "has many" of a foreign model.  This includes "has many through" (i.e.,
 * a many-to-many relationship through an interceding mapping model).
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
 * @version $Id: HasManyThrough.php 4489 2010-03-02 15:34:14Z pmjones $
 * 
 */
class Solar_Sql_Model_Related_HasManyThrough extends Solar_Sql_Model_Related_ToMany
{
    /**
     * 
     * The relationship name through which we find foreign records.
     * 
     * @var string
     * 
     */
    public $through;
    
    /**
     * 
     * The "through" table name. Not definable by the user; is taken from
     * the "through" relationship definition.
     * 
     * @var string
     * 
     */
    public $through_table;
    
    /**
     * 
     * The "through" table alias. Not definable by the user; is taken from
     * the "through" relationship definition.
     * 
     * @var string
     * 
     */
    public $through_alias;
    
    /**
     * 
     * In the "through" table, the column that has the matching native value.
     * 
     * @var string
     * 
     */
    public $through_native_col;
    
    /**
     * 
     * In the "through" table, the column that has the matching foreign value.
     * 
     * @var string
     * 
     */
    public $through_foreign_col;
    
    /**
     * 
     * The virtual element `through_key` automatically populates the 
     * 'through_foreign_col' value for you.
     * 
     * @var string.
     * 
     */
    public $through_key;
    
    /**
     * 
     * The conditions retrieved from the "through" model.
     * 
     * @var string|array
     * 
     */
    public $through_conditions;
    
    /**
     * 
     * The type of join for the "through" model.
     * 
     * @var string
     * 
     */
    public $through_join_type;
    
    /**
     * 
     * Sets the relationship type.
     * 
     * @return void
     * 
     */
    protected function _setType()
    {
        $this->type = 'has_many_through';
    }
    
    /**
     * 
     * Corrects the foreign_key value in the options; uses the native-model
     * table name as singular when a regular has-many, and the foreign-
     * model primary column as-is when a 'has-many through'.
     * 
     * @param array &$opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _fixForeignKey(&$opts)
    {
        $opts['foreign_key'] = $this->_foreign_model->primary_col;
    }
    
    /**
     * 
     * A support method for _fixRelated() to handle has-many relationships.
     * 
     * @param array &$opts The relationship options; these are modified in-
     * place.
     * 
     * @return void
     * 
     */
    protected function _setRelated($opts)
    {
        // retain the name of the "through" related
        $this->through = $opts['through'];
        
        // get the "through" relationship control
        $through = $this->_native_model->getRelated($this->through);
        
        // the foreign column
        if (empty($opts['foreign_col'])) {
            // named by foreign primary key (e.g., foreign.id)
            $this->foreign_col = $this->_foreign_model->primary_col;
        } else {
            $this->foreign_col = $opts['foreign_col'];
        }
        
        // the native column
        if (empty($opts['native_col'])) {
            // named by native primary key (e.g., native.id)
            $this->native_col = $this->_native_model->primary_col;
        } else {
            $this->native_col = $opts['native_col'];
        }
        
        // retain the "through join type"
        if (empty($opts['through_join_type'])) {
            $this->through_join_type = 'left';
        } else {
            $this->through_join_type = $opts['through_join_type'];
        }
        
        // get the through-table
        $this->through_table = $through->foreign_table;
        
        // get the through-alias
        $this->through_alias = $through->foreign_alias;
        
        // a little magic
        if (empty($opts['through_native_col']) &&
            empty($opts['through_foreign_col']) &&
            ! empty($opts['through_key'])) {
            // pre-define through_foreign_col
            $this->through_key = $opts['through_key'];
            $opts['through_foreign_col'] = $opts['through_key'];
        }
        
        // what's the native model key in the through table?
        if (empty($opts['through_native_col'])) {
            $this->through_native_col = $this->_native_model->foreign_col;
        } else {
            $this->through_native_col = $opts['through_native_col'];
        }
        
        // what's the foreign model key in the through table?
        if (empty($opts['through_foreign_col'])) {
            $this->through_foreign_col = $this->_foreign_model->foreign_col;
        } else {
            $this->through_foreign_col = $opts['through_foreign_col'];
        }
        
        // retain the "through where" mods
        $this->through_conditions = $through->getForeignConditions($this->through_alias);
    }
    
    /**
     * 
     * Modifies the native fetch params with eager joins so that the through 
     * table and the foreign table are joined properly.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param Solar_Sql_Model_Params_Fetch $fetch The native fetch params.
     * 
     * @return void
     * 
     * @see modEagerFetch()
     * 
     */
    protected function _modEagerFetch($eager, $fetch)
    {
        // first, join the native table to the through table
        $thru = array(
            'type' => strtolower($this->through_join_type),
            'name' => "{$this->through_table} AS {$this->through_alias}",
            'cond' => array(),
            'cols' => null,
        );
        
        $thru['cond'][] = "{$fetch['alias']}.{$this->native_col} = "
                        . "{$this->through_alias}.{$this->through_native_col}";
        
        $thru['cond'] = array_merge(
            $thru['cond'],
            $this->through_conditions
        );
        
        // keep for countPages() calls?
        if ($thru['type'] != 'left') {
            $thru['keep'] = true;
        } else {
            $thru['keep'] = false;
        }
        
        $fetch->join($thru);
        
        // now join to the through table to the foreign table
        $join = array(
            'type' => strtolower($eager['join_type']),
            'name' => "{$this->foreign_table} AS {$eager['alias']}",
            'cond' => array(),
            'cols' => null,
        );
        
        $join['cond'][] = "{$eager['alias']}.{$this->foreign_col} = "
                        . "{$this->through_alias}.{$this->through_foreign_col}";
        
        // foreign and eager conditions
        $join['cond'] = array_merge(
            $join['cond'],
            $this->getForeignConditions($eager['alias']),
            (array) $eager['conditions']
        );
        
        // keep for countPages() calls only if we kept "through", and the
        // foreign join is not a left join
        if ($thru['keep'] && $join['type'] != 'left') {
            $join['keep'] = true;
        } else {
            $join['keep'] = false;
        }
        
        // done!
        $fetch->join($join);
        
        // always DISTINCT so we don't get multiple duplicate native rows
        $fetch->distinct(true);
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
        // build the "through" join and its conditions
        $thru = array(
            'type' => 'inner',
            'name' => "{$this->through_table} AS {$this->through_alias}",
            'cond' => array(),
            'cols' => null,
        );
        
        $thru['cond'][] = "{$eager['alias']}.{$this->foreign_col} = "
                        . "{$this->through_alias}.{$this->through_foreign_col}";
        
        $thru['cond'] = array_merge(
            $thru['cond'],
            $this->through_conditions
        );
        
        // build the foreign where conditions
        $where = array();
        $col = "{$this->through_alias}.{$this->through_native_col}";
        $where["$col = ?"] = $array[$this->native_col];
        
        $where = array_merge(
            $where,
            $this->getForeignConditions($eager['alias'])
        );
        
        $params = array(
            'alias' => $eager['alias'],
            'cols'  => $eager['cols'],
            'join'  => $thru,
            'where' => $where,
            'order' => $this->order,
            'eager' => $eager['eager'],
        );
        
        $data = $this->_foreign_model->fetchAllAsArray($params);
        
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
        // build the "through" join and its conditions
        $index_col = "{$this->native_alias}__{$this->through_native_col}";
        $thru = array(
            'type' => 'inner',
            'name' => "{$this->through_table} AS {$this->through_alias}",
            'cond' => array(),
            'cols' => "{$this->through_native_col} AS {$index_col}",
        );
        
        $thru['cond'][] = "{$eager['alias']}.{$this->foreign_col} = "
                        . "{$this->through_alias}.{$this->through_foreign_col}";
        
        $thru['cond'] = array_merge(
            $thru['cond'],
            $this->through_conditions
        );
        
        // build the foreign join or where
        $col = "{$this->through_alias}.{$this->through_native_col}";
        
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
            'join'  => ($join) ? array($thru, $join) : $thru,
            'where' => $where,
            'order' => $this->order,
            'eager' => $eager['eager'],
        );
        
        $data = $this->_foreign_model->fetchAllAsArray($params);
        $data = $this->_collate($data, $index_col);
        
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
     * Fetches the related collection for a native ID or record.
     * 
     * @param mixed $spec If a scalar, treated as the native primary key
     * value; if an array or record, retrieves the native primary key value
     * from it.
     * 
     * @return object The related collection object.
     * 
     */
    public function fetch($spec)
    {
        if ($spec instanceof Solar_Sql_Model_Record || is_array($spec)) {
            $native_id = $spec[$this->native_col];
        } else {
            $native_id = $spec;
        }
        
        // build up the "through" join
        $thru = array(
            'type' => 'inner',
            'name' => "{$this->through_table} AS {$this->through_alias}",
            'cond' => array(),
            'cols' => null,
        );
        
        $thru['cond'][] = "{$this->foreign_alias}.{$this->foreign_col} = "
                        . "{$this->through_alias}.{$this->through_foreign_col}";
        
        $thru['cond'] = array_merge(
            $thru['cond'],
            $this->through_conditions
        );
        
        // build up the "where" conditions
        $where = array();
        $cond  = "{$this->through_alias}.{$this->through_native_col} = ?";
        $where[$cond] = $native_id;
        $where = array_merge(
            $where,
            $this->getForeignConditions($this->foreign_alias)
        );
        
        // build the fetch, and go
        $fetch = array(
            'alias' => $this->foreign_alias,
            'join'  => $thru,
            'where' => $where,
            'order' => $this->order,
        );
        
        $obj = $this->_foreign_model->fetchAll($fetch);
        
        if (! $obj) {
            $obj = $this->fetchEmpty();
        }
        
        return $obj;
    }
    
    /**
     * 
     * Collates a result array by an array key, grouping the results by that
     * value.
     *
     * @param array $array The result array.
     *
     * @param string $key The key in the array to collate by.
     * 
     * @return array An array of collated elements, keyed by the collation 
     * value.
     * 
     */
    protected function _collate($array, $key)
    {
        $collated = array();
        foreach ($array as $i => $row) {
            $val = $row[$key];
            unset($row[$key]); // clear the key from the array
            $collated[$val][] = $row;
        }
        return $collated;
    }
    
    /**
     * 
     * Returns a new, empty collection when there is no related data.
     * 
     * @return Solar_Sql_Model_Collection
     * 
     */
    public function fetchEmpty()
    {
        return $this->fetchNew();
    }
    
    /**
     * 
     * Saves the related "through" collection *and* the foreign collection
     * from a native record.
     * 
     * Ensures the "through" collection has an entry for each foreign record,
     * and adds/removes entried in the "through" collection as needed.
     * 
     * @param Solar_Sql_Model_Record $native The native record to save from.
     * 
     * @return void
     * 
     */
    public function save($native)
    {
        // get the foreign collection to work with
        $foreign = $native->{$this->name};
        
        // get the through collection to work with
        $through = $native->{$this->through};
        
        // if no foreign records, kill off all through records
        if ($foreign->isEmpty()) {
            $through->deleteAll();
            return;
        }
        
        // save the foreign records as they are, which creates the necessary
        // primary key values the through mapping will need
        $foreign->save();
        
        // the list of existing foreign values
        $foreign_list = $foreign->getColVals($this->foreign_col);
        
        // the list of existing through values
        $through_list = $through->getColVals($this->through_foreign_col);
        
        // find mappings that *do* exist but shouldn't, and delete them
        foreach ($through_list as $through_key => $through_val) {
            if (! in_array($through_val, $foreign_list)) {
                $through->deleteOne($through_key);
            }
        }
        
        // make sure all existing "through" have the right native IDs on them
        foreach ($through as $record) {
            $record->{$this->through_native_col} = $native->{$this->native_col};
        }
        
        // find mappings that *don't* exist, and add them
        foreach ($foreign_list as $foreign_val) {
            if (! in_array($foreign_val, $through_list)) {
                $through->appendNew(array(
                    $this->through_native_col  => $native->{$this->native_col},
                    $this->through_foreign_col => $foreign_val,
                ));
            }
        }
        
        // done with the mappings, save them
        $through->save();
    }
    
    /**
     * 
     * Are the related "foreign" and "through" collections valid?
     * 
     * @param Solar_Sql_Model_Record $native The native record.
     * 
     * @return bool
     * 
     */
    public function isInvalid($native)
    {
        $foreign = $native->{$this->name};
        $through = $native->{$this->through};
        
        // no foreign and no through means they can't be invalid
        if (! $foreign && ! $through) {
            return false;
        }
        
        // is foreign invalid?
        if ($foreign && $foreign->isInvalid()) {
            return true;
        }
        
        // is through invalid?
        if ($through && $through->isInvalid()) {
            return true;
        }
        
        // both foreign and through are valid
        return false;
    }
}
