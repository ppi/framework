<?php
/**
 * 
 * Represents the characteristics of a relationship where a native model
 * "has many" of a foreign model.
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
 * @version $Id: HasMany.php 4371 2010-02-11 15:52:26Z pmjones $
 * 
 */
class Solar_Sql_Model_Related_HasMany extends Solar_Sql_Model_Related_ToMany
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
        $this->type = 'has_many';
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
     * Saves a related collection from a native record.
     * 
     * @param Solar_Sql_Model_Record $native The native record to save from.
     * 
     * @return void
     * 
     */
    public function save($native)
    {
        $foreign = $native->{$this->name};
        if ($foreign->isEmpty()) {
            return;
        }
        
        // set the foreign_col on each foreign record to the native value
        foreach ($foreign as $record) {
            $record->{$this->foreign_col} = $native->{$this->native_col};
        }
        
        $foreign->save();
    }
}
