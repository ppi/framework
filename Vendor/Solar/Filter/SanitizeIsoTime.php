<?php
/**
 * 
 * Sanitizes a value to an ISO-8601 time.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: SanitizeIsoTime.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_SanitizeIsoTime extends Solar_Filter_SanitizeIsoTimestamp
{
    /**
     * 
     * Forces the value to an ISO-8601 formatted time ("hh:ii:ss").
     * 
     * @param string $value The value to be sanitized.  If an integer, it
     * is used as a Unix timestamp; otherwise, converted to a Unix
     * timestamp using [[php::strtotime() | ]].
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeIsoTime($value)
    {
        // look for His keys?
        if (is_array($value)) {
            $value = $this->_arrayToTime($value);
        }
        
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // normal sanitize
        $format = 'H:i:s';
        if (is_int($value)) {
            return date($format, $value);
        } else {
            return date($format, strtotime($value));
        }
    }
}