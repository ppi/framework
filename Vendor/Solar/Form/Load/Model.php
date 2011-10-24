<?php
/**
 * 
 * Class for loading form definitions from Solar_Sql_Model columns.
 * 
 * @category Solar
 * 
 * @package Solar_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Jeff Moore <jeff@procata.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Model.php 4287 2009-12-31 16:47:54Z pmjones $
 * 
 */
class Solar_Form_Load_Model extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config int default_text_size The default 'size' attribute for text
     * elements.
     * 
     * @config int default_textarea_rows The default 'rows' attribute for
     * textarea elements.
     * 
     * @config int default_textarea_cols The default 'cols' attribute for
     * textarea elements.
     * 
     * @var array
     * 
     */
    protected $_Solar_Form_Load_Model = array(
        'default_text_size'     => 60,
        'default_textarea_rows' => 18,
        'default_textarea_cols' => 60,
    );
    
    /**
     * 
     * The model we use for getting information about columns for elements.
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_model;
    
    /**
     * 
     * Create elements as part of this array name in the form.
     * 
     * @var string
     * 
     */
    protected $_array_name;
    
    /**
     * 
     * Column definitions from the model.
     * 
     * @var array
     * 
     */
    protected $_cols;
    
    /**
     * 
     * Filter definitions from the model.
     * 
     * @var array
     * 
     */
    protected $_filters;
    
    /**
     * 
     * The column names and element options to load into the form.
     * 
     * @var array
     * 
     */
    protected $_load;
    
    /**
     * 
     * Default element values from the model.
     * 
     * @var array
     * 
     */
    protected $_default;
    
    /**
     * 
     * Loads Solar_Form elements based on Solar_Sql_Model columns.
     * 
     * @param Solar_Sql_Model $model Load form elements from this model object.
     * 
     * @param array $load Which model columns to load as form elements. If
     * empty or '*', uses all fetch and calculate columns.
     * 
     * @param string $array_name Load the model columns as elements of this
     * array-name within the form.
     * 
     * @return array An array of form attributes and elements.
     * 
     */
    public function fetch($model, $load = null, $array_name = null)
    {
        if (! $load) {
            $load = '*';
        }
        
        $this->_setModel($model);
        $this->_setLoad($load);
        $this->_setArrayName($array_name);
        
        // loop through the list of requested columns and collect elements
        $elements = array();
        foreach ($this->_load as $name => $spec) {
            
            // if $name is integer, $spec is just a column name,
            // and there are no added element specifications.
            if (is_int($name)) {
                $name = $spec;
                $spec = array();
            } else {
                settype($spec, 'array');
            }
            
            // get the column description
            $col = $this->_getCol($name);
            
            // get the base element
            $elem = $this->_newElement($spec);
            
            // fix each part of the element
            $this->_fixElement($elem, $name, $col);
            
            // keep the element
            $elements[$elem['name']] = $elem;
        }
        
        // done!
        $result = array(
            'attribs'  => array(),
            'elements' => $elements
        );
        
        return $result;
    }
    
    /**
     * 
     * Sets the model to use for loading.
     * 
     * @param Solar_Sql_Model $model The model to use for loading.
     * 
     * @return void
     * 
     */
    protected function _setModel($model)
    {
        // make sure it's a model
        if (! $model instanceof Solar_Sql_Model) {
            throw $this->_exception('ERR_NOT_MODEL_OBJECT');
        }
        
        $this->_model = $model;
        $this->_setCols();
        $this->_setFilters();
        $this->_setDefault();
    }
    
    /**
     * 
     * Sets the array-name for form elements.
     * 
     * @param string $array_name The array name to use.
     * 
     * @return void
     * 
     */
    protected function _setArrayName($array_name)
    {
        // if not specified, set the array_name to the model name
        if (empty($array_name)) {
            $this->_array_name = $this->_model->array_name;
        } else {
            $this->_array_name = $array_name;
        }
    }
    
    /**
     * 
     * Sets the column definitions from the model.
     * 
     * @return void
     * 
     */
    protected function _setCols()
    {
        // all table and calculate column descriptions in the model
        $this->_cols = array_merge(
            $this->_model->table_cols,
            $this->_model->calculate_cols
        );
    }
    
    /**
     * 
     * Gets a column definition from the model; if the column does not exist
     * at the model, gets a "fake" column definition instead.
     * 
     * @param string $name The column name to retrieve.
     * 
     * @return array The column definition.
     * 
     */
    protected function _getCol($name)
    {
        if (! empty($this->_cols[$name])) {
            return $this->_cols[$name];
        } else {
            return $this->_getFakeCol($name);
        }
    }
    
    /**
     * 
     * Gets a "fake" column definition.
     * 
     * @param string $name The fake column name.
     * 
     * @return array The fake column definition.
     * 
     */
    protected function _getFakeCol($name)
    {
        return array(
            'name'    => $name,
            'type'    => 'text',
            'size'    => null,
            'scope'   => null,
            'default' => null,
            'require' => false,
            'primary' => false,
            'autoinc' => false,
        );
    }
    
    /**
     * 
     * Sets the list of column names and element options to load as elements.
     * 
     * @param string|array $load The column names and element options.  If a
     * '*' is used, loads all columns from the model, minus special columns
     * (e.g. primary, created, xmlstruct, etc).
     * 
     * @return void
     * 
     */
    protected function _setLoad($load)
    {
        // if not '*', we have a list of element names and element hints
        if ($load != '*') {
            $this->_load = (array) $load;
            return;
        }
        
        // looking for '*' columns; set the list to all the model columns.
        if ($this->_model->fetch_cols) {
            // use the fetch and calculate cols
            $load = array_merge(
                $this->_model->fetch_cols,
                array_keys($this->_model->calculate_cols)
            );
        } else {
            // use all columns
            $load = array_keys($this->_cols);
        }
        
        // flip around so we can unset easier
        $load = array_flip($load);
        
        // remove special columns
        unset($load[$this->_model->primary_col]);
        unset($load[$this->_model->created_col]);
        unset($load[$this->_model->updated_col]);
        unset($load[$this->_model->inherit_col]);
        
        // remove sequence columns
        foreach ($this->_model->sequence_cols as $key => $val) {
            unset($load[$key]);
        }
        
        // remove xmlstruct columns
        foreach ($this->_model->xmlstruct_cols as $key => $val) {
            unset($load[$key]);
        }
            
        // done!
        $this->_load = array_keys($load);
    }
    
    /**
     * 
     * Sets the default values for elements, using the model.
     * 
     * @return void
     * 
     */
    protected function _setDefault()
    {
        $this->_default = $this->_model->fetchNew();
    }
    
    /**
     * 
     * Gets the default value for a column, or null if the columns does not
     * exist at the model.
     * 
     * @param string $name The column name to get default value for.
     * 
     * @return mixed
     * 
     */
    protected function _getDefault($name)
    {
        if (isset($this->_default[$name])) {
            return $this->_default[$name];
        } else {
            return null;
        }
    }
    
    /**
     * 
     * Sets the filters from the model.
     * 
     * @return void
     * 
     */
    protected function _setFilters()
    {
        $this->_filters = $this->_model->filters;
    }
    
    /**
     * 
     * Gets the filters for a column.
     * 
     * @param string $name The column name.
     * 
     * @return array
     * 
     */
    protected function _getFilters($name)
    {
        if (! empty($this->_filters[$name])) {
            return $this->_filters[$name];
        } else {
            return array();
        }
    }
    
    /**
     * 
     * Returns a new (baseline) form element array.
     * 
     * @param array $spec Element specification hints.
     * 
     * @return array The baseline element array.
     * 
     */
    protected function _newElement($spec)
    {
        // initial set of element keys
        $elem = array(
            'name'    => null,
            'type'    => null,
            'label'   => null,
            'descr'   => null,
            'value'   => null,
            'require' => null,
            'disable' => null,
            'options' => null,
            'attribs' => null,
            'filters' => null,
            'invalid' => null,
        );
        
        // set up the base element with the element info hints
        $elem = array_merge($elem, $spec);
        
        // done
        return $elem;
    }
    
    /**
     * 
     * Fixes each part of the element in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElement(&$elem, $name, $col)
    {
        foreach ($elem as $key => $val) {
            $method = "_fixElement" . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($elem, $name, $col);
            }
        }
    }
    
    /**
     * 
     * Fixes the element name in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementName(&$elem, $name, $col)
    {
        if ($elem['name'] !== null) {
            return;
        }
        
        $elem['name'] = $this->_array_name . '[' . $name . ']';
    }
    
    /**
     * 
     * Fixes the element type in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementType(&$elem, $name, $col)
    {
        if ($elem['type'] !== null) {
            return;
        }
        
        // hide primary keys
        if ($col['primary']) {
            $elem['type'] = 'hidden';
            return;
        }
        
        // pick an element type based on the column type
        switch ($col['type']) {
        
        case 'bool':
            $elem['type'] = 'checkbox';
            break;
            
        case 'clob':
            $elem['type'] = 'textarea';
            break;
            
        case 'date':
        case 'time':
        case 'timestamp':
            $elem['type'] = $col['type'];
            break;
            
        default:
            // look for 'select' and 'file' candidates
            $filters = $this->_getFilters($name);
            foreach ($filters as $filter) {
                // if there is a filter to 'validateInList' or
                // 'validateInKeys', make this a select element.
                if ($filter[0] == 'validateInKeys' || $filter[0] == 'validateInList') {
                    $elem['type'] = 'select';
                    break;
                }
                // if there is a filter to 'validateUpload', make this
                // a file element
                if ($filter[0] == 'validateUpload') {
                    $elem['type'] = 'file';
                    break;
                }
            }
            break;
        }
        
        // if type is still empty, make it text.
        if (! $elem['type']) {
            $elem['type'] = 'text';
        }
    }
    
    /**
     * 
     * Fixes the element label in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementLabel(&$elem, $name, $col)
    {
        if ($elem['label'] !== null) {
            return;
        }
        
        // if no label specified, set up based on element name
        $elem['label'] = $this->_model->locale(strtoupper("LABEL_$name"));
    }
    
    /**
     * 
     * Fixes the element description in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementDescr(&$elem, $name, $col)
    {
        if ($elem['descr'] !== null) {
            return;
        }
        
        // if no label specified, set up based on element name
        $elem['descr'] = $this->_model->locale(strtoupper("DESCR_$name"));
    }
    
    /**
     * 
     * Fixes the element value in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementValue(&$elem, $name, $col)
    {
        if ($elem['value'] !== null) {
            return;
        }
        
        $elem['value'] = $this->_getDefault($name);
    }
    
    /**
     * 
     * Fixes the element require-flag in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementRequire(&$elem, $name, $col)
    {
        if ($elem['require'] !== null) {
            return;
        }
        
        // require if the table says so
        if ($col['require']) {
            $elem['require'] = true;
            return;
        }
        
        // if there is a validateNotBlank filter, mark to require
        $filters = $this->_getFilters($name);
        foreach ($filters as $filter) {
            if ($filter[0] == 'validateNotBlank') {
                // mark as required, and done
                $elem['require'] = true;
                return;
            }
        }
    }
    
    /**
     * 
     * Fixes the element disable-flag in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementDisable(&$elem, $name, $col)
    {
        if ($elem['disable'] !== null) {
            return;
        }
        
        if ($col['primary']) {
            $elem['disable'] = true;
        }
    }
    
    /**
     * 
     * Fixes the element options in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementOptions(&$elem, $name, $col)
    {
        if ($elem['options'] !== null) {
            return;
        }
        
        // only fix options for certain element types
        $types = array('checkbox', 'select', 'radio');
        if (! in_array($elem['type'], $types)) {
            return;
        }
        
        // loop through the filters for the element to find a 'keys' or 'list'
        // validation
        $filters = $this->_getFilters($name);
        foreach ($filters as $filter) {
            $ok = $filter[0] == 'validateInKeys'
               || $filter[0] == 'validateInList';
               
            if ($ok) {
                $elem['options'] = $this->_autoOptions($filter[0], $filter[1]);
                break;
            }
        }
        
        // if still no options for checkboxes, set to (1,0)
        if (! $elem['options'] && $elem['type'] == 'checkbox') {
            $elem['options'] = array(1,0);
        }
    }
    
    /**
     * 
     * Fixes the element attribs in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementAttribs(&$elem, $name, $col)
    {
        // for text elements, set maxlength if none specified
        $fix_maxlength = $elem['type'] == 'text'
                      && empty($elem['attribs']['maxlength'])
                      && $col['size'] > 0;
        
        if ($fix_maxlength) {
            /** @todo Add +1 or +2 to 'size' for numeric types? */
            $elem['attribs']['maxlength'] = $col['size'];
        }
        
        // for text elements, set size
        $fix_size = $elem['type'] == 'text'
                 && empty($elem['attribs']['size']);
        
        if ($fix_size) {
            $elem['attribs']['size'] = $this->_config['default_text_size'];
        }
        
        // for textarea elements, fix rows
        $fix_rows = $elem['type'] == 'textarea'
                 && empty($elem['attribs']['rows']);
        
        if ($fix_rows) {
            $elem['attribs']['rows'] = $this->_config['default_textarea_rows'];
        }
        
        // for textarea elements, fix cols
        $fix_cols = $elem['type'] == 'textarea'
                 && empty($elem['attribs']['cols']);
        
        if ($fix_cols) {
            $elem['attribs']['cols'] = $this->_config['default_textarea_cols'];
        }
    }
    
    /**
     * 
     * Fixes the element filters in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementFilters(&$elem, $name, $col)
    {
        if (! $elem['filters'] === null) {
            return;
        }
        
        $elem['filters'] = array();
    }
    
    /**
     * 
     * Fixes the element invalid-messages in-place.
     * 
     * @param array &$elem The element to work with.
     * 
     * @param string $name The original element name (column name).
     * 
     * @param string $col The column definition used to inform the element.
     * 
     * @return void
     * 
     */
    protected function _fixElementInvalid(&$elem, $name, $col)
    {
        if (! $elem['invalid'] === null) {
            return;
        }
        
        $elem['invalid'] = array();
    }
    
    /**
     * 
     * Builds an option list from validateInKeys and validateInList values.
     * 
     * The 'validateInKeys' options are not changed.
     * 
     * The 'validateInList' options are generally sequential, so the label
     * and the value are made to be identical (based on the label).
     * 
     * @param string $type The validation type, 'validateInKeys' or 'validateInList'.
     * 
     * @param array $opts The options provided by the validation.
     * 
     * @return array
     * 
     */
    protected function _autoOptions($type, $opts)
    {
        // leave the labels and values alone
        if ($type == 'validateInKeys') {
            return $opts;
        }
        
        // make the form display the labels as both labels and values
        if ($type == 'validateInList') {
            $vals = array_values($opts);
            return array_combine($vals, $vals);
        }
    }
}
