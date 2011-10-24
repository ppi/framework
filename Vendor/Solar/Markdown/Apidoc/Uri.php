<?php
/**
 * 
 * Span plugin to create anchors from inline URIs.
 * 
 * Syntax looks like this ...
 * 
 *     <http://example.com>
 * 
 * That will create the following XHTML ...
 * 
 *     <a href="http://example.com">http://example.com</a>
 * 
 * You can use this for emails as well ...
 * 
 *     <pmjones@example.com>
 * 
 * ... and the plugin will obfuscate the email address for you.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Uri.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_Uri extends Solar_Markdown_Plugin_Uri
{
    /**
     * 
     * Support callback for inline URIs.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $href = $this->_escape($matches[1]);
        return $this->_toHtmlToken("<link xlink:href=\"$href\">$href</link>");
    }
    
    /**
     * 
     * Support callback for parsing email addresses.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string A mailto anchor.
     * 
     */
    protected function _parseEmail($matches)
    {
        $addr = $matches[1];
        $href = "mailto:" . $addr;
        return $this->_toHtmlToken("<link xlink:href=\"$href\">$addr</link>");
    }
}
