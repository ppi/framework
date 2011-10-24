<?php
/**
 * 
 * Represents the characteristics of a relationship where a native model
 * "has one" of a foreign model.
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
 * @version $Id: HasOne.php 4371 2010-02-11 15:52:26Z pmjones $
 * 
 */
class Solar_Sql_Model_Related_HasOne extends Solar_Sql_Model_Related_ToOne
{
    /**
     * 
     * Sets the relationship type.
     * 
     * @return void
     * 
     */
    protected function _setType()
    {
        $this->type = 'has_one';
    }
    
    /**
     * 
     * Corrects the foreign_key value in the options; uses the native-model
     * table name as singular.
     * 
     * @param array &$opts The user-defined relationship options.
     * 
     * @return void
     * 
     */
    protected function _fixForeignKey(&$opts)
    {
        $opts['foreign_key'] = $this->_native_model->foreign_col;
    }
    
    /**
     * 
     * A support method for _fixRelated() to handle has-one relationships.
     * 
     * @param array &$opts The relationship options; these are modified in-
     * place.
     * 
     * @return void
     * 
     */
    protected function _setRelated($opts)
    {
        // the foreign column
        if (empty($opts['foreign_col'])) {
            // named by native table's suggested foreign_col name
            $this->foreign_col = $this->_native_model->foreign_col;
        } else {
            $this->foreign_col = $opts['foreign_col'];
        }
        
        // the native column
        if (empty($opts['native_col'])) {
            // named by native primary key
            $this->native_col = $this->_native_model->primary_col;
        } else {
            $this->native_col = $opts['native_col'];
        }
    }
    
    /**
     * 
     * Returns a new record when there is no related data.
     * 
     * @return null
     * 
     */
    public function fetchEmpty()
    {
        return $this->fetchNew();
    }
    
    /**
     * 
     * Saves a related record from a native record.
     * 
     * @param Solar_Sql_Model_Record $native The native record to save from.
     * 
     * @return void
     * 
     */
    public function save($native)
    {
        $foreign = $native->{$this->name};
        
        // cover for has-one-or-null
        if (! $foreign) {
            return;
        }
        
        // set the foreign_col to the native value
        $foreign->{$this->foreign_col} = $native->{$this->native_col};
        $foreign->save();
    }
}
