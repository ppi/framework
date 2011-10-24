<?php
/**
 * 
 * Validates that a value matches a regular expression.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidatePregMatch.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidatePregMatch extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates the value against a regular expression.
     * 
     * Uses [[php::preg_match() | ]] to compare the value against the given
     * regular epxression.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $expr The regular expression to validate against.
     * 
     * @return bool True if the value matches the expression, false if not.
     * 
     */
    public function validatePregMatch($value, $expr)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return (bool) preg_match($expr, $value);
    }
}