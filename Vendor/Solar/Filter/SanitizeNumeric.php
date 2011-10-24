<?php
/**
 * 
 * Sanitizes a value to a string with only numeric characters.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: SanitizeNumeric.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_SanitizeNumeric extends Solar_Filter_Abstract
{
    /**
     * 
     * Strips non-numeric characters from the value.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeNumeric($value)
    {
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // normal sanitize
        return (string) $this->_filter->sanitizeFloat($value);
    }
}