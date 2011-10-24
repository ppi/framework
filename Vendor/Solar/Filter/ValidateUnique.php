<?php
/**
 * 
 * Validates that a value for the current data key is unique among all
 * model records of its inheritance type; note that this validation will
 * work only when the data source is a model record.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateUnique.php 4389 2010-02-14 20:10:24Z pmjones $
 * 
 */
class Solar_Filter_ValidateUnique extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that a value for the current data key is unique among all
     * model records of its inheritance type.
     * 
     * This will exclude any record having the same primary-key value as the
     * current record.
     * 
     * N.b.: If you are attempting to validate the primary column as unique,
     * you *have* to pass an additional $where condition on another unique
     * column. This is so the record being validated can recognize "itself"
     * in the database.  For example ...
     * 
     * {{code: php
     *     // validate 'foo' as unique when 'foo' is the primary column.
     *     // we need to make sure the record recognizes itself by some
     *     // other unique column value, 'bar'.  this is from inside a
     *     // Solar_Sql_Model::_setup() method.
     *     $where = array("bar != :bar AND bar IS NOT NULL")
     *     $this->_addFilter('foo', 'validateUnique', $where);
     * }}
     * 
     * ... but really, you should be using an artificial key (e.g. integer id
     * autoincremented) as your primary, not a natural key.  It makes this
     * *so* much easier.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $where Additional "WHERE" conditions to exclude records
     * from the uniqueness check.
     * 
     * @return bool True if unique, false if not.
     * 
     */
    public function validateUnique($value, $where = null)
    {
        // is the data source a record?
        $record = $this->_filter->getData();
        if (! $record instanceof Solar_Sql_Model_Record) {
            throw $this->_exception('ERR_NOT_MODEL_RECORD');
        }
        
        // make sure the $where is an array
        settype($where, 'array');
        
        // get the record (data) model
        $model = $record->getModel();
        
        // the column we're validating as unique. what we do is select the
        // current value from the database, and if it's there, that means
        // the current value is not unique.  we'll add exclusion conditions
        // below.
        $col = $this->_filter->getDataKey();
        $where[] = "$col = :$col";
        
        // what is the primary-key column for the record model?
        $primary = $model->primary_col;
        
        // only add a primary key exclusion if the column being validated
        // is not itself the primary key.
        if ($col != $primary) {
            // exclude the current record by its primary key value.
            if ($record[$primary] === null) {
                $where[] = "$primary IS NOT NULL";
            } else {
                $where[] = "$primary != :$primary";
            }
        }
        
        // see if we can fetch a row, with only the primary-key column to
        // reduce resource usage.
        $result = $model->fetchValue(array(
            'where' => $where,
            'cols'  => array($primary),
            'bind'  => $record->toArray(),
        ));
        
        // if empty, no result was returned, so the value is unique.
        return empty($result);
    }
}