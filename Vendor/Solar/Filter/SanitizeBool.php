<?php
/**
 * 
 * Sanitizes a value to boolean true or false.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: SanitizeBool.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_SanitizeBool extends Solar_Filter_Abstract
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
     * Forces the value to a boolean.
     * 
     * Note that this recognizes $this->_true and $this->_false values.
     * 
     * @param mixed $value The value to sanitize.
     * 
     * @return bool The sanitized value.
     * 
     */
    public function sanitizeBool($value)
    {
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // PHP booleans
        if ($value === true || $value === false) {
            return $value;
        }
        
        // "string" booleans
        $value = strtolower(trim($value));
        if (in_array($value, $this->_true)) {
            return true;
        }
        if (in_array($value, $this->_false)) {
            return false;
        }
        
        // forcibly recast to a boolean
        return (bool) $value;
    }
}