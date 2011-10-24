<?php
/**
 * 
 * Represents the characteristics of a "to-one" related model.
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
 * @version $Id: ToOne.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
abstract class Solar_Sql_Model_Related_ToOne extends Solar_Sql_Model_Related
{
    /**
     * 
     * Is this related to one record?
     * 
     * @return bool
     * 
     */
    public function isOne()
    {
        return true;
    }
    
    /**
     * 
     * Is this related to many records?
     * 
     * @return bool
     * 
     */
    public function isMany()
    {
        return false;
    }
    
    /**
     * 
     * Returns foreign data as a record object.
     * 
     * @param array $data The foreign data.
     * 
     * @return Solar_Sql_Model_Record A foreign record object.
     * 
     */
    public function newObject($data)
    {
        return $this->_foreign_model->newRecord($data);
    }
    
    /**
     * 
     * Returns an empty related value for an internal array result.
     * 
     * @return null
     * 
     */
    protected function _getEmpty()
    {
        return null;
    }
    
    /**
     * 
     * Fetches a new related record.
     * 
     * @param array $data Data for the new record.
     * 
     * @return Solar_Sql_Model_Record
     * 
     */
    public function fetchNew($data = array())
    {
        return $this->_foreign_model->fetchNew($data);
    }
    
    /**
     * 
     * Sets the base name for the foreign class; assumes the related name is
     * is singular and inflects it to plural.
     * 
     * @param array $opts The user-defined relationship eager.
     * 
     * @return void
     * 
     */
    protected function _setForeignClass($opts)
    {
        $catalog = $this->_native_model->catalog;
        
        // a little magic
        if (empty($opts['foreign_class']) && ! empty($opts['foreign_name'])) {
            $this->foreign_name = $opts['foreign_name'];
            $opts['foreign_class'] = $catalog->getClass($this->foreign_name);
        }
        
        if (empty($opts['foreign_class'])) {
            // no class given.  convert 'foo_bar' to 'foo_bars' ...
            $this->foreign_name = $this->_inflect->toPlural($opts['name']);
            // ... then use the plural form of the name to get the class.
            $this->foreign_class = $catalog->getClass($this->foreign_name);
        } else {
            $this->foreign_class = $opts['foreign_class'];
        }
    }
    
    /**
     * 
     * Fixes the related column names in the user-defined eager **in place**.
     * 
     * The foreign key is stored in the **foreign** model.
     * 
     * @param array $opts The user-defined relationship eager.
     * 
     * @return void
     * 
     */
    protected function _fixRelatedCol(&$opts)
    {
        $opts['foreign_col'] = $opts['foreign_key'];
    }
    
    /**
     * 
     * Sets the merge type; defaults to 'server' merges.
     * 
     * @param array $opts The user-defined options for the relationship.
     * 
     * @return void
     * 
     */
    protected function _setMerge($opts)
    {
        // default to server
        if (empty($opts['merge'])) {
            $this->merge = 'server';
            return;
        }
        
        // check for 'server' or 'client'
        $opts['merge'] = strtolower(trim($opts['merge']));
        if ($opts['merge'] == 'client' || $opts['merge'] == 'server') {
            $this->merge = $opts['merge'];
        } else {
            throw $this->_exception('ERR_UNKNOWN_MERGE', array(
                'merge' => $opts['merge'],
                'known' => '"client" or "server"',
            ));
        }
    }
    
    /**
     * 
     * Fixes the eager params based on the settings for this related.
     * 
     * Adds a column prefix when not already specified.
     * 
     * If there are sub-eagers, sets the merge strategy to 'client' so that
     * the sub-eagers are honored.
     * 
     * On a server merge, sets the join flag.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @return void
     * 
     */
    protected function _fixEagerParams($eager)
    {
        if (! $eager['cols_prefix']) {
            if ($eager['alias']) {
                $eager->colsPrefix($eager['alias']);
            } else {
                $eager->colsPrefix($this->name);
            }
        }
        
        // if there are sub-eagers, merge this eager client-side; otherwise,
        // the sub-eagers won't be honored.
        if ($eager['eager']) {
            $eager->merge('client');
        }
        
        parent::_fixEagerParams($eager);
        
        if ($eager['merge'] == 'server') {
            $eager->joinFlag(true);
        }
    }
    
    /**
     * 
     * Modifies the native fetch with an eager join so that the foreign table
     * is joined properly and foreign columns are selected.
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
        // the basic join array
        $join = array(
            'type' => strtolower($eager['join_type']),
            'name' => "{$this->foreign_table} AS {$eager['alias']}",
            'cond' => array(),
            'cols' => null,
        );
        
        // standard to-one condition (works for both has-one and belongs-to)
        $join['cond'][] = "{$fetch['alias']}.{$this->native_col} = "
                . "{$eager['alias']}.{$this->foreign_col}";
        
        // foreign and eager conditions
        $join['cond'] = array_merge(
            $join['cond'],
            $this->getForeignConditions($eager['alias']),
            (array) $eager['conditions']
        );
        
        // what columns to fetch?
        if (! $eager['cols']) {
            $cols = null;
        } else {
            $cols = array();
            foreach ($eager['cols'] as $col) {
                $cols[] = "{$col} AS {$eager['cols_prefix']}__{$col}";
            }
        }
        
        // add the columns
        $join['cols'] = $cols;
        
        // add the join to the parent fetch
        $fetch->join($join);
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
    public function modEagerResult($eager, &$result, $type, $fetch)
    {
        // pre-emptively return if no result, or no cols requested
        if (! $result || ! $eager['cols']) {
            return;
        }
        
        switch ($type) {
        case 'one':
            if ($eager['merge'] == 'server') {
                // server-side merge
                $this->_emergeFromArrayOne($eager, $result);
            } else {
                // client-side merge
                $this->_fetchIntoArrayOne($eager, $result);
            }
            break;
        case 'all':
        case 'assoc':
        case 'array':
            if ($eager['merge'] == 'server') {
                // server-side merge
                $this->_emergeFromArrayAll($eager, $result);
            } else {
                // client-side merge
                $this->_fetchIntoArrayAll($eager, $result, $fetch);
            }
            break;
        default:
            throw $this->_exception('ERR_UNKNOWN_FETCH', array(
                'fetch' => $type,
                'known' => '"one", "all", "assoc", or "array"',
            ));
            break;
        }
    }
    
    /**
     * 
     * Pulls server-merged foreign columns from the native results and puts
     * them into their own sub-array within the one native row.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param array &$array The native row with the foreign columns in it.
     * 
     * @return void
     * 
     */
    protected function _emergeFromArrayOne($eager, &$array)
    {
        $data = array();
        
        foreach ($eager['cols'] as $col) {
            $key = "{$eager['cols_prefix']}__{$col}";
            if (array_key_exists($key, $array)) {
                $data[$col] = $array[$key];
                unset($array[$key]);
            }
        }
        
        $array[$this->name] = $data;
    }
    
    /**
     * 
     * Pulls server-merged foreign columns from the native results and puts
     * them into their own sub-array within each of the many native rows.
     * 
     * @param Solar_Sql_Model_Params_Eager $eager The eager params.
     * 
     * @param array &$array The native rowset with the foreign columns in it.
     * 
     * @return void
     * 
     */
    protected function _emergeFromArrayAll($eager, &$array)
    {
        foreach ($array as &$row) {
            $this->_emergeFromArrayOne($eager, $row);
        }
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
            $collated[$val] = $row;
        }
        return $collated;
    }
}
