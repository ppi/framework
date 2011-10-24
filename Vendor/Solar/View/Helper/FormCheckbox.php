<?php
/**
 * 
 * Helper for a 'checkbox' element.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormCheckbox.php 4442 2010-02-26 16:33:06Z pmjones $
 * 
 */
class Solar_View_Helper_FormCheckbox extends Solar_View_Helper_FormElement
{
    
    /**
     * 
     * Default configuration values.
     * 
     * @config string label_class A CSS class to use for the label tag to 
     * identify it as a label for a checkbox.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_FormCheckbox = array(
        'label_class' => 'checkbox',
    );
    
    /**
     * 
     * Generates a 'checkbox' element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formCheckbox($info)
    {
        $this->_prepare($info);
        
        // make sure there is a checked value
        if (empty($this->_options[0])) {
            $this->_options[0] = 1;
        }
        
        // make sure there is an unchecked value
        if (empty($this->_options[1])) {
            $this->_options[1] = 0;
        }
        
        // is it checked already?
        if ($this->_value == $this->_options[0]) {
            $this->_attribs['checked'] = 'checked';
        } else {
            unset($this->_attribs['checked']);
        }
        
        // add the "checked" option first
        $xhtml = '<input type="checkbox"'
               . ' name="' . $this->_view->escape($this->_name) . '"'
               . ' value="' . $this->_view->escape($this->_options[0]) . '"'
               . $this->_view->attribs($this->_attribs)
               . ' />';
               
        // wrap in a label?
        if ($this->_label) {
            if ($this->_config['label_class']) {
                $attribs = $this->_view->attribs(array(
                    'class' => $this->_config['label_class'],
                ));
            } else {
                $attribs = null;
            }
            
            $label = $this->_view->getText($this->_label);
            $xhtml = "<label{$attribs}>{$xhtml} {$label}</label>";
        }
        
        // prefix with unchecked value
        $xhtml = $this->_view->formHidden(array('name' => $this->_name, 'value' => $this->_options[1])) . $xhtml;
        
        return $xhtml;
    }
}
