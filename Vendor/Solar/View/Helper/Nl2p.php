<?php
/**
 * 
 * Converts 2 or more newlines to paragraph tags.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Nl2p.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_Nl2p extends Solar_View_Helper
{
    /**
     * 
     * Converts 2 or more newlines to paragraph tags.
     * 
     * @param string $text The text on which to change newlines into <p> tags.
     * 
     * @param array $attr Attributes for each <p> tag.
     * 
     * @return string The escaped text, with newlines changed to paragraphs.
     * 
     */
    public function nl2p($text, $attr = array())
    {
        // normalize line endings
        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        
        // arribs for <p> tags
        $attribs = $this->_view->attribs($attr);
        
        // split on 2 newlines, with any space between them as well
        $grafs = preg_split('/\n\s*\n/', $text);
        
        // loop through the paragraph blocks
        $html = '';
        foreach ($grafs as $graf) {
            $graf = trim($graf);
            if ($graf) {
                $html .= "<p$attribs>" . $this->_view->escape($graf) . "</p>\n\n";
            }
        }
        
        return $html;
    }
}
