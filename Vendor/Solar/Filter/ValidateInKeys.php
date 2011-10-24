<?php
/**
 * 
 * Validates that the value is a key in the list of allowed options.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateInKeys.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateInKeys extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is a key in the list of allowed options.
     * 
     * Given an array (second parameter), the value (first parameter) must 
     * match at least one of the array keys.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $array An array of allowed options.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateInKeys($value, $array)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return array_key_exists($value, (array) $array);
    }
}