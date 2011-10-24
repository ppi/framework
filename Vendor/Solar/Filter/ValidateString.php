<?php
/**
 * 
 * Validates that a value can be represented as a string.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateString.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateString extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value can be represented as a string.
     * 
     * Essentially, this means any scalar value is valid (no arrays, objects,
     * resources, etc).
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateString($value)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return is_scalar($value);
    }
}