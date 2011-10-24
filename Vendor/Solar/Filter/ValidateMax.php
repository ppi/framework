<?php
/**
 * 
 * Validates that a value is less than than or equal to a maximum.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateMax.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateMax extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is less than than or equal to a maximum.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $max The maximum valid value.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMax($value, $max)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return is_numeric($value) && $value <= $max;
    }
}