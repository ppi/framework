<?php
/**
 * 
 * Validates that a value represents an integer.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateInt.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateInt extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value represents an integer.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateInt($value)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        if (is_int($value)) {
            return true;
        }
        
        // otherwise, must be numeric, and must be same as when cast to int
        return is_numeric($value) && $value == (int) $value;
    }
}