<?php
/**
 * 
 * Validates that a value is an ISO 8601 timestamp string.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateIsoTimestamp.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateIsoTimestamp extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is an ISO 8601 timestamp string.
     * 
     * The format is "yyyy-mm-ddThh:ii:ss" (note the literal "T" in the
     * middle, which acts as a separator -- may also be a space). As an
     * alternative, the value may be an array with all of the keys for
     * `Y, m, d, H, i`, and optionally `s`, in which case the value is
     * converted to an ISO 8601 string before validating it.
     * 
     * Also checks that the date itself is valid (for example, no Feb 30).
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIsoTimestamp($value)
    {
        // look for YmdHis keys?
        if (is_array($value)) {
            $value = $this->_arrayToTimestamp($value);
        }
        
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        // correct length?
        if (strlen($value) != 19) {
            return false;
        }
        
        // valid date?
        $date = substr($value, 0, 10);
        if (! $this->_filter->validateIsoDate($date)) {
            return false;
        }
        
        // valid separator?
        $sep = substr($value, 10, 1);
        if ($sep != 'T' && $sep != ' ') {
            return false;
        }
        
        // valid time?
        $time = substr($value, 11, 8);
        if (! $this->_filter->validateIsoTime($time)) {
            return false;
        }
        
        // must be ok
        return true;
    }
    
    /**
     * 
     * Converts an array of timestamp parts to a string timestamp.
     * 
     * @param array $array The array of timestamp parts.
     * 
     * @return string
     * 
     */
    protected function _arrayToTimestamp($array)
    {
        $value = $this->_arrayToDate($array)
               . ' '
               . $this->_arrayToTime($array);
               
        return trim($value);
    }
    
    /**
     * 
     * Converts an array of date parts to a string date.
     * 
     * @param array $array The array of date parts.
     * 
     * @return string
     * 
     */
    protected function _arrayToDate($array)
    {
        $date = array_key_exists('Y', $array) &&
                trim($array['Y']) != '' &&
                array_key_exists('m', $array) &&
                trim($array['m']) != '' &&
                array_key_exists('d', $array) &&
                trim($array['d']) != '';
              
        if (! $date) {
            return;
        }
        
        return $array['Y'] . '-'
             . $array['m'] . '-'
             . $array['d'];
    }
    
    /**
     * 
     * Converts an array of time parts to a string time.
     * 
     * @param array $array The array of time parts.
     * 
     * @return string
     * 
     */
    protected function _arrayToTime($array)
    {
        $time = array_key_exists('H', $array) &&
                trim($array['H']) != '' &&
                array_key_exists('i', $array) &&
                trim($array['i']) != '';
              
        if (! $time) {
            return;
        }
        
        $s = array_key_exists('s', $array) && trim($array['s']) != ''
           ? $array['s']
           : '00';
        
        return $array['H'] . ':'
             . $array['i'] . ':'
             . $s;
    }
}