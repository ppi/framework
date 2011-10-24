<?php
/**
 * 
 * Validates that a value is not exactly zero.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateNotZero.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateNotZero extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is not exactly zero.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateNotZero($value)
    {
        // reverse the blank-check so that empties are not
        // treated as zero.
        if ($this->_filter->getRequire() && $this->_filter->validateBlank($value)) {
            return false;
        }
        
        $zero = is_numeric($value) && $value == 0;
        return ! $zero;
    }
}