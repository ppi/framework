<?php
/**
 * 
 * Validates that a value is a locale code.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateLocaleCode.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateLocaleCode extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is a locale code.
     * 
     * The format is two lower-case letters, an underscore, and two upper-case
     * letters.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateLocaleCode($value)
    {
        $expr = '/^[a-z]{2}_[A-Z]{2}$/D';
        return $this->_filter->validatePregMatch($value, $expr);
    }
}