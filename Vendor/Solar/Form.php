<?php
/**
 * 
 * Form-processing class; also hints the view on how to present the form.
 * 
 * @category Solar
 * 
 * @package Solar_Form Form processor with automated loading and presentation
 * hinting.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Form.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_Form extends Solar_Base
{
    // we use "success/failure" terminology rather than "valid/invalid"
    // terminology for a couple reasons.
    // 
    // (1) the form may be in a midway state, where validation has not been
    // applied yet; the form is neither valid nor invalid in that case.  
    // 
    // (2) form feedback usually indicates something more than whether or not 
    // the data are valid; the feedback is intended to show that (on success) 
    // some other process has been completed, e.g. saving to a database.
    const STATUS_SUCCESS = true;
    const STATUS_FAILURE = false;
    
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency request A Solar_Request dependency object.
     * 
     * @config array attribs An array of <form> tag attributes; used for hinting
     * the view on how to present the form.  Defaults are 'method="post"',
     * 'action="REQUEST_URI"', and 'enctype="multipart/form-data"'.
     * 
     * @config string success The overall "success" message when validating form
     * input. Default is Solar locale key SUCCESS_FORM.
     * 
     * @config string failure The overall "failure" message when validating form
     * input. Default is Solar locale key FAILURE_FORM.
     * 
     * @config dependency filter A Solar_Filter dependency injection; default is empty,
     *   which creates a standard Solar_Filter object on the fly.
     * 
     * @var array
     * 
     */
    protected $_Solar_Form = array(
        'request' => 'request',
        'filter'  => null,
        'success' => null,
        'failure' => null,
        'attribs' => array(),
    );
    
    /**
     * 
     * The validation status of the form.
     * 
     * @var bool Null if validation has not occurred yet, true if
     * valid, false if not valid.
     * 
     */
    protected $_status = null;
    
    /**
     * 
     * Default <form> tag attributes.
     * 
     * @var array
     * 
     */
    protected $_default_attribs = array(
        'action'  => null,
        'method'  => 'post',
        'enctype' => 'multipart/form-data',
    );
    
    /**
     * 
     * Attributes for the form tag itself.
     * 
     * The `$attribs` array holds HTML attributes for the
     * form itself (not for individual elements) such as
     * `action`, `method`, and `enctype`.  Note that these
     * are "hints" for the presentation of the form, and may not
     * be honored by the view.
     * 
     * @var array
     * 
     */
    public $attribs = array();
    
    /**
     * 
     * The array of elements in this form.
     * 
     * The `$elements` array contains all elements in the form,
     * including their names, types, values, any invalidation messages,
     * filter callbacks, and so on. 
     * 
     * In general, you should not try to set $elements yourself;
     * instead, Solar_Form::setElement() and Solar_Form::setElements().
     * 
     * @var array
     * 
     */
    public $elements = array();
    
    /**
     * 
     * Overall feedback about the state of the form.
     * 
     * The `$feedback` array stores feedback messages for
     * the form itself (not for individual elements). For example,
     * "Saved successfully." or "Please correct the noted errors."
     * Each array element is an additional feedback message.
     * 
     * Note that the $feedback property pertains to the form as a
     * whole, not the individual elements.  This is as opposed to
     * the 'invalid' key in each of the elements, which contains
     * invalidation messages specific to that element.
     * 
     * @var array
     * 
     */
    public $feedback = array();
    
    /**
     * 
     * Array of submitted values.
     * 
     * Populated on the first call to [[Solar_Form::_populate() | ]], which itself uses
     * [[Solar_Request::get()]] or [[Solar_Request::post()]], depending on
     * the value of $this->attribs['method'].
     * 
     * @var array
     * 
     * @todo Do we really need this as a property?
     * 
     */
    protected $_submitted = null;
    
    /**
     * 
     * Default values for each element.
     * 
     * `name`
     * : (string) The name attribute.
     * 
     * `type`
     * : (string) The input or type attribute ('text', 'select', etc).
     * 
     * `label`
     * : (string) A short label for the element.
     * 
     * `value`
     * : (string) The default or selected value(s) for the element.
     * 
     * `descr`
     * : (string) A longer description of the element, such as a tooltip
     *   or help text.
     * 
     * `status`
     * : (bool) Whether or not the particular element has passed or failed
     *   validation (true or false), or null if there has been no attempt at
     *   validation.
     * 
     * `require`
     * : (bool) Whether or not the element is required.
     * 
     * `disable`
     * : (bool) If disabled, the element is read-only (but is still
     *   submitted with other elements).
     * 
     * `options`
     * : (array) The list of allowed values as options for this element
     *   as an associative array in the form (value => label).
     * 
     * `attribs`
     * : (array) Additional XHTML attributes for the element in the
     *   form (attribute => value).
     * 
     * `invalid`
     * : (array) An array of messages if the value is invalid.
     * 
     * @var array
     * 
     */
    protected $_default_element = array(
        'name'    => null,
        'type'    => null,
        'label'   => null,
        'descr'   => null,
        'value'   => null,
        'status'  => null,
        'require' => false,
        'disable' => false,
        'options' => array(),
        'attribs' => array(),
        'filters' => array(),
        'invalid' => array(),
    );
    
    /**
     * 
     * A Solar_Filter object for filtering form values.
     * 
     * @var Solar_Filter
     * 
     * @see setFilterLocaleObj()
     * 
     */
    protected $_filter;
    
    /**
     * 
     * Request environment object.
     * 
     * @var Solar_Request
     * 
     */
    protected $_request;
    
    /**
     * 
     * Cross-site request forgery detector.
     * 
     * @var Solar_Csrf
     * 
     */
    protected $_csrf;
    
    /**
     * 
     * Sets the default success and failure messages.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        $this->_Solar_Form['success'] = $this->locale('SUCCESS_FORM');
        $this->_Solar_Form['failure'] = $this->locale('FAILURE_FORM');
    }
    
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
        
        // request environment
        $this->_request = Solar::dependency(
            'Solar_Request',
            $this->_config['request']
        );
        
        // filter object
        $this->_filter = Solar::dependency(
            'Solar_Filter',
            $this->_config['filter']
        );
        
        // csrf object
        $this->_csrf = Solar::factory('Solar_Csrf');
        
        // set the default action attribute
        $action = $this->_request->server('REQUEST_URI');
        $this->_default_attribs['action'] = $action;
        
        // reset everything
        $this->reset();
    }
    
    // -----------------------------------------------------------------
    // 
    // Element-management methods
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Sets one element in the form.  Appends if element does not exist.
     * 
     * @param string $name The element name to set or add; overrides
     * $info['name'].
     * 
     * @param array $info Element information using the same keys as
     * in Solar_Form::$_default_element.
     * 
     * @param string $array Rename the element as a key in this array.
     * 
     * @return void
     * 
     */
    public function setElement($name, $info, $array = null)
    {
        // prepare the name as an array key?
        $name = $this->_prepareName($name, $array);
        
        // make sure we have all the required keys.
        $info = array_merge($this->_default_element, $info);
        
        // forcibly cast each of the keys of the info array. do it this way
        // so that extra keys are not removed from the info array.
        $info['name']    = (string) $name;
        $info['type']    = (string) $info['type'];
        $info['label']   = (string) $info['label'];
        $info['value']   =          $info['value']; // mixed
        $info['descr']   = (string) $info['descr'];
        $info['require'] = (bool)   $info['require'];
        $info['disable'] = (bool)   $info['disable'];
        $info['options'] = (array)  $info['options'];
        $info['attribs'] = (array)  $info['attribs'];
        $info['filters'] = (array)  $info['filters'];
        $info['invalid'] = (array)  $info['invalid'];
        
        // retain info as an element.
        $this->elements[$name] = $info;
    }
    
    /**
     * 
     * Sets multiple elements in the form.  Appends if they do not exist.
     * 
     * @param array $list Element information as array(name => info), where
     * each info value is an array like Solar_Form::$_default_element.
     * 
     * @param string $array Rename each element as a key in this array.
     * 
     * @return void
     * 
     */
    public function setElements($list, $array = null)
    {
        foreach ((array) $list as $name => $info) {
            $this->setElement($name, $info, $array);
        }
    }
    
    /**
     * 
     * Gets multiple elements from this form.
     * 
     * @param string|array $spec If a string, return all elements with that
     * prefix (i.e., all elements in an array).  If an array, return that
     * specific list of elements.  If empty, return all elements.
     * 
     * @return array
     * 
     */
    public function getElements($spec = null)
    {
        // pre-emptively return all elements when there's no spec
        if (! $spec) {
            return $this->elements;
        }
        
        // the elements to return
        $list = array();
        
        // return only specific element names?
        if (is_array($spec)) {
            foreach ($spec as $name) {
                if (! empty($this->elements[$name])) {
                    $list[$name] = $this->elements[$name];
                }
            }
        }
        
        // return all elements of a specific prefix?
        if (is_string($spec)) {
            foreach($this->elements as $name => $info) {
                if (strpos($name, $spec) === 0) {
                    $list[$name] = $info;
                }
            }
        }
        
        // done!
        return $list;
    }
    
    /**
     * 
     * Sets the attributes of one element.
     * 
     * @param string $name The element name.
     * 
     * @param array $attribs Set these attribs on the element; the key is the
     * attribute name, and the value is the attribute value.
     * 
     * @param string $array Rename the element as a key in this array.
     * 
     * @return void
     * 
     */
    public function setAttribs($name, $attribs, $array = null)
    {
        // make sure the element exists
        $name = $this->_prepareName($name, $array);
        if (! empty($this->elements[$name])) {
            foreach ((array) $attribs as $key => $val) {
                $this->elements[$name]['attribs'][$key] = $val;
            }
        }
    }
    
    /**
     * 
     * Sets the type of one element.
     * 
     * @param string $name The element name.
     * 
     * @param string $type The element type ('text', 'select', etc).
     * 
     * @param string $array Rename the element as a key in this array.
     * 
     * @return void
     * 
     */
    public function setType($name, $type, $array = null)
    {
        $name = $this->_prepareName($name, $array);
        if (! empty($this->elements[$name])) {
            $this->elements[$name]['type'] = $type;
        }
    }
    
    /**
     * 
     * Reorders the existing elements.
     * 
     * @param array $list The order in which elements should be placed; each
     * value in the array is an element name.
     * 
     * @return void
     * 
     */
    public function orderElements($list)
    {
        // the set of elements as they are now
        $old = $this->elements;
        // reset the elements to blank
        $this->elements = array();
        // put the elements in the requested order
        foreach ((array) $list as $name) {
            if (isset($old[$name])) {
                $this->elements[$name] = $old[$name];
            }
        }
        // retain all remaining old elements
        foreach ($old as $name => $info) {
            $this->elements[$name] = $info;
        }
        // done!
    }
    
    /**
     * 
     * Tells the internal filter what object it should use for locale
     * translations.
     * 
     * @param Solar_Base $obj The object to use for locale translations.
     * 
     * @return void
     * 
     */
    public function setFilterLocaleObject($obj)
    {
        $this->_filter->setChainLocaleObject($obj);
    }
    
    /**
     * 
     * Adds one filter to an element.
     * 
     * If the added filter is a "validateNotBlank" filter, automatically sets
     * the "require" flag on the element to true.
     * 
     * @param string $name The element name.
     * 
     * @param array|string $spec The filter specification; either a
     * Solar_Filter method name (string), or an array where the first element
     * is a method name and remaining elements are parameters to that method.
     * 
     * @param string $array Rename the element as a key in this array.
     * 
     * @return void
     * 
     */
    public function addFilter($name, $spec, $array = null) 
    {
        // make sure the element exists
        $name = $this->_prepareName($name, $array);
        if (empty($this->elements[$name])) {
            throw $this->_exception('ERR_NO_SUCH_ELEMENT', array(
                'name' => $name,
            ));
        }
        
        // force the filter spec to an array
        $spec = (array) $spec;
        
        // add the filter spec to the element
        $this->elements[$name]['filters'][] = $spec;
        
        // if it's a "not-blank" filter, set the require flag
        if ($spec[0] == 'validateNotBlank') {
            $this->elements[$name]['require'] = true;
        }
    }
    
    /**
     * 
     * Adds many filters to one element.
     * 
     * @param string $name The element name.
     * 
     * @param array|string $list The list of filters for this element.
     * 
     * @param string $array Rename the element as a key in this array.
     * 
     * @return void
     * 
     */
    public function addFilters($name, $list, $array = null)
    {
        foreach ((array) $list as $spec) {
            $this->addFilter($name, $spec, $array);
        }
    }
    
    /**
     * 
     * Adds one or more invalid message to an element, sets the element status
     * to false (invalid), and sets the form status to false (invalid); if the
     * element does not exist, adds the message as form-level feedback.
     * 
     * @param string $name The element name.
     * 
     * @param string|array $spec The invalidation message(s).
     * 
     * @param string $array Rename each element as a key in this array.
     * 
     * @return void
     * 
     */
    public function addInvalid($name, $spec, $array = null)
    {
        // prepare the name as an array key
        $name = $this->_prepareName($name, $array);
        
        // mark the status of the form as a whole; do this first so that
        // the very first non-element invalid feedback does not get dropped.
        $this->setStatus(false);
        
        // does the element exist?
        if (empty($this->elements[$name])) {
            
            // no; add as messages as feedback on the form as a whole
            foreach ((array) $spec as $text) {
                $this->feedback[] = "$name: $text";
            }
            
        } else {
            
            // yes, add messages to the element itself
            foreach ((array) $spec as $text) {
                $this->elements[$name]['invalid'][] = $text;
            }
            
            // mark the status of the element
            $this->elements[$name]['status'] = false;
            
        }
    }
    
    /**
     * 
     * Adds invalidation messages to multiple elements, sets their status to
     * false (invalid) and sets the form status to false (invalid).
     * 
     * @param array $list An array where the key is the element name, and the
     * value is a string or array of invalidation messages for that element.
     * 
     * @param string $array Rename each element as a key in this array.
     * 
     * @return void
     * 
     */
    public function addInvalids($list, $array = null)
    {
        foreach ((array) $list as $name => $spec) {
            $this->addInvalid($name, $spec, $array);
        }
    }
    
    // -----------------------------------------------------------------
    // 
    // Value-management methods
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Manually set the value of one element.
     * 
     * Note that this is subtly different from [[Solar_Form::populate() | ]].
     * This method takes full name of the element, whereas populate() takes a 
     * "natural" hierarchical array like $_POST.
     * 
     * @param string $name The element name.
     * 
     * @param mixed $value Set the element to this value.
     * 
     * @param string $array Rename the element as a key in this array.
     * 
     * @return void
     * 
     */
    public function setValue($name, $value, $array = null)
    {
        // make sure the element exists
        $name = $this->_prepareName($name, $array);
        if (! empty($this->elements[$name])) {
            $this->elements[$name]['value'] = $value;
        }
        
    }
    
    /**
     * 
     * Manually set the value of several elements.
     * 
     * Note that this is subtly different from [[Solar_Form::populate() | ]]. 
     * This method takes a flat array or struct where the full name of the 
     * element is the key, as vs populate() which takes a "natural" 
     * hierarchical array like $_POST.
     * 
     * @param array|Solar_Struct $spec The data source to set values from.
     * 
     * @param string $array Rename each element as a key in this array.
     * 
     * @return void
     * 
     */
    public function setValues($spec, $array = null)
    {
        // we traverse through the elements, *not* the data keys, so that
        // Solar_Sql_Model_Record objects do not lazy-load items that are
        // not in the form.
        foreach ($this->elements as $name => &$element) {
            
            // are we looking inside a specific element array?
            if ($array) {
                // we have to find the non-array name version of the
                // element name.
                $find = '/^' . preg_quote($array, '/') . '\[(\w+)\]$/';
                if (preg_match($find, $name, $matches)) {
                    $key = $matches[1];
                } else {
                    // this element is not part of the array we need
                    continue;
                }
            } else {
                // not looking in an array, use the name as-is
                $key = $name;
            }
            
            // is the key set in the data spec?
            $isset = is_array($spec) && array_key_exists($key, $spec)
                  || $spec instanceof Solar_Struct && isset($spec->$key);
            
            if ($isset) {
                $element['value'] = $spec[$key];
            }
        }
    }
    
    /**
     * 
     * Automatically populates form elements with specified values.
     * 
     * @param array $submit The source data array for populating form
     * values as array(name => value); if null, will populate from POST
     * or GET vars as determined from the Solar_Form::$attribs['method']
     * value.
     * 
     * @return void
     * 
     */
    public function populate($submit = null)
    {
        $this->_submitted = array();
        $this->_status = null;
        
        // import the submitted values
        if (is_array($submit)) {
            // from an array
            $this->_submitted = $submit;
        } elseif (is_object($submit)) {
            // from an object
            $this->_submitted = (array) $submit;
        } else {
            // from GET or POST, per the form method.
            $method = strtolower($this->attribs['method']);
            if ($method == 'get' || $method == 'post') {
                $this->_submitted = $this->_request->$method();
            }
        }
        
        // populate the submitted values into the
        // elements themsevles.
        $this->_populate($this->_submitted);
    }
    
    /**
     * 
     * Applies the filter chain to the form element values; in particular,
     * checks validation and updates the 'invalid' keys for each element that
     * fails, and checks for CSRF attempts automatically.
     * 
     * This method cycles through each element in the form, where it ...
     * 
     * 1. Applies the filters to populated user input for the element,
     * 
     * 2. Validates the filtered value against the validation rules for the element,
     * 
     * 3. Adds invalidation messages to the element if it does not pass validation.
     * 
     * If all populated values pass validation, the method returns boolean
     * true, indicating the form as a whole it valid; if even one validation on
     * one element fails, the method returns boolean false.
     * 
     * In general, you should only validate the values after user input has
     * been populated with [[Solar_Form::populate()]].
     * 
     * Note that filters and validation rules are added with the
     * [[Solar_Form::setElement()]] and [[Solar_Form::setElements()]] methods;
     * please see those pages for more information on how to add filters and
     * validation to an element.
     * 
     * @return bool True if all elements are valid, false if not.
     * 
     */
    public function validate()
    {
        // reset the filter chain so we can rebuild it
        $this->_filter->resetChain();
        
        // build the filter chain and data values. note that the foreach()
        // loop uses an info **reference**, not a copy.
        $data = array();
        foreach ($this->elements as $name => &$info) {
            // keep a **reference** to the data (not a copy)
            $data[$name] = &$info['value'];
            
            // set the filters and require-flag, reference not needed
            $this->_filter->addChainFilters($name, $info['filters']);
            $this->_filter->setChainRequire($name, $info['require']);
        }
        
        // apply the filter chain to the data, which will modify the 
        // element data in place because of the references
        $status = $this->_filter->applyChain($data);
        $this->setStatus($status);
        
        // retain any invalidation messages
        $invalid = $this->_filter->getChainInvalid();
        foreach ((array) $invalid as $key => $val) {
            $this->addInvalid($key, $val);
        }
        
        // check for csrf attempts
        if ($this->_csrf->isForgery()) {
            // looks like a forgery: validation failure
            $this->feedback[] = 'ERR_CSRF_ATTEMPT';
            $this->setStatus(false);
        }
        
        // done!
        return $this->_status;
    }
    
    /**
     * 
     * Returns the form element values as an array.
     * 
     * @param string $key Return only values that are part of
     * this array key.  If null, returns all values in the
     * form.
     * 
     * @return array An associative array of element values.
     * 
     */
    public function getValues($key = null)
    {
        $values = array();
        foreach ($this->elements as $name => $elem) {
            $this->_values($name, $elem['value'], $values);
        }
        
        if (! $key) {
            return $values;
        }
        
        if (! empty($values[$key])) {
            return $values[$key];
        }
    }
    
    /**
     * 
     * Returns one form element value.
     * 
     * @param string $key The element key, including the array name (if any).
     * 
     * @return mixed The element value.
     * 
     */
    public function getValue($key)
    {
        if (array_key_exists($key, $this->elements)) {
            return $this->elements[$key]['value'];
        }
    }
    
    // -----------------------------------------------------------------
    // 
    // General-purpose methods
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Resets the form object to its originally-configured state, and adds
     * an anti-CSRF element with the current value of the session token.
     * 
     * This clears out all elements, filters, validations, and feedback,
     * as well as all submitted values.  Use this method to "start over
     * again" using the same form object.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        // attribs should be the default set, plus config overrides
        $this->attribs = array_merge(
            $this->_default_attribs,
            $this->_config['attribs']
        );
        
        $this->elements   = array();
        $this->feedback   = array();
        $this->_submitted = null;
        
        // add the csrf token value if present
        if ($this->_csrf->hasToken()) {
            $name = $this->_csrf->getKey();
            $this->setElement($name, array(
                'type'  => 'hidden',
                'value' => $this->_csrf->getToken(),
            ));
        }
    }
    
    /**
     * 
     * Forcibly sets the overall form validation status.
     * 
     * Does not set individual element status values.
     * 
     * @param bool $status Solar_Form::STATUS_SUCCESS if you want to say the 
     * form as a whole is valid, Solar_Form::STATUS_FAILURE if you want to say
     * the form as a whole is is invalid.
     * 
     * @return void
     * 
     */
    public function setStatus($status)
    {
        // only allow certain statuses
        $allowed = array(
            Solar_Form::STATUS_SUCCESS,
            Solar_Form::STATUS_FAILURE,
            null,
        );
        
        if (! in_array($status, $allowed)) {
            throw $this->_exception('ERR_STATUS_NOT_ALLOWED', array(
                'status' => (string) $status,
            ));
        }
        
        // no operation if status does not change
        if ($this->_status === $status) {
            return;
        }
        
        // reset feedback when we change from one status to another
        if ($status === null) {
            $this->feedback = array();
        } elseif ($status) {
            $this->feedback = array($this->_config['success']);
        } else {
            $this->feedback = array($this->_config['failure']);
        }
        
        // set the status to the new value
        $this->_status = $status;
    }
    
    /**
     * 
     * Gets the current overall form validation status.
     * 
     * @return bool True if valid, false if not valid, null if validation
     * has not been attempted.
     * 
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    /**
     * 
     * Has the current form been successfully validated?
     * 
     * Note that if validation has not been attempted, this will return false.
     * 
     * @return bool
     * 
     */
    public function isSuccess()
    {
        return $this->_status === Solar_Form::STATUS_SUCCESS;
    }
    
    /**
     * 
     * Has the current form failed validation?
     * 
     * Note that if validation has not been attempted, this will return false.
     * 
     * @return bool
     * 
     */
    public function isFailure()
    {
        return $this->_status === Solar_Form::STATUS_FAILURE;
    }
    
    /**
     * 
     * Loads form attributes and elements from an external source.
     * 
     * You can pass an arbitrary number of parameters to this method;
     * all params after the first will be passed as arguments to the
     * fetch() method of the loader class.
     * 
     * The loader class itself must have at least one method, fetch(),
     * that returns an associative array with keys 'attribs' and 
     * 'elements' which contain, respectively, values for $this->attribs
     * and $this->setElements().
     * 
     * {{code: php
     *     $form = Solar::factory('Solar_Form');
     *     $form->load('Solar_Form_Load_Xml', '/path/to/form.xml');
     * }}
     * 
     * @param string|object $obj If a string, it is treated as a class
     * name to instantiate with [[Solar::factory()]]; if an object, it is
     * used as-is.  Either way, the fetch() method of the object will
     * be called to populate this form (via $this->attribs property and
     * the $this->setElements() method).
     * 
     * @return void
     * 
     */
    public function load($obj)
    {
        // if the first param is a string class name
        // try to instantiate it.
        if (is_string($obj)) {
            $obj = Solar::factory($obj);
        }
        
        // if we *still* don't have an object, or if there's no
        // fetch() method, there's a problem.
        if (! is_object($obj) || ! is_callable(array($obj, 'fetch'))) {
            throw $this->_exception('ERR_METHOD_NOT_CALLABLE', array(
                'method' => 'fetch',
            ));
        }
        
        // get any additional arguments to pass to the fetch
        // method (after dropping the first param) ...
        $args = func_get_args();
        array_shift($args);
        
        // ... and call the fetch method.
        $info = call_user_func_array(
            array($obj, 'fetch'),
            $args
        );
        
        // we don't call reset() because there are
        // sure to be cases when you need to load()
        // more than once to get a full form.
        $this->_load($info);
    }
    
    /**
     * 
     * Adds attribs and elements from the loader results into this form.
     * 
     * @param array $info Attribs and elements info.
     * 
     * @return void
     * 
     */
    protected function _load($info)
    {
        // merge the loaded attribs onto the current ones.
        $this->attribs = array_merge(
            $this->attribs,
            $info['attribs']
        );
        
        // add elements, overwriting existing ones with the same names
        // (no way around this, I'm afraid).
        $this->setElements($info['elements']);
    }
    
    
    // -----------------------------------------------------------------
    //
    // Support methods
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Prepares a name as an array key, if needed.
     * 
     * @param string $name The element name.
     * 
     * @param string $array The array name, if any, into which we place
     * the element.
     * 
     * @return string The prepared element name.
     * 
     */
    protected function _prepareName($name, $array = null)
    {
        if ($array) {
            $pos = strpos($name, '[');
            if ($pos === false) {
                // name is not itself an array.
                // for example, 'field' becomes 'array[field]'
                $name = $array . "[$name]";
            } else {
                // the name already has array keys, for example
                // 'field[0]'. make the name just another key
                // in the array, for example 'array[field][0]'.
                $name = $array . '[' .
                    substr($name, 0, $pos) . ']' .
                    substr($name, $pos);
            }
        }
        return $name;
    }
    
    /**
     * 
     * Recursive method to map the submitted values into elements.
     * 
     * @param array|string $src The source data populating form
     * values.  If an array, will recursively descend into the array;
     * if a scalar, will map the value into a form element.
     * 
     * @param string $name The name of the current element mapped from
     * the array of submitted values.  For example, $src['foo']['bar']['baz']
     * maps to "foo[bar][baz]".
     * 
     * @return void
     * 
     */
    protected function _populate($src, $name = null)
    {
        // are we working with an array?
        if (is_array($src)) {
            
            // sequential arrays are generally multiple-select items.
            // only check the first key on the array.
            $is_sequential = is_int(key($src));
            
            // temporal values may also be expressed as arrays
            $types = array('date', 'time', 'timestamp');
            $is_temporal = isset($this->elements[$name]) &&
                           in_array($this->elements[$name]['type'], $types);
            
            // retain value as-is, or descend through sub-elements?
            if ($is_sequential || $is_temporal) {
                // retain value as-is (no recursive descent)
                $this->elements[$name]['value'] = $src;
            } else {
                // not sequential, not temporal. descend through each of the
                // sub-elements.
                foreach ($src as $key => $val) {
                    $sub = empty($name) ? $key : $name . "[$key]";
                    $this->_populate($val, $sub);
                }
            }
            
        } elseif (isset($this->elements[$name])) {
            
            // convenient reference
            $elem =& $this->elements[$name];
            
            // do not populate certain elements, as this will
            // reset their value inappropriately.
            $skip = $elem['type'] == 'submit' ||
                    $elem['type'] == 'button' ||
                    $elem['type'] == 'reset';
                    
            if ($skip) {
                return;
            }
            
            // is this a multiple select?
            $multiple = $elem['type'] == 'select' &&
                        ! empty($elem['attribs']['multiple']);
            
            // set the value appropriately
            if ($multiple && ! $src) {
                // empty on a multiple.  force it to an empty array.
                // (merely casting to array gets us an array with one
                // empty-string value.)
                $elem['value'] = array();
            } else {
                $elem['value'] = $src;
            }
        }
    }
    
    /**
     * 
     * Recursively pulls values from elements into an associative array.
     * 
     * @param string $key The current array key for the values array.  If
     * this has square brackets in it, that's a sign we need to keep creating
     * sub-elements for the values array.
     * 
     * @param mixed $val The element value to put into the values array, once
     * we stop creating sub-elements based on the element name.
     * 
     * @param array &$values The values array into which we will place the
     * element value.  Note that it becomes a reference to sub-elements as
     * the recursive function creates new sub-elements based on the form
     * element name.
     * 
     * @return void
     * 
     */
    protected function _values($key, $val, &$values)
    {
        if (strpos($key, '[') === false) {
        
            // no '[' in the key, so we're at the end
            // of any recursive descent; capture the value.
            if (empty($key)) {
                // handles elements named as auto-append arrays '[]'
                $values[] = $val;
            } else {
                $values[$key] = $val;
            }
            return;
            
        } else {
        
            // recursively parse the element name ($key) to create an
            // array-key for its value.
            // 
            // $key is something like "foo[bar][baz]".
            // 
            // 0123456789012
            // foo[bar][baz]
            // 
            // find the first '['.
            $pos = strpos($key, '[');
            
            // the part before the '[' is the new value key
            $new = substr($key, 0, $pos);
            
            // the part after the '[' still needs to be processed
            $key = substr($key, $pos+1);
            
            // create $values['foo'] if it does not exist.
            if (! isset($values[$new])) {
                $values[$new] = null;
            }
            
            // now $key is something like "bar][baz]".
            // 
            // 012345678
            // bar][baz]
            // 
            // remove the first remaining ']'.  this should leave us
            // with 'bar[baz]'.
            $pos = strpos($key, ']');
            $key = substr_replace($key, '', $pos, 1);
            
            
            // continue to descend,
            // but relative to the new value array.
            $this->_values($key, $val, $values[$new]);
        }
    }
}
