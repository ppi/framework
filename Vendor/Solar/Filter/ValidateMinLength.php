<?php
/**
 * 
 * Validates that a value is at least a certain length.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateMinLength.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateMinLength extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that a string is at least a certain length.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The value must have at least this many
     * characters.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMinLength($value, $min)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return strlen($value) >= $min;
    }
}