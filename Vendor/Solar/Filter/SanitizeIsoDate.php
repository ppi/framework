<?php
/**
 * 
 * Sanitizes a value to an ISO-8601 date.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: SanitizeIsoDate.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_SanitizeIsoDate extends Solar_Filter_SanitizeIsoTimestamp
{
    /**
     * 
     * Forces the value to an ISO-8601 formatted date ("yyyy-mm-dd").
     * 
     * @param string $value The value to be sanitized.  If an integer, it
     * is used as a Unix timestamp; otherwise, converted to a Unix
     * timestamp using [[php::strtotime() | ]].
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeIsoDate($value)
    {
        // look for Ymd keys?
        if (is_array($value)) {
            $value = $this->_arrayToDate($value);
        }
        
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // normal sanitize
        $format = 'Y-m-d';
        if (is_int($value)) {
            return date($format, $value);
        } else {
            return date($format, strtotime($value));
        }
    }
}