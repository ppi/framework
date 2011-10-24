<?php
/**
 * 
 * Validates that a value is blank (null, empty string, or string of only 
 * whitespace characters).
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateBlank.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateBlank extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is null, or is a string composed only of
     * whitespace.
     * 
     * Non-strings and non-nulls never validate as blank; this includes
     * integers, floats, numeric zero, boolean true and false, any array with
     * zero or more elements, and all objects and resources.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateBlank($value)
    {
        if (! is_string($value) && ! is_null($value)) {
            return false;
        }
        
        return trim($value) == '';
    }
}