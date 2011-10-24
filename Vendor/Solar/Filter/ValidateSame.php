<?php
/**
 * 
 * Validates that this value has the same value and type as some other element
 * in the filter chain.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateSame.php 4272 2009-12-17 21:56:32Z pmjones $
 * 
 */
class Solar_Filter_ValidateSame extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that this value has the same value and type as some other 
     * element in the filter chain.
     * 
     * If the other element does not exist in $this->_data, the validation
     * will fail.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $other_key Check against the value and type of this 
     * element in $this->_data.
     * 
     * @return bool True if the types and values are equal, false if not.
     * 
     */
    public function validateSame($value, $other_key)
    {
        if (! $this->_filter->dataKeyExists($other_key)) {
            return false;
        }
        
        $other = $this->_filter->getData($other_key);
        return $value === $other;
    }
}
