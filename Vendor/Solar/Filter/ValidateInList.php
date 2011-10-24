<?php
/**
 * 
 * Validates that a value is in a list of allowed values.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateInList.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateInList extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is in a list of allowed values.
     * 
     * Strict checking is enforced, so a string "1" is not the same as
     * an integer 1.  This helps to avoid matching 0 and empty, etc.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $array An array of allowed values.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateInList($value, $array)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return in_array($value, (array) $array, true);
    }
}