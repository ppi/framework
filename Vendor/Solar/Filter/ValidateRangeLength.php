<?php
/**
 * 
 * Validates that a value's length is within a given range.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateRangeLength.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateRangeLength extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the length of the value is within a given range.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The minimum valid length.
     * 
     * @param mixed $max The maximum valid length.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateRangeLength($value, $min, $max)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        $len = strlen($value);
        return ($len >= $min && $len <= $max);
    }
}