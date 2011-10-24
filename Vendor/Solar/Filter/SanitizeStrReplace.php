<?php
/**
 * 
 * Sanitizes a value to a string using str_replace().
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: SanitizeStrReplace.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_SanitizeStrReplace extends Solar_Filter_Abstract
{
    /**
     * 
     * Applies [[php::str_replace() | ]] to the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param string|array $search Find this string.
     * 
     * @param string|array $replace Replace with this string.
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeStrReplace($value, $search, $replace)
    {
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // normal sanitize
        return str_replace($search, $replace, $value);
    }
}