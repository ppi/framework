<?php
/**
 * 
 * Validates that a value **is not** in a list of disallowed values.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateNotInList.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateNotInList extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that a value **is not** in a list of disallowed values.
     * 
     * Strict checking is enforced, so a string "1" is not the same as
     * an integer 1.  This helps to avoid matching 0 and empty, etc.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $array An array of disallowed values.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateNotInList($value, $array)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return ! in_array($value, (array) $array, true);
    }
}