<?php
/**
 * 
 * Span plugin for Markdown anchor shortcuts.
 * 
 * You can link to another page using `[display text](http://example.com)`.
 * 
 * Alternatively, you can use defined links ...
 * 
 *     Show [this link][id].
 * 
 *     [id]: http://example.com
 * 
 * And a shorthand for defined links ...
 * 
 *     Show the [example][].
 *     
 *     [example]: http://example.com
 * 
 * Named-reference link definitions are captured in the prepare() phase
 * by the StripLinkDefs plugin, and are used by both the Link plugin and
 * the Image plugin.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Link.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_Link extends Solar_Markdown_Plugin_Link
{
    /**
     * 
     * Support callback for named-reference links.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseReference($matches)
    {
        $whole_match = $matches[1];
        $alt_text    = $matches[2];
        $name        = strtolower(trim($matches[3]));
        
        if (empty($name)) {
            // for shortcut links like [this][].
            $name = strtolower($alt_text);
        }
        
        $link = $this->_markdown->getLink($name);
        if ($link) {
            
            $href = $this->_escape($link['href']);
            $result = "<link xlink:href=\"$href\"";
            
            if ($link['title']) {
                $title = $this->_escape($link['title']);
                $result .=  " xlink:title=\"$title\"";
            }
            
            $result .= ">" . $this->_escape($alt_text) . "</link>";
            
            // encode special Markdown characters
            $result = $this->_encode($result);
            
        } else {
            $result = $whole_match;
        }
        
        return $this->_toHtmlToken($result);
    }
    
    /**
     * 
     * Support callback for inline links.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseInline($matches)
    {
        $alt_text = $this->_escape($matches[2]);
        $href     = $this->_escape($matches[3]);
        
        $result   = "<link xlink:href=\"$href\"";
        
        if (! empty($matches[6])) {
            $title = $this->_escape($matches[6]);
            $result .=  " xlink:title=\"$title\"";
        }
    
        $result .= ">$alt_text</link>";
        
        // encode special Markdown characters
        $result = $this->_encode($result);
        
        return $this->_toHtmlToken($result);
    }
}
