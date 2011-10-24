<?php
/**
 * 
 * Validates that a value is an ISO 8601 date string.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateIsoDate.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateIsoDate extends Solar_Filter_ValidateIsoTimestamp
{
    /**
     * 
     * Validates that the value is an ISO 8601 date string.
     * 
     * The format is "yyyy-mm-dd".  Also checks to see that the date
     * itself is valid (for example, no Feb 30).
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIsoDate($value)
    {
        // look for Ymd keys?
        if (is_array($value)) {
            $value = $this->_arrayToDate($value);
        }
        
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        // basic date format
        // yyyy-mm-dd
        $expr = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D';
        
        // validate
        if (preg_match($expr, $value, $match) &&
            checkdate($match[2], $match[3], $match[1])) {
            return true;
        } else {
            return false;
        }
    }
}