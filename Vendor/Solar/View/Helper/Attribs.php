<?php
/**
 * 
 * Plugin to convert an associative array to a string of tag attributes.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Attribs.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_Attribs extends Solar_View_Helper
{
    /**
     * 
     * Converts an associative array to an attribute string.
     * 
     * @param array $attribs From this array, each key-value pair is 
     * converted to an attribute name and value.
     * 
     * @return string The XHTML for the attributes.
     * 
     */
    public function attribs($attribs)
    {
        $xhtml = '';
        foreach ((array) $attribs as $key => $val) {
            
            // skip empty values
            if (empty($val)) {
                continue;
            }
            
            // space-separate multiple values
            if (is_array($val)) {
                $val = implode(' ', $val);
            }
            
            // add the attribute, but only if really empty.
            // using the string cast and strict equality to make sure that
            // a string zero is not counted as an empty value.
            if ((string) $val !== '') {
                $xhtml .= ' ' . $this->_view->escape($key)
                       .  '="' . $this->_view->escape($val) . '"';
            }
        }
        
        // done
        return $xhtml;
    }
}
