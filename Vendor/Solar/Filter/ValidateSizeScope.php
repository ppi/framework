<?php
/**
 * 
 * Validates that a value has only a certain number of digits and decimals.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateSizeScope.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateSizeScope extends Solar_Filter_Abstract
{
    /**
     * 
     * See the value has only a certain number of digits and decimals.
     * 
     * The value must be numeric, can be no longer than the `$size`,
     * and can have no more decimal places than the `$scope`.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param int $size The total number of digits allowed in the value,
     * excluding the negative sign and decimal point.
     * 
     * @param int $scope The maximum number of decimal places.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateSizeScope($value, $size, $scope)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        // scope has to be smaller than size.
        // both size and scope have to be positive numbers.
        if ($size < $scope || $size < 0 || $scope < 0 ||
            ! is_numeric($size) || ! is_numeric($scope)) {
            return false;
        }
        
        // value must be only numeric
        if (! is_numeric($value)) {
            return false;
        }
        
        // drop trailing and leading zeroes
        $value = (float) $value;
        
        // test the size (whole + decimal) and scope (decimal only).
        // does not include signs (+/-) or the decimal point itself.
        // 
        // use the @ signs in strlen() checks to suppress errors
        // when the match-element doesn't exist.
        $expr = "/^(\-)?([0-9]+)?((\.)([0-9]+))?\$/D";
        if (preg_match($expr, $value, $match) &&
            @strlen($match[2] . $match[5]) <= $size &&
            @strlen($match[5]) <= $scope) {
            return true;
        } else {
            return false;
        }
    }
}