<?php
/**
 * 
 * Helper for standalone options.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormOptions.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_View_Helper_FormOptions extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Generates a list of options.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formOptions($info)
    {
        $this->_prepare($info);
        
        // force $this->_value to array so we can compare multiple values
        // to multiple options.
        settype($this->_value, 'array');
        
        // build the list of options
        $list = array();
        foreach ($this->_options as $opt_value => $opt_label) {
            $selected = '';
            if (in_array($opt_value, $this->_value)) {
                $selected = ' selected="selected"';
            }
            $list[] = '<option'
                    . ' value="' . $this->_view->escape($opt_value) . '"'
                    . ' label="' . $this->_view->escape($opt_label) . '"'
                    . $selected . $this->_view->attribs($this->_attribs)
                    . '>' . $this->_view->escape($opt_label) . "</option>";
        }
        
        // now build the XHTML
        return implode("\n", $list);
    }
}
