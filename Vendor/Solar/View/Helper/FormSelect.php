<?php
/**
 * 
 * Helper for 'select' list of options.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormSelect.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_View_Helper_FormSelect extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Generates 'select' list of options.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formSelect($info)
    {
        $this->_prepare($info);
        
        // force $this->_value to array so we can compare multiple values
        // to multiple options.
        settype($this->_value, 'array');
        
        // check for multiple attrib and change name if needed
        if (isset($this->_attribs['multiple']) &&
            $this->_attribs['multiple'] == 'multiple' &&
            substr($this->_name, -2) != '[]') {
            $this->_name .= '[]';
        }
        
        // check for multiple implied by the name
        if (substr($this->_name, -2) == '[]') {
            // set multiple attrib
            $this->_attribs['multiple'] = 'multiple';
            // if no value is selected, the element won't be sent back to the
            // server at all (like an unchecked checkbox).  add a default
            // blank value under a non-array name so that if no values are
            // selected, an empty value is sent back to the server.
            $xhtml = $this->_view->formHidden(array(
                'name'  => substr($this->_name, 0, -2),
                'value' => null,
            ));
        } else {
            // not multiple, start with blank xhtml
            $xhtml = '';
        }
        
        // build the list of options
        $list = array();
        foreach ($this->_options as $value => $label) {
            if (is_array($label)) {
                
                // Use <optgroup>
                $list[] = '<optgroup label="'
                        . $this->_view->escape($value)
                        . '">';
                
                foreach ($label as $grp_value => $grp_label) {
                    $list[] = $this->_getOption($grp_value, $grp_label);
                }
                
                $list[] = '</optgroup>';
                
            } else {
                $list[] = $this->_getOption($value, $label);
            }
        }
        
        // build and return the remaining xhtml
        return $xhtml
             . '<select name="' . $this->_view->escape($this->_name) . '"'
             . $this->_view->attribs($this->_attribs) . ">\n"
             . "    " . implode("\n    ", $list) . "\n"
             . "</select>";
    }
    
    /**
     *
     * Builds an option for the select.
     *
     * @param string $value The option value.
     *
     * @param string $label The option lavel.
     * 
     * @return string The option XHTML.
     * 
     */
    protected function _getOption($value, $label)
    {
        $selected = '';
        
        if (in_array($value, $this->_value)) {
            $selected = ' selected="selected"';
        }
        
        $option = '<option'
                . ' value="' . $this->_view->escape($value) . '"'
                . ' label="' . $this->_view->escape($label) . '"'
                . $selected
                . '>' . $this->_view->escape($label) . "</option>";
        
        return $option;        
    }
}
