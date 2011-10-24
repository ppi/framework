<?php
/**
 * 
 * Validates that a value is a boolean representation.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateBool.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateBool extends Solar_Filter_Abstract
{
    /**
     * 
     * String representations of "true" boolean values.
     * 
     * @var array
     * 
     */
    protected $_true = array('1', 'on', 'true', 't', 'yes', 'y');
    
    /**
     * 
     * String representations of "false" boolean values.
     * 
     * @var array
     * 
     */
    protected $_false = array('0', 'off', 'false', 'f', 'no', 'n', '');
    
    /**
     * 
     * Validates that the value is a boolean representation.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateBool($value)
    {
        // need to allow for blanks if not required, because
        // empty strings are boolean false, and strings composed of blanks
        // are boolean true.
        if ($this->_filter->validateBlank($value) && ! $this->_filter->getRequire()) {
            return true;
        }
        
        // PHP booleans
        if ($value === true || $value === false) {
            return true;
        }
        
        // "string" booleans
        $value = strtolower(trim($value));
        if (in_array($value, $this->_true, true) ||
            in_array($value, $this->_false, true)) {
            return true;
        }
        
        return false;
    }
}