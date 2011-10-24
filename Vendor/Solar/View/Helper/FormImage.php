<?php
/**
 * 
 * Helper for a 'image' element.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormImage.php 3366 2008-08-26 01:36:49Z pmjones $
 * 
 */
class Solar_View_Helper_FormImage extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Generates an 'image' element.
     * 
     * @param array $info An array of element information.  Uses the element
     * 'attribs[src]' for the image source.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formImage($info)
    {
        $this->_prepare($info);
        
        // look for attribs['src']
        if (! empty($this->_attribs['src'])) {
            $src = $this->_attribs['src'];
            unset($this->_attribs['src']);
        }
        
        $xhtml = '<input type="image"'
               . ' name="' . $this->_view->escape($this->_name) . '"'
               . ' src="' . $this->_view->publicHref($src) . '"'
               . $this->_view->attribs($this->_attribs)
               . '/>';
        
        return $xhtml;
    }
}
