<?php
/**
 * 
 * Validates that a value is a legal IP address.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateIp.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateIp extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is a legal IP address.
     * 
     * Currently validates only IPv4; in future versions, will validate both
     * IPv4 and IPv6.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIp($value)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return $this->_filter->validateIpv4($value);
    }
}