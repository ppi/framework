<?php
/**
 * 
 * Helper for a 'textarea' element.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormTextarea.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_View_Helper_FormTextarea extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Generates a 'textarea' element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formTextarea($info)
    {
        $this->_prepare($info);
        return '<textarea'
             . ' name="' . $this->_view->escape($this->_name) . '"'
             . $this->_view->attribs($this->_attribs) . '>'
             . $this->_view->escape($this->_value)
             . '</textarea>';
    }
}
