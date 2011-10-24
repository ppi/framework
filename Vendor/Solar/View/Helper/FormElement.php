<?php
/**
 * 
 * Abstract helper for form elements.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormElement.php 3409 2008-09-20 18:04:17Z pmjones $
 * 
 */
abstract class Solar_View_Helper_FormElement extends Solar_View_Helper {
    
    /**
     * 
     * Default form element information.
     * 
     * @var array
     * 
     */
    protected $_info = array(
        'type'    => '',
        'name'    => '',
        'value'   => '',
        'label'   => '',
        'attribs' => '',
        'options' => array(),
        'require' => false,
        'disable' => false,
        'invalid' => array(),
    );
    
    /**
     * 
     * The order in which to process info keys.
     * 
     * Attribs is last so that attributes are unset properly.
     * 
     * @var array
     * 
     */
    protected $_keys = array(
        'type',
        'name',
        'value',
        'label',
        'options',
        'require',
        'disable',
        'invalid',
        'attribs',
    );
    
    /**
     * 
     * The form element type (text, radio, etc).
     * 
     * @var string
     * 
     */
    protected $_type;
    
    /**
     * 
     * The form element name.
     * 
     * @var string
     * 
     */
    protected $_name;
    
    
    /**
     * 
     * The form element value.
     * 
     * @var string
     * 
     */
    protected $_value;
    
    /**
     * 
     * The form element label.
     * 
     * @var string
     * 
     */
    protected $_label;
    
    /**
     * 
     * The form element attributes (checked, selected, readonly, etc).
     * 
     * @var array
     * 
     */
    
    protected $_attribs;
    
    /**
     * 
     * Options for checkbox, select, and radio elements.
     * 
     * @var array
     * 
     */
    protected $_options;
    
    /**
     * 
     * Whether or not the element is required.
     * 
     * @var bool
     * 
     */
    protected $_require;
    
    /**
     * 
     * Whether or not the element is to be disabled.
     * 
     * @var bool
     * 
     */
    protected $_disable;
    
    /**
     * 
     * Feedback messages for the element.
     * 
     * @var bool
     * 
     */
    protected $_invalid;
    
    /**
     * 
     * Prepares an info array and imports to the properties.
     * 
     * @param array $info An array of element information.
     * 
     * @return void
     * 
     */
    protected function _prepare($info)
    {
        $info = array_merge($this->_info, $info);
        
        settype($info['type'], 'string');
        settype($info['name'], 'string');
        settype($info['label'], 'string');
        settype($info['attribs'], 'array');
        settype($info['options'], 'array');
        settype($info['require'], 'bool');
        settype($info['disable'], 'bool');
        settype($info['invalid'], 'array');
        
        foreach ($this->_keys as $key) {
            unset($info['attribs'][$key]);
            $prop = "_$key";
            $this->$prop = $info[$key];
        }
    }
}
