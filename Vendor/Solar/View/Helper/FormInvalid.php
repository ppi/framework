<?php
/**
 * 
 * Helper for building list of invalid messages for a form element.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormInvalid.php 3995 2009-09-08 18:49:24Z pmjones $
 * 
 */
class Solar_View_Helper_FormInvalid extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string css_class_list The CSS class to use for the list tag.
     * 
     * @config string css_class_item The CSS class to use for the item tag.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_FormInvalid = array(
        'css_class_list' => 'invalid',
        'css_class_item' => 'invalid',
    );
    
    /**
     * 
     * Indent this many levels.
     * 
     * @var int
     * 
     */
    protected $_indent = 0;
    
    /**
     * 
     * Helper for building list of invalid messages for a form element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formInvalid($info)
    {
        $this->_prepare($info);
        
        if (! $this->_invalid) {
            return;
        }
        
        $html = array();
        
        $attribs = array(
            'class' => $this->_config['css_class_list']
        );
        
        $text = '<ul'
              . $this->_view->attribs($attribs)
              . '>';
              
        $html[] = $this->_indent(0, $text);
        
        $attribs = array(
            'class' => $this->_config['css_class_item']
        );
        
        foreach ((array) $this->_invalid as $item) {
            $text = '<li'
                    . $this->_view->attribs($attribs)
                    . '>'
                    . $this->_view->escape($item)
                    . '</li>';
            
            $html[] = $this->_indent(1, $text);
        }
        
        $html[] = $this->_indent(0, '</ul>');
        
        return implode("\n", $html);
    }
    
    /**
     * 
     * Sets the indent level.
     * 
     * @param int $indent The indent level.
     * 
     * @return void
     * 
     */
    public function setIndent($indent)
    {
        $this->_indent = (int) $indent;
    }
    
    /**
     * 
     * Returns text after indenting it.
     * 
     * @param int $num The indent level.
     * 
     * @param string $text The text to indent.
     * 
     * @return string The indented text.
     * 
     */
    protected function _indent($num, $text)
    {
        $num += $this->_indent;
        return str_pad('', $num * 4) . $text;
    }
}
