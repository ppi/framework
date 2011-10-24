<?php
/**
 * 
 * Validates that a value is formatted as a MIME type.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ValidateMimeType.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Filter_ValidateMimeType extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is formatted as a MIME type.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param array $allowed The MIME type must be one of these
     * allowed values; if null, then all values are allowed.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMimeType($value, $allowed = null)
    {
        // basically, anything like 'text/plain' or
        // 'application/vnd.ms-powerpoint' or
        // 'text/xml+xhtml'
        $word = '[a-zA-Z][\-\.a-zA-Z0-9+]*';
        $expr = '|^' . $word . '/' . $word . '$|D';
        $ok = $this->_filter->validatePregMatch($value, $expr);
        $allowed = (array) $allowed;
        if ($ok && count($allowed) > 0) {
            $ok = in_array($value, $allowed);
        }
        return $ok;
    }
}