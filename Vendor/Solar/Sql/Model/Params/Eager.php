<?php
/**
 * 
 * A value-object to represent the various parameters available when eager-
 * fetching related records in a model fetch() call.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Eager.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
class Solar_Sql_Model_Params_Eager extends Solar_Sql_Model_Params {
    
    /**
     * 
     * Default data array.
     * 
     * @var array
     * 
     */
    protected $_data = array(
        'eager'                => array(),
        'alias'                => null,
        'cols'                 => array(),
        'merge'                => null,
        'native_by'            => null,
        'wherein_max'          => null,
        'cols_prefix'          => null,
        'join_type'            => null,
        'conditions'           => array(),
        'join_flag'            => null,
        'join_only'            => null,
    );
    
    /**
     * 
     * Sets the merge type to use ('client' or 'server').
     * 
     * @param string $val The merge type to use ('client' or 'server').
     * 
     * @return Solar_Sql_Model_Params_Eager
     * 
     */
    public function merge($val)
    {
        $val = strtolower($val);
        if (! $val) {
            $this->_data['merge'] = null;
        } elseif ($val == 'client' || $val == 'server') {
            $this->_data['merge'] = $val;
        } else {
            throw $this->_exception('ERR_UNKNOWN_MERGE', array(
                'merge' => $val,
                'known' => '"client" or "server"',
            ));
        }
        return $this;
    }
    
    /**
     * 
     * Sets the column-prefix to use when selecting columns.
     * 
     * @param string $val The column-prefix to use.
     * 
     * @return Solar_Sql_Model_Params_Eager
     * 
     */
    public function colsPrefix($val)
    {
        $val = (string) $val;
        if (! $val) {
            $this->_data['cols_prefix'] = null;
        } else {
            $this->_data['cols_prefix'] = (string) $val;
        }
        return $this;
    }
    
    /**
     * 
     * Sets the join type to use (null, 'left', or 'inner').
     * 
     * @param string $val The join type to use (null, 'left', or 'inner').
     * 
     * @return Solar_Sql_Model_Params_Eager
     * 
     */
    public function joinType($val)
    {
        $val = strtolower($val);
        if (! $val) {
            $this->_data['join_type'] = null;
        } elseif ($val == 'left' || $val == 'inner') {
            $this->_data['join_type'] = $val;
        } else {
            throw $this->_exception('ERR_UNKNOWN_JOIN_TYPE', array(
                'join_type' => $val,
                'known' => 'null, "left", or "inner"',
            ));
        }
        return $this;
    }
    
    /**
     * 
     * Sets the join condition to use; note that this overrides the existing
     * join condition.
     * 
     * @param string $cond The ON condition.
     * 
     * @param string $val A value to quote into the condition, replacing
     * question-mark placeholders.
     * 
     * @return Solar_Sql_Model_Params_Eager
     * 
     */
    public function addCondition($cond, $val = Solar_Sql_Select::IGNORE)
    {
        // BC-helping logic
        if (is_int($cond) && is_string($val)) {
            $cond = $val;
            $val = Solar_Sql_Select::IGNORE;
        }
        
        // now the real logic. use triple-equals so that empties are honored.
        if ($val === Solar_Sql_Select::IGNORE) {
            $this->_data['conditions'][] = $cond;
        } else {
            $this->_data['conditions'][$cond] = $val;
        }
        return $this;
    }
    
    /**
     * 
     * Sets the join flag; i.e., whether or not this eager should be used to
     * control which parent records are selected.
     * 
     * @param bool $val The join flag setting.
     * 
     * @return Solar_Sql_Model_Params_Eager
     * 
     */
    public function joinFlag($val)
    {
        $val = ($val === null) ? null : (bool) $val;
        $this->_data['join_flag'] = $val;
        return $this;
    }
    
    /**
     * 
     * Whether or not this is a "join-only"; in a join-only, the eager is
     * joined, but no rows are selected.
     * 
     * @param bool $val The join-only setting.
     * 
     * @return Solar_Sql_Model_Params_Eager
     * 
     */
    public function joinOnly($val)
    {
        $val = ($val === null) ? null : (bool) $val;
        $this->_data['join_only'] = $val;
        return $this;
    }
    
    /**
     * 
     * Should native records be selected by "WHERE IN (...)" a list of IDs,
     * or by a sub-SELECT?
     * 
     * @param bool $val The setting of 'wherein' or 'select'.
     * 
     * @return Solar_Sql_Model_Params_Eager
     * 
     */
    public function nativeBy($val)
    {
        $val = strtolower($val);
        if (! $val) {
            $this->_data['native_by'] = null;
        } elseif ($val == 'wherein' || $val == 'select') {
            $this->_data['native_by'] = $val;
        } else {
            throw $this->_exception('ERR_UNKNOWN_NATIVE_BY', array(
                'native_by' => $val,
                'known' => '"wherein" or "select"',
            ));
        }
        return $this;
    }
    
    /**
     * 
     * When automatically choosing a "native-by" strategy, the maximum number
     * of records to use a "WHERE IN (...)" for; past this amount, use a sub-
     * SELECT.
     * 
     * @param bool $val The setting of 'wherein' or 'select'.
     * 
     * @return Solar_Sql_Model_Params_Eager
     * 
     */
    public function whereinMax($val)
    {
        $val = ($val === null) ? null : (int) $val;
        $this->_data['wherein_max'] = $val;
        return $this;
    }
    
    /**
     * 
     * Loads this params object with an array of data using support methods.
     * 
     * @param array $data The data to load.
     * 
     * @return Solar_Sql_Model_Params_Eager
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
            'merge',
            'order',
            'cols_prefix' => 'colsPrefix',
            'join_type'   => 'joinType',
            'join_flag'   => 'joinFlag',
            'join_only'   => 'joinOnly',
            'native_by'   => 'nativeBy',
            'wherein_max' => 'whereinMax',
        ));

        $this->_loadTwo($data, array(
            'conditions'   => 'addCondition',
        ));
    }
}
