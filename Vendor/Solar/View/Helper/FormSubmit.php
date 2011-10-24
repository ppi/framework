<?php
/**
 * 
 * Helper for a 'submit' button.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormSubmit.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_View_Helper_FormSubmit extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Generates a 'submit' button.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formSubmit($info)
    {
        $this->_prepare($info);
        
        // we process values this way so that blank submit buttons
        // get the browser-default value
        if (empty($this->_value)) {
            $escval = '';
        } else {
            $escval = ' value="' . $this->_view->escape($this->_value) . '"';
        }
        
        // output
        return '<input type="submit"'
             . ' name="' . $this->_view->escape($this->_name) . '"'
             . $escval
             . $this->_view->attribs($this->_attribs) . ' />';
    }
}
