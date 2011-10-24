<?php
/**
 * 
 * Abstract class for filters, both 'sanitize' and  'validate'.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Abstract.php 4263 2009-12-07 19:25:31Z pmjones $
 * 
 */
abstract class Solar_Filter_Abstract extends Solar_Base {
    
    /**
     * 
     * Default configuration values.
     * 
     * @config Solar_Filter filter The "parent" Solar_Filter object.
     * 
     * @var array
     * 
     */
    protected $_Solar_Filter_Abstract = array(
        'filter' => null,
    );
    
    /**
     * 
     * The "parent" filter object.
     * 
     * @var Solar_Filter
     * 
     */
    protected $_filter;
    
    /**
     * 
     * The locale key to use when a value is invalid.
     * 
     * @var string
     * 
     */
    protected $_invalid;
    
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
        $this->_filter = $this->_config['filter'];
        $this->_resetInvalid();
    }
    
    /**
     * 
     * Returns the value of the $_invalid property.
     * 
     * @return string
     * 
     */
    public function getInvalid()
    {
        return $this->_invalid;
    }
    
    /**
     * 
     * Resets the $_invalid property to its default value.
     * 
     * For all non-validate classes, the value is null.
     * 
     * For a class ValidateFooBar, the value is "INVALID_FOO_BAR".
     * 
     * @return void
     * 
     */
    protected function _resetInvalid()
    {
        $parts = explode('_', get_class($this));
        $name = end($parts);
        if (substr($name, 0, 8) != 'Validate') {
            // skip it, sanitizers don't use error messages.
            $this->_invalid = null;
            return;
        }
        
        // 'validateFooBar' => 'invalidFooBar'
        $name = 'invalid' . substr($name, 8);
        
        // 'invalidFoobar' => 'INVALID_FOO_BAR'
        $name = strtoupper(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
        
        // keep it
        $this->_invalid = $name;
    }
    
    /**
     * 
     * Sets $this->_invalid to the specified value and returns false.
     * 
     * @param string $key A locale key for $this->_invalid.  If empty, leaves
     * $this->_invalid as it it.
     * 
     * @return false
     * 
     */
    protected function _invalid($key = null)
    {
        if ($key) {
            $this->_invalid = $key;
        }
        return false;
    }
}