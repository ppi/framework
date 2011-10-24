<?php
/**
 * 
 * Validates that a value **is not** a key in the list of allowed options.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateNotInKeys.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateNotInKeys extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that a value **is not** a key in the list of allowed
     * options.
     * 
     * Given an array (second parameter), the value (first parameter) must not
     * match any the array keys.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $array An array of disallowed options.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateNotInKeys($value, $array)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return ! array_key_exists($value, (array) $array);
    }
}