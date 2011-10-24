<?php
/**
 * 
 * Validates that a value is composed only of word characters.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateWord.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateWord extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is composed only of word characters.
     * 
     * These include a-z, A-Z, 0-9, and underscore, indicated by a 
     * regular expression "\w".
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateWord($value)
    {
        $expr = '/^\w+$/D';
        return $this->_filter->validatePregMatch($value, $expr);
    }
}