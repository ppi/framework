<?php
/**
 * 
 * Validates that a value is a legal IPv4 address.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateIpv4.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateIpv4 extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is a legal IPv4 address.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIpv4($value)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        // does the value convert back and forth properly?
        $result = ip2long($value);
        if ($result == -1 || $result === false) {
            // does not properly convert to a "long" result
            return false;
        } elseif (long2ip($result) !== $value) {
            // the long result does not convert back to an identical original
            // value
            return false;
        } else {
            // looks valid
            return true;
        }
    }
}