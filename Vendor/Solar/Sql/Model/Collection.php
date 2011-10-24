<?php
/**
 * 
 * Represents a collection of Solar_Sql_Model_Record objects.
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
 * @version $Id: Collection.php 4405 2010-02-18 04:27:25Z pmjones $
 * 
 * @todo Implement an internal unit-of-work status registry so that we can 
 * handle mass insert/delete without hitting the database unnecessarily.
 * 
 */
class Solar_Sql_Model_Collection extends Solar_Struct
{
    /**
     * 
     * The "parent" model for this record.
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_model;
    
    /**
     * 
     * The pager information for this collection.
     * 
     * `count`
     * : (int) The total number of rows in the database.
     * 
     * `pages`
     * : (int) The total number of pages in the database (count / paging).
     * 
     * `paging`
     * : (int) The number of rows per page for the collection.
     * 
     * `page`
     * : (int) The page-number of the collection.
     * 
     * `begin`
     * : (int) The row-number at which the collection begins.
     * 
     * `end`
     * : (int) The row-number at which the collection ends.
     * 
     * @var array
     * 
     * @see setPagerInfo()
     * 
     * @see getPagerInfo()
     * 
     */
    protected $_pager_info = array(
        'count'  => null,
        'pages'  => null,
        'paging' => null,
        'page'   => null,
        'begin'  => null,
        'end'    => null,
    );
    
    /**
     * 
     * When calling save(), these are the data keys that were invalid and thus
     * not fully saved.
     * 
     * @var mixed
     * 
     * @see save()
     * 
     */
    protected $_invalid_offsets = array();
    
    /**
     * 
     * Returns a record from the collection based on its key value.  Converts
     * the stored data array to a record of the correct class on-the-fly.
     * 
     * @param int|string $key The sequential or associative key value for the
     * record.
     * 
     * @return Solar_Sql_Model_Record
     * 
     */
    public function __get($key)
    {
        if (! $this->__isset($key)) {
            // create a new blank record for the missing key
            $this->_data[$key] = $this->_model->fetchNew();
        }
        
        // convert array to record object.
        // honors single-table inheritance.
        if (is_array($this->_data[$key])) {
            
            // convert the data array to an object.
            // get the main data to load to the record.
            $load = $this->_data[$key];
            
            // done
            $this->_data[$key] = $this->_model->newRecord($load);
        }
        
        // return the record
        return $this->_data[$key];
    }
    
    /**
     * 
     * Returns an array of the unique primary keys contained in this 
     * collection. Will not cause records to be created for as of yet 
     * unaccessed rows.
     * 
     * @param string $col The column to look for; when null, uses the model
     * primary-key column.
     *
     * @return array
     * 
     */
    public function getPrimaryVals($col = null)
    {
        // what key to look for?
        if (empty($col)) {
            $col = $this->_model->primary_col;
        }
        
        // get all key values
        $list = array();
        foreach ($this->_data as $key => $val) {
            $list[$key] = $val[$col];
        }
        
        // done!
        return $list;
    }
    
    /**
     * 
     * Returns an array of all values for a single column in the collection.
     *
     * @param string $col The column name to retrieve values for.
     *
     * @return array An array of key-value pairs where the key is the
     * collection element key, and the value is the column value for that
     * element.
     * 
     */
    public function getColVals($col)
    {
        $list = array();
        foreach ($this as $key => $record) {
            $list[$key] = $record->$col;
        }
        return $list;
    }
    
    /**
     * 
     * Injects the model from which the data originates.
     * 
     * Also loads accessor method lists for column and related properties.
     * 
     * These let users override how the column properties are accessed
     * through the magic __get, __set, etc. methods.
     * 
     * @param Solar_Sql_Model $model The origin model object.
     * 
     * @return void
     * 
     */
    public function setModel(Solar_Sql_Model $model)
    {
        $this->_model = $model;
    }
    
    /**
     * 
     * Returns the model from which the data originates.
     * 
     * @return Solar_Sql_Model $model The origin model object.
     * 
     */
    public function getModel()
    {
        return $this->_model;
    }
    
