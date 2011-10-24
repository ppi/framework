<?php
/**
 * 
 * Validates that a value is no longer than a certain length.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateMaxLength.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateMaxLength extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that a string is no longer than a certain length.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $max The value must have no more than this many
     * characters.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMaxLength($value, $max)
    {
        // reverse the normal check for blankness so that blank strings
        // are not checked for length.
        if ($this->_filter->getRequire() && $this->_filter->validateBlank($value)) {
            return false;
        }
        
        return strlen($value) <= $max;
    }
}