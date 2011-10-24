<?php
/**
 * 
 * Validates that a value is an ISO 8601 time string.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateIsoTime.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateIsoTime extends Solar_Filter_ValidateIsoTimestamp
{
    /**
     * 
     * Validates that the value is an ISO 8601 time string (hh:ii::ss format).
     * 
     * As an alternative, the value may be an array with all of the keys for
     * `H`, `i`, and optionally `s`, in which case the value is
     * converted to an ISO 8601 string before validating it.
     * 
     * Per note from Chris Drozdowski about ISO 8601, allows two
     * midnight times ... 00:00:00 for the beginning of the day, and
     * 24:00:00 for the end of the day.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIsoTime($value)
    {
        // look for His keys?
        if (is_array($value)) {
            $value = $this->_arrayToTime($value);
        }
        
        $expr = '/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/D';
        
        return $this->_filter->validatePregMatch($value, $expr) ||
               ($value == '24:00:00');
    }
}