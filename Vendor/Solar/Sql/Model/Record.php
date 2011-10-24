<?php
/**
 * 
 * Represents a single record returned from a Solar_Sql_Model.
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
 * @version $Id: Record.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
class Solar_Sql_Model_Record extends Solar_Struct
{
    const SQL_STATUS_DELETED    = 'deleted';
    const SQL_STATUS_INSERTED   = 'inserted';
    const SQL_STATUS_REFRESHED  = 'refreshed';
    const SQL_STATUS_ROLLBACK   = 'rollback';
    const SQL_STATUS_UNCHANGED  = 'unchanged';
    const SQL_STATUS_UPDATED    = 'updated';
    
    /**
     * 
     * A list of all accessor methods for all record classes.
     * 
     * @var array
     * 
     */
    static protected $_access_methods_list = array();
    
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
     * The list of accessor methods for individual column properties.
     * 
     * For example, a method called __getFooBar() will be registered for
     * ['get']['foo_bar'] => '__getFooBar'.
     * 
     * @var array
     * 
     */
    protected $_access_methods = array();
    
    /**
     * 
     * Tracks the the status *of this record* at the database.
     * 
     * Status values are:
     * 
     * `deleted`
     * : This record has been deleted; load(), etc. will not work.
     * 
     * `inserted`
     * : The record was inserted successfully.
     * 
     * `updated`
     * : The record was updated successfully.
     * 
     * @var string
     * 
     */
    protected $_sql_status = null;
    
    /**
     * 
     * Tracks if *this record* is new (i.e., not in the database yet).
     * 
     * @var bool
     * 
     */
    protected $_is_new = false;
    
    /**
     * 
     * Notes which values *on this record* are not valid.
     * 
     * Keyed on property name => failure message.
     * 
     * @var array
     * 
     */
    protected $_invalid = array();
    
    /**
     * 
     * If you call save() and an exception gets thrown, this stores that
     * exception.
     * 
     * @var Solar_Exception
     * 
     */
    protected $_save_exception;
    
    /**
     * 
     * Filters added for this one record object.
     * 
     * @var array
     * 
     */
    protected $_filters = array();
    
    /**
     * 
     * An array of the initial (clean) data for the record.
     * 
     * This tracks only table-column data, not calculate-cols or related-cols.
     * 
     * @var array
     * 
     * @see setStatus()
     * 
     */
    protected $_initial = array();
    
    /**
     * 
     * Magic getter for record properties; automatically calls __getColName()
     * methods when they exist.
     * 
     * @param string $key The property name.
     * 
     * @return mixed The property value.
     * 
     */
    public function __get($key)
    {
        $found = array_key_exists($key, $this->_data);
        if (! $found && ! empty($this->_model->related[$key])) {
            // the key is for a related that has no data yet.
            // get the relationship object and get the related object
            $related = $this->_model->getRelated($key);
            $this->_data[$key] = $related->fetch($this);
        }
        
        // if an accessor method exists, use it
        if (! empty($this->_access_methods[$key]['get'])) {
            // use accessor method
            $method = $this->_access_methods[$key]['get'];
            return $this->$method();
        } else {
            // no accessor method; use parent method.
            return parent::__get($key);
        }
    }
    
    /**
     * 
     * Magic setter for record properties; automatically calls __setColName()
     * methods when they exist.
     * 
     * @param string $key The property name.
     * 
     * @param mixed $val The value to set.
     * 
     * @return void
     * 
     */
    public function __set($key, $val)
    {
        // if an accessor method exists, use it
        if (! empty($this->_access_methods[$key]['set'])) {
            // use accessor method
            $method = $this->_access_methods[$key]['set'];
            $this->$method($val);
            $this->_setIsDirty();
        } else {
            // no accessor method; use parent method
            parent::__set($key, $val);
        }
    }
    
    /**
     * 
     * Sets a key in the data to null.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __unset($key)
    {
        // if an accessor method exists, use it
        if (! empty($this->_access_methods[$key]['unset'])) {
            // use accessor method
            $method = $this->_access_methods[$key]['unset'];
            $this->$method();
            $this->_setIsDirty();
        } else {
            // no accessor method; use parent method
            parent::__unset($key);
        }
    }
    
    /**
     * 
     * Checks if a data key is set.
     * 
     * @param string $key The requested data key.
     * 
     * @return void
     * 
     */
    public function __isset($key)
    {
        // if an accessor method exists, use it
        if (! empty($this->_access_methods[$key]['isset'])) {
            // use accessor method
            $method = $this->_access_methods[$key]['isset'];
            $result = $this->$method();
        } else {
            // no accessor method; use parent method
            $result = parent::__isset($key);
        }
        
        // done
        return $result;
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
    
    /**
     * 
     * Loads the struct with data from an array or another struct.
     * 
     * Also unserializes columns per the "serialize_cols" model property.
     * 
     * This is a complete override from the parent load() method.
     * 
     * @param array|Solar_Struct $spec The data to load into the object.
     * 
     * @param array $cols Load only these columns.
     * 
     * @return void
     * 
     */
    public function load($spec, $cols = null)
    {
        // force to array
        if ($spec instanceof Solar_Struct) {
            // we can do this because $spec is of the same class
            $load = $spec->_data;
        } elseif (is_array($spec)) {
            $load = $spec;
        } else {
            $load = array();
        }
        
        // remove any load columns not in the whitelist
        if (! empty($cols)) {
            $cols = (array) $cols;
            foreach ($load as $key => $val) {
                if (! in_array($key, $cols)) {
                    unset($load[$key]);
                }
            }
        }
        
        // Set values, respecting accessor methods
        foreach ($load as $key => $value) {
            $this->$key = $value;
        }
        
        // fix relateds
        $this->_fixRelatedData();
    }
    
    /**
     * 
     * Sets the access method lists for this instance.
     * 
     * @return void
     * 
     */
    protected function _setAccessMethods()
    {
        $class = get_class($this);
        if (! array_key_exists($class, self::$_access_methods_list)) {
            $this->_loadAccessMethodsList($class);
        }
        $this->_access_methods = self::$_access_methods_list[$class];
    }
    
    /**
     * 
     * Loads the access method list for a given class.
     * 
     * @param string $class The class to load methods for.
     * 
     * @return void
     * 
     * @see $_access_methods_list
     * 
     */
    protected function _loadAccessMethodsList($class)
    {
        $list = array();
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            
            // if not a "__" method, or if a native magic method, skip it
            $skip = strncmp($method, '__', 2) !== 0
                 || $method == '__set'
                 || $method == '__get'
                 || $method == '__isset'
                 || $method == '__unset';
                 
            if ($skip) {
                continue;
            }
            
            // get
            if (strncmp($method, '__get', 5) == 0) {
                $col = strtolower(preg_replace(
                    '/([a-z])([A-Z])/',
                    '$1_$2',
                    substr($method, 5)
                ));
                $list[$col]['get'] = $method;
                continue;
            }
            
            // set
            if (strncmp($method, '__set', 5) == 0) {
                $col = strtolower(preg_replace(
                    '/([a-z])([A-Z])/',
                    '$1_$2',
                    substr($method, 5)
                ));
                $list[$col]['set'] = $method;
                continue;
            }
            
            // isset
            if (strncmp($method, '__isset', 7) == 0) {
                $col = strtolower(preg_replace(
                    '/([a-z])([A-Z])/',
                    '$1_$2',
                    substr($method, 7)
                ));
                $list[$col]['isset'] = $method;
                continue;
            }
            
            // unset
            if (strncmp($method, '__unset', 7) == 0) {
                $col = strtolower(preg_replace(
                    '/([a-z])([A-Z])/',
                    '$1_$2',
                    substr($method, 7)
                ));
                $list[$col]['unset'] = $method;
                continue;
            }
        }
        
        // retain the list of methods
        self::$_access_methods_list[$class] = $list;
    }
    
    // -----------------------------------------------------------------
    //
    // Model
    //
    // -----------------------------------------------------------------
    
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
     * Gets the name of the primary-key column.
     * 
     * @return string
     * 
     */
    public function getPrimaryCol()
    {
        return $this->_model->primary_col;
    }
    
    /**
     * 
     * Gets the value of the primary-key column.
     * 
     * @return mixed
     * 
     */
    public function getPrimaryVal()
    {
        $col = $this->_model->primary_col;
        return $this->$col;
    }
    
    
    // -----------------------------------------------------------------
    //
    // Record data
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Converts the properties of this model Record or Collection to an array,
     * including related models stored in properties and calculated columns.
     * 
     * @return array
     * 
     */
    public function toArray()
    {
        $data = array();
        
        // snag a full list of available values
        // unloaded related values are not included            
        $keys = array_merge(
            array_keys($this->_data), 
            array_keys($this->_model->calculate_cols)
        );
        
        foreach ($keys as $key) {
            
            // not an empty-related. get the existing value.
            $val = $this->$key;
            
            // get the sub-value if any
            if ($val instanceof Solar_Struct) {
                $val = $val->toArray();
            }
            
            // keep the sub-value
            $data[$key] = $val;
        }
        
        // done!
        return $data;
    }
    
    // -----------------------------------------------------------------
    //
    // Persistence: save, insert, update, delete, refresh.
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Saves this record and all related records to the database, inserting or
     * updating as needed.
     * 
     * Hook methods:
     * 
     * 1. `_preSave()` runs before all save operations.
     * 
     * 2. `_preInsert()` and `_preUpdate()` run before the insert or update.
     * 
     * 3. As part of the model insert()/update() logic, `filter()` gets called,
     *    which itself has `_preFilter()` and `_postFilter()` hooks.
     *    
     * 4. `_postInsert()` and `_postUpdate()` run after the insert or update.
     * 
     * 5. `_postSave()` runs after all save operations, but before related
     *    records are saved.
     * 
     * 6. `_preSaveRelated()` runs before saving related records.
     * 
     * 7. Each related record is saved, invoking the save() routine with all
     *    its hooks on each related record.
     * 
     * 8. `_postSaveRelated()` runs after all related records are saved.
     * 
     * @param array $data An associative array of data to merge with existing
     * record data.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function save($data = null)
    {
        if ($this->isDeleted()) {
            throw $this->_exception('ERR_DELETED', array(
                'class' => get_class($this),
            ));
        }
        
        $this->_save_exception = null;
        
        // load data at save-time?
        if ($data) {
            $this->load($data);
            $this->_setIsDirty();
        }
        
        try {
            $this->_save();
            $this->_saveRelated();
            if ($this->isInvalid()) {
                return false;
            } else {
                return true;
            }
        } catch (Solar_Sql_Model_Record_Exception_Invalid $e) {
            // filtering should already have set the invalid messages
            $this->_save_exception = $e;
            return false;
        }
    }
    
    /**
     * 
     * Perform a save() within a transaction, with automatic commit and
     * rollback.
     * 
     * @param array $data An associative array of data to merge with existing
     * record data.
     * 
     * @return bool True on success, false on failure.
     * 
     * @todo Make this the default save() behavior? That means renamaing and
     * refactoring the record/collection save() methods.
     * 
     */
    public function saveInTransaction($data = null)
    {
        // convenient reference to the SQL connection
        $sql = $this->_model->sql;
        
        // start the transaction
        $sql->begin();
        
        try {
            
            // attempt the save
            if ($this->save($data)) {
                // entire save was valid, keep it
                $sql->commit();
                return true;
            } else {
                // at least one part of the save was *not* valid.
                // throw it all away.
                $sql->rollback();
                
                // note that we're not invalid, exactly, but that we
                // rolled back.
                $this->_setSqlStatus(self::SQL_STATUS_ROLLBACK);
                return false;
            }
            
        } catch (Exception $e) {
            
            // some sort of exception came up **besides** invalid data (which
            // is handled inside save() already).  get its message.
            if ($e->getCode() == 'ERR_QUERY_FAILED') {
                // special treatment for failed queries.
                $info = $e->getInfo();
                $text = $info['pdo_text'] . ". "
                      . "Please call getSaveException() for more information.";
            } else {
                // normal treatment.
                $text = $e->getCode() . ': ' . $e->getMessage();
            }
            
            // roll back and retain the exception
            $sql->rollback();
            $this->_save_exception = $e;
            
            // set as invalid and force the record status afterwards
            $this->setInvalid('*', $text);
            $this->_setSqlStatus(self::SQL_STATUS_ROLLBACK);
            
            // done
            return false;
        }
    }
    
    /**
     * 
     * Saves the current record, but only if the record is "dirty".
     * 
     * On saving, invokes the pre-save, pre- and post- insert/update,
     * and post-save hooks.
     * 
     * @return void
     * 
     */
    protected function _save()
    {
        // only save if need to
        if ($this->isDirty() || $this->isNew()) {
            
            // pre-save routine
            $this->_preSave();
            
            // perform pre-save for any relateds that need to modify the 
            // native record, but only if instantiated
            $list = array_keys($this->_model->related);
            foreach ($list as $name) {
                if (! empty($this->_data[$name])) {
                    $this->_model->getRelated($name)->preSave($this);
                }
            }
            
            // insert or update based on newness
            if ($this->isNew()) {
                $this->_insert();
            } else {
                $this->_update();
            }
            
            // post-save routine
            $this->_postSave();
        }
    }
    
    /**
     * 
     * User-defined pre-save logic.
     * 
     * @return void
     * 
     */
    protected function _preSave()
    {
    }
    
    /**
     * 
     * User-defined post-save logic.
     * 
     * @return void
     * 
     */
    protected function _postSave()
    {
    }
    
    /**
     * 
     * Inserts the current record into the database, making calls to pre- and
     * post-insert logic.
     * 
     * @return void
     * 
     */
    protected function _insert()
    {
        // pre-insert logic
        $this->_preInsert();
        
        // modify special columns for insert
        $this->_modInsert();
        
        // apply record filters
        $this->filter();
        
        // get the data for insert
        $data = $this->_getInsertData();
        
        // try the insert
        try {
            // retain the inserted ID, if any
            $id = $this->_model->insert($data);
        } catch (Solar_Sql_Adapter_Exception_QueryFailed $e) {
            // failed at at the database for some reason
            $this->setInvalid('*', $e->getInfo('pdo_text'));
            throw $e;
        }
        
        // if there is an autoinc column, set its value
        foreach ($this->_model->table_cols as $col => $info) {
            if ($info['autoinc'] && empty($this->_data[$col])) {
                // set the value ...
                $this->_data[$col] = $id;
                // ... and skip all other cols
                break;
            }
        }
        
        // record was successfully inserted
        $this->_setSqlStatus(self::SQL_STATUS_INSERTED);
        
        // post-insert logic
        $this->_postInsert();
    }
    
    /**
     * 
     * Modify the current record before it is inserted into the DB.
     * 
     * @return void
     * 
     */
    protected function _modInsert()
    {
        // time right now for created/updated
        $now = date('Y-m-d H:i:s');
        
        // force the 'created' value if there is a 'created' column
        $col = $this->_model->created_col;
        if ($col) {
            $this->$col = $now;
        }
        
        // force the 'updated' value if there is an 'updated' column
        $col = $this->_model->updated_col;
        if ($col) {
            $this->$col = $now;
        }
        
        // if inheritance is turned on, auto-set the inheritance value
        if ($this->_model->isInherit()) {
            $col = $this->_model->inherit_col;
            $this->$col = $this->_model->inherit_name;
        }
        
        // auto-set sequence values if needed
        foreach ($this->_model->sequence_cols as $col => $val) {
            if (empty($this->$col)) {
                // no value given for the key. add a new sequence value.
                $this->$col = $this->_model->sql->nextSequence($val);
            }
        }
    }
    
    /**
     * 
     * Gather values to insert into the DB for a new record.
     * 
     * @return array The values to be inserted.
     * 
     */
    protected function _getInsertData()
    {
        // get only table columns
        $data = array();
        $cols = array_keys($this->_model->table_cols);
        foreach ($this->_data as $col => $val) {
            if (in_array($col, $cols)) {
                $data[$col] = $val;
            }
        }
        
        // serialize columns for insert
        $this->_model->serializeCols($data);
        
        // done
        return $data;
    }
    
    /**
     * 
     * User-defined pre-insert logic.
     * 
     * @return void
     * 
     */
    protected function _preInsert()
    {
    }
    
    /**
     * 
     * User-defined post-insert logic.
     * 
     * @return void
     * 
     */
    protected function _postInsert()
    {
    }
    
    /**
     * 
     * Updates the current record at the database, making calls to pre- and
     * post-update logic.
     * 
     * @return void
     * 
     */
    protected function _update()
    {
        // pre-update logic
        $this->_preUpdate();
        
        // modify special columns for update
        $this->_modUpdate();
        
        // apply record filters
        $this->filter();
        
        // get the data for update
        $data = $this->_getUpdateData();
        
        // it's possible we have no data to update, even after all that
        if (! $data) {
            $this->_setSqlStatus(self::SQL_STATUS_UNCHANGED);
            return;
        }
        
        // build the where clause
        $primary = $this->getPrimaryCol();
        $where = array("$primary = ?" => $this->getPrimaryVal());
        
        // try the update
        try {
            $this->_model->update($data, $where);
        } catch (Solar_Sql_Adapter_Exception_QueryFailed $e) {
            // failed at at the database for some reason
            $this->setInvalid('*', $e->getInfo('pdo_text'));
            throw $e;
        }
        
        // record was successfully updated
        $this->_setSqlStatus(self::SQL_STATUS_UPDATED);
        
        // post-update logic
        $this->_postUpdate();
    }
    
    /**
     * 
     * Modify the current record before it is updated into the DB.
     * 
     * @return void
     * 
     */
    protected function _modUpdate()
    {
        // force the 'updated' value
        $col = $this->_model->updated_col;
        if ($col) {
            $this->$col = date('Y-m-d H:i:s');
        }
        
        // if inheritance is turned on, auto-set the inheritance value
        if ($this->_model->isInherit()) {
            $col = $this->_model->inherit_col;
            $this->$col = $this->_model->inherit_name;
        }
        
        // auto-set sequences where keys exist and values are empty
        foreach ($this->_model->sequence_cols as $col => $val) {
            if (array_key_exists($col, $this->_data) && empty($this->$col)) {
                // key is present but no value is given.
                // add a new sequence value.
                $this->$col = $this->_model->sql->nextSequence($val);
            }
        }
    }
    
    /**
     * 
     * Gather values to update into the DB.  Only values that have
     * Changed will be updated
     * 
     * @return array values that should be updated
     * 
     */
    protected function _getUpdateData()
    {
        // get only table columns that have changed
        $data = array();
        $cols = array_keys($this->_model->table_cols);
        foreach ($this->_data as $col => $val) {
            if (in_array($col, $cols) && $this->isChanged($col)) {
                $data[$col] = $val;
            }
        }
        
        // serialize columns for update
        $this->_model->serializeCols($data);
        
        // done!
        return $data;
    }
    
    /**
     * 
     * User-defined pre-update logic.
     * 
     * @return void
     * 
     */
    protected function _preUpdate()
    {
    }
    
    /**
     * 
     * User-defined post-update logic.
     * 
     * @return void
     * 
     */
    protected function _postUpdate()
    {
    }
    
    /**
     * 
     * Saves each related record.
     * 
     * Invokes the pre- and post- saveRelated methods.
     * 
     * @return void
     * 
     * @todo Keep track of invalid saves on related records and collections?
     * 
     */
    protected function _saveRelated()
    {
        // pre-hook
        $this->_preSaveRelated();
        
        // save each related
        $list = array_keys($this->_model->related);
        foreach ($list as $name) {
            
            // only save if instantiated
            if (! empty($this->_data[$name])) {
                // get the relationship object and save the related
                $related = $this->_model->getRelated($name);
                $related->save($this);
            }
        }
        
        // post-hook
        $this->_postSaveRelated();
    }
    
    /**
     * 
     * User-defined logic to execute before saving related records.
     * 
     * @return void
     * 
     */
    protected function _preSaveRelated()
    {
    }
    
    /**
     * 
     * User-defined logic to execute after saving related records.
     * 
     * @return void
     * 
     */
    protected function _postSaveRelated()
    {
    }
    
    /**
     * 
     * Deletes this record from the database.
     * 
     * @return void
     * 
     */
    public function delete()
    {
        if ($this->isNew()) {
            throw $this->_exception('ERR_CANNOT_DELETE_NEW_RECORD', array(
                'class' => get_class($this),
            ));
        }
        
        if ($this->isDeleted()) {
            throw $this->_exception('ERR_DELETED', array(
                'class' => get_class($this),
            ));
        }
        
        $this->_preDelete();
        
        $primary = $this->getPrimaryCol();
        $where = array(
            "$primary = ?" => $this->getPrimaryVal(),
        );
        
        $this->_model->delete($where);
        
        $this->_setSqlStatus(self::SQL_STATUS_DELETED);
        
        $this->_postDelete();
    }
    
    /**
     * 
     * User-defined pre-delete logic.
     * 
     * @return void
     * 
     */
    protected function _preDelete()
    {
    }
    
    /**
     * 
     * User-defined post-delete logic.
     * 
     * @return void
     * 
     */
    protected function _postDelete()
    {
    }
    
    /**
     * 
     * Refreshes data for this record from the database.
     * 
     * Note that this does not refresh any related or calculated values.
     * 
     * @return void
     * 
     */
    public function refresh()
    {
        if ($this->isNew()) {
            throw $this->_exception('ERR_CANNOT_REFRESH_NEW_RECORD', array(
                'class' => get_class($this),
            ));
        }

        if ($this->isDeleted()) {
            throw $this->_exception('ERR_DELETED', array(
                'class' => get_class($this),
            ));
        }
        
        $id = $this->getPrimaryVal();
        if (! $id) {
            throw $this->_exception('ERR_CANNOT_REFRESH_BLANK_ID', array(
                'class' => get_class($this),
            ));
        }
        
        $result = $this->_model->fetch($id);
        foreach ($this->_model->table_cols as $col => $info) {
            $this->_data[$col] = $result->_data[$col];
        }
        
        // note record is refreshed
        $this->_setSqlStatus(self::SQL_STATUS_REFRESHED);
        
        // cannot be dirty or invalid at this point
        $this->_is_dirty = false;
        $this->_invalid = array();
    }
    
    /**
     * 
     * Increments the value of a column **immediately at the database** and
     * retains the incremented value in the record.
     * 
     * Incrementing by a negative value effectively decrements the value.
     * 
     * N.b.: This results in 2 SQL calls: one to update the value at the
     * database, then one to select the new value from the database.
     * 
     * N.b.: You may have trouble incrementing from a NULL starting point.
     * You should define columns to be incremented with a  "DEFAULT '0'" so
     * they never are null (although strictly speaking you do *not* need to 
     * define them as NOT NULL).
     * 
     * N.b.: This **will not** clear the cache for the model, since it uses
     * direct SQL to effefct the increment.  Thus, you will need to clear the
     * cache manually if you want to the incremented values to show up from
     * the cache.
     * 
     * @param string $col The column to increment.
     * 
     * @param int|float $amt The amount to increment by (default 1).
     * 
     * @return int|float The value after incrementing.  Note that other 
     * processes may have incremented the column as well, so this may not
     * correspond directly with adding the amount to the current value in the
     * record.
     * 
     */
    public function increment($col, $amt = 1)
    {
        if ($this->isNew()) {
            throw $this->_exception('ERR_CANNOT_INCREMENT_NEW_RECORD', array(
                'class' => get_class($this),
            ));
        }

        if ($this->isDeleted()) {
            throw $this->_exception('ERR_DELETED', array(
                'class' => get_class($this),
            ));
        }
        
        // make sure the column exists
        if (! array_key_exists($col, $this->_model->table_cols)) {
            throw $this->_exception('ERR_NO_SUCH_COLUMN', array(
                'class' => get_class($this),
                'name' => $col,
            ));
        }
        
        // the table and primary-key col name
        $table = $this->_model->table_name;
        $key = $this->getPrimaryCol();
        $sql = $this->_model->sql;
        
        // we need to have a primary value
        $val = $this->getPrimaryVal();
        if (! $val) {
            throw $this->_exception('ERR_NO_PRIMARY_VAL', array(
                'primary_col' => $col
            ));
        };
        
        // change column by $amt
        $cmd = "UPDATE $table SET $col = $col + :amt WHERE $key = :$key";
        $sql->query($cmd, array(
            $key  => $val,
            'amt' => $amt,
        ));
        
        // get the most-current value
        $cmd = "SELECT $col FROM $table WHERE $key = :$key";
        $new = $sql->fetchValue($cmd, array($key => $val));
        
        // set the data directly, **without** passing through
        // __set(), so as not to dirty the record.
        $this->_data[$col] = $new;
        
        // fake the initial value so that isChanged() won't trigger
        $this->_initial[$col] = $new;
        
        // done!
        return $new;
    }
    
    // -----------------------------------------------------------------
    // 
    // Filtering and data invalidation.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Filter the data.
     * 
     * @param Solar_Filter $filter Use this filter instead of the default one.
     * When empty (the default), uses the default filter for the record.
     * 
     * @return void
     * 
     */
    public function filter($filter = null)
    {
        // pre-filter hook
        $this->_preFilter();
        
        // filter object
        if ($filter) {
            // do not free external filter
            $free = false;
        } else {
            // use default filter, free when done
            $filter = $this->newFilter();
            $free   = true;
        }
        
        // apply filters
        $valid = $filter->applyChain($this);
        
        // retain invalids
        $invalid = $filter->getChainInvalid();
        
        // free the filter?
        if ($free) {
            $filter->free();
        }
        
        // reclaim memory
        unset($filter);
        
        // was it valid?
        if (! $valid) {
            
            // use custom validation messages per column when available
            foreach ($invalid as $key => $old) {
                $locale_key = "INVALID_" . strtoupper($key);
                $new = $this->_model->locale($locale_key);
                if ($new != $locale_key) {
                    $invalid[$key] = $new;
                }
            }
            
            $this->_invalid = $invalid;
            throw $this->_exception('ERR_INVALID', $this->_invalid);
        }
        
        // post-logic, and done
        $this->_postFilter();
    }
    
    /**
     * 
     * Returns a new filter object with the filters from the record model.
     * 
     * @return Solar_Filter
     * 
     */
    public function newFilter()
    {
        // create a filter object based on the model's filter class
        $filter = Solar::factory($this->_model->filter_class);
        
        // note which table cols are not part of the fetch cols
        $skip = array_diff(
            array_keys($this->_model->table_cols),
            $this->_model->fetch_cols
        );
        
        // set filters as specified by the model
        foreach ($this->_model->filters as $key => $list) {
            // skip table cols that are not part of the fetch cols
            if (in_array($key, $skip)) {
                continue;
            }
            $filter->addChainFilters($key, $list);
        }
        
        // set filters added to this record
        foreach ($this->_filters as $key => $list) {
            $filter->addChainFilters($key, $list);
        }
        
        // set which elements are required by the table itself
        foreach ($this->_model->table_cols as $key => $info) {
            if ($info['autoinc']) {
                // autoinc are not required
                $flag = false;
            } elseif (in_array($key, $this->_model->sequence_cols)) {
                // auto-sequence are not required
                $flag = false;
            } else {
                // go with the col info
                $flag = $info['require'];
            }
            
            // set the requirement flag
            $filter->setChainRequire($key, $flag);
        }
        
        // tell the filter to use the model for locale strings
        $filter->setChainLocaleObject($this->_model);
        
        // done!
        return $filter;
    }
    
    /**
     * 
     * User-defined logic executed before filters are applied to the record
     * data.
     * 
     * @return void
     * 
     */
    protected function _preFilter()
    {
    }
    
    /**
     * 
     * User-defined logic executed after filters are applied to the record
     * data.
     * 
     * @return void
     * 
     */
    protected function _postFilter()
    {
    }
    
    /**
     * 
     * Forces one property to be "invalid" and sets a validation failure message
     * for it.
     * 
     * @param string $key The property name.
     * 
     * @param string $message The validation failure message.
     * 
     * @return void
     * 
     */
    public function setInvalid($key, $message)
    {
        $this->_invalid[$key][] = $message;
    }
    
    /**
     * 
     * Forces multiple properties to be "invalid" and sets validation failure
     * message for them.
     * 
     * @param array $list An associative array where the key is the property
     * name, and the value is a string (or array of strings) of invalidation
     * messages.
     * 
     * @return void
     * 
     */
    public function setInvalids($list)
    {
        foreach ($list as $key => $messages) {
            foreach ((array) $messages as $message) {
                $this->_invalid[$key][] = $message;
            }
        }
    }
    
    /**
     * 
     * Returns the validation failure message for one or more properties,
     * including the messages on related records and collections.
     * 
     * @param string $key Return the message for this property; if empty,
     * returns messages for all invalid properties.
     * 
     * @return string|array
     * 
     */
    public function getInvalid($key = null)
    {
        $invalid = $this->_getInvalid();
        if ($key) {
            return $invalid[$key];
        } else {
            return $invalid;
        }
    }
    
    /**
     * 
     * Support method to collect all validation failure messages for all
     * properties and relateds.
     * 
     * @return array
     * 
     */
    protected function _getInvalid()
    {
        // Start with the invalids we've collected for this record
        $list = $this->_invalid;
        
        $relateds = array_keys($this->_model->related);
        foreach ($relateds as $name) {
            // Skip relateds that are not instantiated
            // or that are NULL or array()
            if (!isset($this->_data[$name])) {
                continue;
            }
            
            // Prevent infinite recursion
            $related = $this->_model->getRelated($name);
            if ($related instanceof Solar_Sql_Model_Related_BelongsTo) {
                continue;
            }
            
            $val = $this->_data[$name];

            // Only check actual related objects
            if (!is_object($val)) {
                continue;
            }

            // Copy any invalid data from related items            
            if ($val->isInvalid()) {
                $list[$name] = $val->getInvalid();
            }
        }
        // done!
        return $list;
    }
    
    // -----------------------------------------------------------------
    //
    // Record status
    //
    // -----------------------------------------------------------------

    /**
     * 
     * Is the record new?
     * 
     * @return bool
     * 
     */
    public function isNew()
    {
        return (bool) $this->_is_new;
    }

    /**
     * 
     * Returns the SQL status of this record at the database.
     * 
     * @return string The status value.
     * 
     */
    public function getSqlStatus()
    {
        return $this->_sql_status;
    }
    
    /**
     * 
     * Sets the SQL status of this record, resetting dirty/new/invalid as
     * needed.
     * 
     * @param string $sql_status The new status to set on this record.
     * 
     * @return void
     * 
     */
    protected function _setSqlStatus($sql_status)
    {
        // is this a change in status?
        if ($sql_status == $this->_sql_status) {
            // no change, we're done
            return;
        }
        
        // set the new status
        $this->_sql_status = $sql_status;
        
        // should we reset other information?
        $reset = in_array($this->_sql_status, array(
            self::SQL_STATUS_INSERTED,
            self::SQL_STATUS_REFRESHED,
            self::SQL_STATUS_UNCHANGED,
            self::SQL_STATUS_UPDATED,
        ));
        
        if ($reset) {
            
            // reset the initial data for table columns
            $this->_initial = array_intersect_key(
                $this->_data,
                $this->_model->table_cols
            );
            
            // no longer invalid, dirty, or new
            $this->_invalid = array();
            $this->_is_dirty = false;
            $this->_is_new = false;
        }
    }
    
    /**
     * 
     * Tells if the record, or a particular table-column in the record, has
     * changed from its initial value.
     * 
     * This is slightly complicated.  Changes to or from a null are reported
     * as "changed".  If both the initial value and new value are numeric
     * (that is, whether they are string/float/int), they are compared using
     * normal inequality (!=).  Otherwise, the initial value and new value
     * are compared using strict inequality (!==).
     * 
     * This complexity results from converting string and numeric values in
     * and out of the database.  Coming from the database, a string numeric
     * '1' might be filtered to an integer 1 at some point, making it look
     * like the value was changed when in practice it has not.
     * 
     * Similarly, we need to make allowances for nulls, because a non-numeric
     * null is loosely equal to zero or an empty string.
     * 
     * @param string $col The table-column name; if null, 
     * 
     * @return void|bool Returns null if the table-column name does not exist,
     * boolean true if the data is changed, boolean false if not changed.
     * 
     * @todo How to handle changes to array values?
     * 
     */
    public function isChanged($col = null)
    {
        // if no column specified, check if the record as a whole has changed
        if ($col === null) {
            foreach ($this->_initial as $col => $val) {
                if ($this->isChanged($col)) {
                    return true;
                }
            }
            return false;
        }
        
        // col needs to exist in the initial array
        if (! array_key_exists($col, $this->_initial)) {
            return null;
        }
        
        // track changes on structs
        $dirty = $this->_data[$col] instanceof Solar_Struct
              && $this->_data[$col]->isDirty();
        if ($dirty) {
            return true;
        }
        
        // track changes to or from null
        $from_null = $this->_initial[$col] === null &&
                     $this->_data[$col] !== null;
        
        $to_null   = $this->_initial[$col] !== null &&
                     $this->_data[$col] === null;
        
        if ($from_null || $to_null) {
            return true;
        }
        
        // track numeric changes
        $both_numeric = is_numeric($this->_initial[$col]) &&
                        is_numeric($this->_data[$col]);
        if ($both_numeric) {
            // use normal inequality
            return $this->_initial[$col] != (string) $this->_data[$col];
        }
        
        // use strict inequality
        return $this->_initial[$col] !== $this->_data[$col];
    }
    
    /**
     * 
     * Is the record or one of its relateds invalid?
     * 
     * @return bool
     * 
     */
    public function isInvalid()
    {
        if ($this->_invalid) {
            // one or more properties on this record is invalid.
            // although we could use _getInvalid() here, this is
            // a quick shortcut for common cases.
            return true;
        } elseif ($this->_sql_status == self::SQL_STATUS_ROLLBACK) {
            // we had a rollback, so *something* is invalid
            return true;
        } elseif ($this->_getInvalid()) {
            // one or more related records is invalid
            return true;
        } else {
            // looks like nothing is invalid
            return false;
        }
    }
    
    /**
     * 
     * Gets a list of all changed table columns.
     * 
     * @return array
     * 
     */
    public function getChanged()
    {
        $list = array();
        foreach ($this->_initial as $col => $val) {
            if ($this->isChanged($col)) {
                $list[] = $col;
            }
        }
        return $list;
    }
    
    /**
     * 
     * Returns the exception (if any) generated by the most-recent call to the
     * save() method.
     * 
     * @return Exception
     * 
     * @see save()
     * 
     */
    public function getSaveException()
    {
        return $this->_save_exception;
    }
    
    // -----------------------------------------------------------------
    // 
    // Automated forms.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns a new Solar_Form object pre-populated with column properties,
     * values, and filters ready for processing (all based on the model for
     * this record).
     * 
     * @param array $cols An array of column property names to include in
     * the form.  If empty, uses all fetch columns and all calculate columns.
     * 
     * @return Solar_Form
     * 
     */
    public function newForm($cols = null)
    {
        // put into this array in the form
        $array_name = $this->_model->array_name;
        
        // build the form
        $form = Solar::factory('Solar_Form');
        $form->load('Solar_Form_Load_Model', $this->_model, $cols, $array_name);
        $form->setValues($this, $array_name);
        $form->addInvalids($this->_invalid, $array_name);
        
        // set the form status. if the record is invalid, always set the
        // form to failure.  if the record is valid, only set the form to
        // success when the form has not already been set to success.
        if ($this->isInvalid()) {
            
            // set the form to "failure"
            $form->setStatus(Solar_Form::STATUS_FAILURE);
            
        } elseif ($form->getStatus() !== Solar_Form::STATUS_FAILURE) {
            
            // set the form to "success" on these SQL statuses
            $success = array(
                self::SQL_STATUS_INSERTED,
                self::SQL_STATUS_UPDATED,
                self::SQL_STATUS_UNCHANGED,
            );
            
            if (in_array($this->getSqlStatus(), $success)) {
                $form->setStatus(Solar_Form::STATUS_SUCCESS);
            }
            
        }
        
        return $form;
    }
    
    /**
     * 
     * Adds a column filter to this record instance.
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
    public function addFilter($col, $method)
    {
        $args = func_get_args();
        array_shift($args); // the first param is $col
        $this->_filters[$col][] = $args;
    }
    
    /**
     * 
     * Initialize the record object.  This is effectively a "first load"
     * method.
     * 
     * @param Solar_Sql_Model $model The originating model object instance (a
     * dependency injection).
     * 
     * @param array $spec The data with which to initialize this record.
     * 
     * @return void
     * 
     */
    public function init(Solar_Sql_Model $model, $spec)
    {
        if ($this->_model) {
            throw $this->_exception('ERR_CANNOT_REINIT', array(
                'class' => get_class($this),
            ));
        }
        
        // inject the model
        $this->_model = $model;
        
        // sets access methods
        $this->_setAccessMethods();
        
        // force spec to array
        if ($spec instanceof Solar_Struct) {
            // we can do this because $spec is of the same class
            $load = $spec->_data;
        } elseif (is_array($spec)) {
            $load = $spec;
        } else {
            $load = array();
        }
        
        // unserialize any serialize_cols in the load
        $this->_model->unserializeCols($load);

        // Make sure changes to xml struct records cause us to be dirty
        foreach ($this->_model->xmlstruct_cols as $col) {
            if (!empty($load[$col])) {
                $load[$col]->setParent($this);
            }
        }
        
        // use parent load to push values directly into $_data array
        parent::load($load);
        
        // Record the inital values but only for columns that have physical backing
        $this->_initial = array_intersect_key($load, $model->table_cols);
        
        // placeholders for nonexistent calculate_cols, bypassing __set
        foreach ($this->_model->calculate_cols as $name => $info) {
            if (! array_key_exists($name, $this->_data)) {
                $this->_data[$name] = null;
            }
        }
        
        // fix up related data elements
        $this->_fixRelatedData();
        
        // new?
        $this->_is_new = false;
        
        // can't be invalid
        $this->_invalid = array();
        
        // can't be dirty
        $this->_is_dirty = false;
        
        // no last sql status
        $this->_sql_status = null;
    }
    
    /**
     * 
     * Initialize the record object as a "new" record; as with init(), this is
     * effectively a "first load" method.
     * 
     * @param Solar_Sql_Model $model The originating model object instance (a
     * dependency injection).
     * 
     * @param array $spec The data with which to initialize this record.
     * 
     * @return void
     * 
     * @see init()
     * 
     */
    public function initNew(Solar_Sql_Model $model, $spec)
    {
        $this->init($model, $spec);
        $this->_is_new = true;
    }
    
    /**
     * 
     * Has this record been deleted?
     * 
     * @return bool
     * 
     */
    public function isDeleted()
    {
        return $this->_sql_status == self::SQL_STATUS_DELETED;
    }
    
    /**
     * 
     * Make sure our related data values are the right value and type.
     * 
     * Make sure our related objects are the right type or will be loaded when
     * necessary
     * 
     * @return void
     * 
     */
    protected function _fixRelatedData()
    {
        $list = array_keys($this->_model->related);
        foreach ($list as $name) {
            
            // convert related values to correct object type
            $convert = array_key_exists($name, $this->_data)
                    && ! is_object($this->_data[$name]);
            
            if (! $convert) {
                continue;
            }
            
            $related = $this->_model->getRelated($name);
            if (empty($this->_data[$name])) {
                $this->_data[$name] = $related->fetchEmpty();
            } else {
                $this->_data[$name] = $related->newObject($this->_data[$name]);
            }
        }
    }
    
    /**
     * 
     * Create a new record/collection related to this one and returns it.
     * 
     * @param string $name The relation name.
     * 
     * @param array $data Initial data.
     * 
     * @return Solar_Sql_Model_Record|Solar_Sql_Model_Collection
     * 
     */
    public function newRelated($name, $data = null)
    {
        $related = $this->_model->getRelated($name);
        $new = $related->fetchNew($data);
        return $new;
    }
    
    /**
     * 
     * Sets the related to be a new record/collection, but only if the
     * related is empty.
     * 
     * @param string $name The relation name.
     * 
     * @param array $data Initial data.
     * 
     * @return Solar_Sql_Model_Record|Solar_Sql_Model_Collection
     * 
     */
    public function setNewRelated($name, $data = null)
    {
        if ($this->$name) {
            throw $this->_exception('ERR_RELATED_ALREADY_SET', array(
                'class' => get_class($this),
                'name'  => $name,
            ));
        }
        $this->$name = $this->newRelated($name, $data);
        return $this->$name;
    }
    
    /**
     * 
     * Convenience method for getting a dump of the record, or one of its
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
        unset($clone->_model);
        parent::dump($clone, $label);
    }
}
