<?php
/**
 * 
 * Helper for inline JavaScript blocks.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ScriptInline.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_View_Helper_ScriptInline extends Solar_View_Helper
{
    /**
     * 
     * Returns a <script></script> block that properly commented for inclusion
     * in XHTML documents.
     * 
     * @param string $code The source of the script.
     * 
     * @param array $attribs Additional attributes for the <script> tag.
     * 
     * @return string The <script></script> tag with the inline script.
     * 
     * @see <http://developer.mozilla.org/en/docs/Properly_Using_CSS_and_JavaScript_in_XHTML_Documents>
     * 
     */
    public function scriptInline($code, $attribs = null)
    {
        settype($attribs, 'array');
        unset($attribs['src']);
        
        if (empty($attribs['type'])) {
            $attribs['type'] = 'text/javascript';
        }
        
        return '<script'
             . $this->_view->attribs($attribs) . ">\n"
             . "//<![CDATA[\n"
             . trim($code)
             . "\n//]]>\n"
             . "</script>\n";
    }
}
