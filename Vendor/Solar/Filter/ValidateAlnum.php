<?php
/**
 * 
 * Validates that the value is only letters (upper or lower case) and digits.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateAlnum.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateAlnum extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is only letters (upper or lower case) and digits.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateAlnum($value)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return ctype_alnum((string)$value);
    }
}