    /**
     * 
     * Injects pager information for the collection.
     * 
     * Generally used only by the model fetchAll() and fetchAssoc() methods.
     * 
     * @param array $info An array of information with keys for `count`,
     * `pages`, `paging`, `page`, `begin`, and `end`.
     * 
     * @return void
     * 
     * @see $_pager_info
     * 
     */
    public function setPagerInfo($info)
    {
        $base = array(
            'count'  => null,
            'pages'  => null,
            'paging' => null,
            'page'   => null,
            'begin'  => null,
            'end'    => null,
        );
        
        $this->_pager_info = array_merge($base, $info);
    }
    
    /**
     * 
     * Gets the injected pager information for the collection.
     * 
     * @return array An array of information with keys for `count`,
     * `pages`, `paging`, `page`, `begin`, and `end`.
     * 
     * @see $_pager_info
     * 
     */
    public function getPagerInfo()
    {
        return $this->_pager_info;
    }
    
    /**
     * 
     * Loads the struct with data from an array or another struct.
     * 
     * This is a complete override from the parent load() method.
     * 
     * We need this so that fetchAssoc() loading works properly; otherwise, 
     * integer keys get renumbered, which disconnects the association.
     * 
     * @param array|Solar_Struct $spec The data to load into the object.
     * 
     * @return void
     * 
     */
    public function load($spec)
    {
        // force to array
        if ($spec instanceof Solar_Struct) {
            // we can do this because $spec is of the same class
            $this->_data = $spec->_data;
        } elseif (is_array($spec)) {
            $this->_data = $spec;
        } else {
            $this->_data = array();
        }
    }
    
    /**
     * 
     * Returns the data for each record in this collection as an array.
     * 
     * @return array
     * 
     */
    public function toArray()
    {
        $data = array();
        foreach ($this as $key => $record) {
            $data[$key] = $record->toArray();
        }
        return $data;
    }
    
