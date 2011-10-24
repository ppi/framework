<?php
/**
 * 
 * Sanitizes a value to a string using preg_replace().
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: SanitizePregReplace.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_SanitizePregReplace extends Solar_Filter_Abstract
{
    /**
     * 
     * Applies [[php::preg_replace() | ]] to the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @param string $pattern The regex pattern to apply.
     * 
     * @param string $replace Replace the found pattern with this string.
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizePregReplace($value, $pattern, $replace)
    {
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // normal sanitize
        return preg_replace($pattern, $replace, $value);
    }
}