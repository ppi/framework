<?php
/**
 * 
 * Helper for anchor href tags, with built-in text translation.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Anchor.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_Anchor extends Solar_View_Helper
{
    /**
     * 
     * Returns an anchor tag.
     * 
     * @param Solar_Uri|string $spec The anchor href specification.
     * 
     * @param string $text A locale translation key.  If empty, the
     * href will be used as the anchor text
     * 
     * @param array $attribs Attributes for the anchor.
     * 
     * @return string
     * 
     */
    public function anchor($spec, $text = null, $attribs = array())
    {
        // get an escaped href value
        $href = $this->_view->href($spec);
        
        // using the string cast and strict equality to make sure that
        // a string zero is not counted as an empty value.
        if ((string) $text === '') {
            // make sure text is something visible,
            $text = $href;
        } else {
            $text = $this->_view->getText($text);
        }        

        // build attribs, after dropping any 'href' attrib
        settype($attribs, 'array');
        unset($attribs['href']);
        $attr = $this->_view->attribs($attribs);
        
        // build text and return
        return "<a href=\"$href\"$attr>$text</a>";
    }
}
