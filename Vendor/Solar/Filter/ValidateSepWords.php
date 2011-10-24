<?php
/**
 * 
 * Validates that a value is composed of one or more words separated by
 * a single separator-character.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateSepWords.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateSepWords extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is composed of one or more words separated by
     * a single separator-character.
     * 
     * Word characters include a-z, A-Z, 0-9, and underscore, indicated by the 
     * regular expression "\w".
     * 
     * By default, the separator is a space, but you can include as many other
     * separators as you like.  Two separators in a row will fail validation.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $sep The word separator character(s), such as " -'" (to
     * allow spaces, dashes, and apostrophes in the word).  Default is ' '.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateSepWords($value, $sep = ' ')
    {
        $expr = '/^[\w' . preg_quote($sep) . ']+$/D';
        return $this->_filter->validatePregMatch($value, $expr);
    }
}