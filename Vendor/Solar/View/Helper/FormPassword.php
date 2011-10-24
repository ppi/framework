<?php
/**
 * 
 * Helper for a 'password' element.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormPassword.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
class Solar_View_Helper_FormPassword extends Solar_View_Helper_FormElement
{
    /**
     * 
     * User-defined configuration values.
     * 
     * @config bool retain Only retain values between posts if true. For 
     * security, we should not echo back a password on a failed form 
     * validation attempt.
     * 
     * @config string auto_complete If set, forces an auto_complete attribute 
     * with this value. The auto_complete attribute indicates to the browser 
     * that it should not attempt to remember the value of this field for 
     * future auto completion. The attribute is not a part of the HTML 4 era 
     * standard, but is supported by all popular browsers.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_FormPassword = array(
        'retain'        => false,
        'auto_complete' => 'off',
    );
    
    /**
     * 
     * Generates a 'password' element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formPassword($info)
    {
        $this->_prepare($info);

        if ($this->_config['retain']) {
            $value = $this->_view->escape($this->_value);
        } else {            
            $value = '';
        }
        
        $attribs = $this->_attribs;
        
        unset($attribs['auto_complete']);
        if ($this->_config['auto_complete']) {
            $attribs['auto_complete'] = $this->_config['auto_complete'];
        }
        
        return '<input type="password"'
             . ' name="' . $this->_view->escape($this->_name) . '"'
             . ' value="' . $value . '"'
             . $this->_view->attribs($attribs)
             . ' />';
    }
}
