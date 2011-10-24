<?php
/**
 * 
 * Validates that a value is of a certain ctype.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateCtype.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateCtype extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates the value against a [[php::ctype | ]] function.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $type The ctype to validate against: 'alnum',
     * 'alpha', 'digit', etc.
     * 
     * @return bool True if the value matches the ctype, false if not.
     * 
     */
    public function validateCtype($value, $type)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        $func = 'ctype_' . $type;
        return (bool) $func((string)$value);
    }
}