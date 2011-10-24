<?php
/**
 * 
 * Sanitizes a value to a string using trim().
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: SanitizeTrim.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_SanitizeTrim extends Solar_Filter_Abstract
{
    /**
     * 
     * Trims characters from the beginning and end of the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param string $chars Trim these characters (default space).
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeTrim($value, $chars = ' ')
    {
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // normal sanitize
        return trim($value, $chars);
    }
}