    /**
     * 
     * Saves all the records from this collection to the database one-by-one,
     * inserting or updating as needed.
     * 
     * @return void
     * 
     */
    public function save()
    {
        // reset the "invalid record offset"
        $this->_invalid_offsets = array();
        
        // pre-logic
        $this->_preSave();
        
        // save, instantiating each record
        foreach ($this as $offset => $record) {
            if (! $record->isDeleted()) {
                $result = $record->save();
                if (! $result) {
                    $this->_invalid_offsets[] = $offset;
                }
            }
        }
        
        // post-logic
        $this->_postSave();
        
        // done!
        if ($this->_invalid_offsets) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * 
     * User-defined pre-save logic for the collection.
     * 
     * @return void
     * 
     */
    protected function _preSave()
    {
    }
    
    /**
     * 
     * User-defined post-save logic for the collection.
     * 
     * @return void
     * 
     */
    protected function _postSave()
    {
    }
    
    /**
     * 
     * Are there any records in the collection?
     * 
     * @return bool True if empty, false if not.
     * 
     */
    public function isEmpty()
    {
        return empty($this->_data);
    }
    
    /**
     * 
     * Are there any invalid records in the collection?
     * 
     * @return bool
     * 
     */
    public function isInvalid()
    {
        if ($this->_invalid_offsets) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Returns an array of invalidation messages from each invalid record, 
     * keyed on the record offset within the collection.
     * 
     * @return array
     * 
     */
    public function getInvalid()
    {
        $invalid = array();
        $list = $this->getInvalidRecords();
        foreach ($list as $offset => $record) {
            $list[$offset] = $record->getInvalid();
        }
        return $list;
    }
    
    /**
     * 
     * Returns an array of the invalid record objects within the collection,
     * keyed on the record offset within the collection.
     * 
     * @return array
     * 
     */
    public function getInvalidRecords()
    {
        $list = array();
        foreach ($this->_invalid_offsets as $key) {
            $list[$key] = $this->__get($key);
        }
        return $list;
    }
    
    /**
     * 
     * Deletes each record in the collection one-by-one.
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
        $this->_preDeleteAll();
        foreach ($this->_data as $key => $val) {
            $this->deleteOne($key);
        }
        $this->_postDeleteAll();
    }
    
    /**
     * 
     * User-defined pre-delete logic.
     * 
     * @return void
     * 
     */
    protected function _preDeleteAll()
    {
    }
    
    /**
     * 
     * User-defined post-delete logic.
     * 
     * @return void
     * 
     */
    protected function _postDeleteAll()
    {
    }
    
    /**
     * 
     * Fetches a new record and appends it to the collection.
     * 
     * @param array $spec An array of data for the new record.
     * 
     * @return Solar_Sql_Model_Record The newly-appended record.
     * 
     */
    public function appendNew($spec = null)
    {
        // create a new record from the spec and append it
        $record = $this->_model->fetchNew($spec);
        $this->_data[] = $record;
        return $record;
    }
    
    /**
     * 
     * Deletes a record from the database and removes it from the collection.
     * 
     * @param mixed $spec If a Solar_Sql_Model_Record, looks up the record in
     * the collection and deletes it.  Otherwise, is treated as an offset 
     * value (**not** a record primary key value) and that record is deleted.
     * 
     * @return void
     * 
     * @see getRecordOffset()
     * 
     */
    public function deleteOne($spec)
    {
        if ($spec instanceof Solar_Sql_Model_Record) {
            $key = $this->getRecordOffset($spec);
            if ($key === false) {
                $info = $spec->toArray();
                throw $this->_exception('ERR_NOT_IN_COLLECTION', $info);
            }
        } else {
            $key = $spec;
        }
        
        if ($this->__isset($key)) {
            $record = $this->__get($key);
            if (! $record->isDeleted()) {
                $record->delete();
            }
            $record->free();
            unset($record);
            unset($this->_data[$key]);
        }
    }
    
    /**
     * 
     * Removes all records from the collection but **does not** delete them
     * from the database.
     * 
     * @return void
     * 
     */
    public function removeAll()
    {
        $this->_data = array();
    }
    
    /**
     * 
     * Removes one record from the collection but **does not** delete it from
     * the database.
     * 
     * @param mixed $spec If a Solar_Sql_Model_Record, looks up the record in
     * the collection and deletes it.  Otherwise, is treated as an offset 
     * value (**not** a record primary key value) and that record is removed.
     * 
     * @return void
     * 
     * @see getRecordOffset()
     * 
     */
    public function removeOne($spec)
    {
        if ($spec instanceof Solar_Sql_Model_Record) {
            $key = $this->getRecordOffset($spec);
            if ($key === false) {
                $info = $spec->toArray();
                throw $this->_exception('ERR_NOT_IN_COLLECTION', $info);
            }
        } else {
            $key = $spec;
        }
        
        unset($this->_data[$key]);
    }
    
    /**
     * 
     * Given a record object, looks up its offset value in the collection.
     * 
     * For this to work, the record primary key must exist in the collection,
     * **and** the record looked up in the collection must have the same
     * primary key and be of the same class.
     * 
     * Note that the returned offset may be zero, indicating the first element
     * in the collection.  As such, you should check the return for boolean 
     * false to indicate failure.
     * 
     * @param Solar_Sql_Model_Record $record The record to find in the
     * collection.
     * 
     * @return mixed The record offset (which may be zero), or boolean false
     * if the same record was not found in the collection.
     * 
     */
    public function getRecordOffset($record)
    {
        // the primary value of the record
        $val = $record->getPrimaryVal();
        
        // mapping of primary-key values to offset values
        $map = array_flip($this->getPrimaryVals());
        
        // does the record primary value exist in the collection?
        // use array_key_exists() instead of empty() so we can honor zeroes.
        if (! array_key_exists($val, $map)) {
            return false;
        }
        
        // retain the offset value
        $offset = $map[$val];
        
        // look up the record inside the collection
        $lookup = $this->__get($offset);
        
        // the primary keys are already known to be the same from above.
        // if the classes match as well, consider records to be "the same".
        if (get_class($lookup) === get_class($record)) {
            return $offset;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * ArrayAccess: set a key value; appends to the array when using []
     * notation.
     * 
     * @param string $key The requested key.
     * 
     * @param string $val The value to set it to.
     * 
     * @return void
     * 
     */
    public function offsetSet($key, $val)
    {
        if ($key === null) {
            $key = $this->count();
            if (! $key) {
                $key = 0;
            }
        }
        
        return $this->__set($key, $val);
    }
    
    /**
     * 
     * Overrides normal locale() to use the **model** locale strings.
     * 
     * @param string $key The key to get a locale string for.
     * 
     * @param string $num If 1, returns a singular string; otherwise, returns
     * a plural string (if one exists).
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return string The locale string, or the original $key if no
     * string found.
     * 
     */
    public function locale($key, $num = 1, $replace = null)
    {
        return $this->_model->locale($key, $num, $replace);
    }
}