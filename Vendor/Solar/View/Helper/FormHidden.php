<?php
/**
 * 
 * Helper for a 'hidden' element.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormHidden.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_View_Helper_FormHidden extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Generates a 'hidden' element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formHidden($info)
    {
        $this->_prepare($info);
        return '<input type="hidden"'
             . ' name="' . $this->_view->escape($this->_name) . '"'
             . ' value="' . $this->_view->escape($this->_value) . '"'
             . $this->_view->attribs($this->_attribs)
             . ' />';
    }
}
