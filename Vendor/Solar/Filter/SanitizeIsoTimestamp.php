<?php
/**
 * 
 * Sanitizes a value to an ISO-8601 timestamp.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: SanitizeIsoTimestamp.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_SanitizeIsoTimestamp extends Solar_Filter_Abstract
{
    /**
     * 
     * Forces the value to an ISO-8601 formatted timestamp using a space
     * separator ("yyyy-mm-dd hh:ii:ss") instead of a "T" separator.
     * 
     * @param mixed $value The value to be sanitized.  If an integer, it
     * is used as a Unix timestamp; otherwise, converted to a Unix
     * timestamp using [[php::strtotime() | ]].  If an array, and it has *all*
     * the keys for `Y, m, d, h, i, s`, then the array is converted into
     * an ISO 8601 string before sanitizing.
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeIsoTimestamp($value)
    {
        // look for YmdHis keys?
        if (is_array($value)) {
            $value = $this->_arrayToTimestamp($value);
        }
        
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // normal sanitize
        $format = 'Y-m-d H:i:s';
        if (is_int($value)) {
            return date($format, $value);
        } else {
            return date($format, strtotime($value));
        }
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