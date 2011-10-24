<?php
/**
 * 
 * Helper for action anchors, with built-in text translation.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Action.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_Action extends Solar_View_Helper
{
    /**
     * 
     * Returns an action anchor tag.
     * 
     * @param string|Solar_Uri_Action $spec The action specification.
     * 
     * @param string $text A locale translation key.
     * 
     * @param string $attribs Additional attributes for the anchor.
     * 
     * @return string
     * 
     */
    public function action($spec, $text = null, $attribs = null)
    {
        // get an escaped href action value
        $href = $this->_view->actionHref($spec);
        
        // using the string cast and strict equality to make sure that
        // a string zero is not counted as an empty value.
        if ((string) $text === '') {
            // make sure text is something visible
            $text = $href;
        } else {
            $text = $this->_view->getText($text);
        }
        
        // build attribs, after dropping any 'href' attrib
        settype($attribs, 'array');
        unset($attribs['href']);
        $attribs = $this->_view->attribs($attribs);
        
        // escape text and return
        return "<a href=\"$href\"$attribs>$text</a>";
    }
}
