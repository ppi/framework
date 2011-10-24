<?php
/**
 * 
 * Helper for a 'file' element.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormFile.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_View_Helper_FormFile extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Generates a 'file' element.
     * 
     * Note that this helper ignores the "value" entirely.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formFile($info)
    {
        $this->_prepare($info);
        return '<input type="file"'
             . ' name="' . $this->_view->escape($this->_name) . '"'
             . $this->_view->attribs($this->_attribs)
             . ' />';
    }
}
