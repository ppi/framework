<?php
/**
 * 
 * Sanitizes a value to an integer.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: SanitizeInt.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_SanitizeInt extends Solar_Filter_Abstract
{
    /**
     * 
     * Forces the value to an integer.
     * 
     * Attempts to extract a valid integer from the given value, using an
     * algorithm somewhat less naive that "remove all characters that are not
     * '0-9+-'".  The result may not be expected, but it will be a integer.
     * 
     * @param mixed $value The value to be sanitized.
     * 
     * @return int The sanitized value.
     * 
     */
    public function sanitizeInt($value)
    {
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // sanitize numerics and non-strings
        if (! is_string($value) || is_numeric($value)) {
            // we double-cast here to honor scientific notation.
            // (int) 1E5 == 1, but (int) (float) 1E5 == 100000
            return (int) (float) $value;
        }
        
        // it's a non-numeric string, attempt to extract an integer from it.
        
        // remove all chars except digit and minus.
        // this removes all + signs; any - sign takes precedence because ...
        //     0 + -1 = -1
        //     0 - +1 = -1
        // ... at least it seems that way to me now.
        $value = preg_replace('/[^0-9-]/', '', $value);
        
        // remove all trailing minuses
        $value = rtrim($value, '-');
        
        // pre-empt further checks if already empty
        if ($value == '') {
            return (int) $value;
        }
        
        // remove all minuses not at the front
        $is_negative = ($value[0] == '-');
        $value = str_replace('-', '', $value);
        if ($is_negative) {
            $value = '-' . $value;
        }
        
        // looks like we're done
        return (int) $value;
    }
}