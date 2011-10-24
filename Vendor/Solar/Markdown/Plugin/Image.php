<?php
/**
 * 
 * Span plugin to place image tags.
 * 
 * Syntax is the same as the Link plugin, except you prefix with `!` to 
 * indicate an image instead of an anchor.
 * 
 * Use `![image alt text](/path/to/image)` as an inline image, or
 * `![image name][]` with a defined link.
 * 
 * Named-reference link definitions are captured in the prepare() phase
 * by the StripLinkDefs plugin, and are used by both the Link plugin and
 * the Image plugin.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 * @author John Gruber <http://daringfireball.net/projects/markdown/>
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Image.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_Image extends Solar_Markdown_Plugin
{
    /**
     * 
     * This is a span plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_span = true;
    
    /**
     * 
     * These should be encoded as special Markdown characters.
     * 
     * @var string
     * 
     */
    protected $_chars = '![]()';
    
    /**
     * 
     * Span plugin to place image tags.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        // First, handle reference-style labeled images: ![alt text][id]
        $text = preg_replace_callback('{
            (                                   # wrap whole match in $1
              !\[
                ('.$this->_nested_brackets.')   # alt text = $2
              \]
              
              [ ]?                              # one optional space
              (?:\n[ ]*)?                       # one optional newline followed by spaces
                            
              \[            
                (.*?)                           # id = $3
              \]
            
            )
            }xs', 
            array($this, '_parseReference'),
            $text
        );
        
        // Next, handle inline images:  ![alt text](url "optional title")
        // Don't forget: encode * and _
        $text = preg_replace_callback('{
            (                                   # wrap whole match in $1
              !\[
                ('.$this->_nested_brackets.')   # alt text = $2
              \]
              \(                                # literal paren
                [ \t]*      
                <?(\S+?)>?                      # src url = $3
                [ \t]*      
                (                               # $4
                  ([\'"])                       # quote char = $5
                  (.*?)                         # title = $6
                  \5                            # matching quote
                  [ \t]*    
                )?                              # title is optional
              \)
            )
            }xs',
            array($this, '_parseInline'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for named-reference images.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseReference($matches)
    {
        $whole_match = $matches[1];
        $alt         = $matches[2];
        $name        = strtolower(trim($matches[3]));
        
        if (empty($name)) {
            // for shortcut links like ![this][].
            $name = strtolower($alt);
        }
        
        $link = $this->_markdown->getLink($name);
        if ($link) {
            
            $href   = $this->_escape($link['href']);
            $alt    = $this->_escape($alt);
            $result = "<img src=\"$href\" alt=\"$alt\"";
            
            if (! empty($link['title'])) {
                $title = $this->_escape($link['title']);
                $result .=  " title=\"$title\"";
            }
            
            $result .= " />";
        
        } else {
            // no matching link reference
            $result = $whole_match;
        }
        
        // encode special Markdown characters
        $result = $this->_encode($result);
        
        return $this->_toHtmlToken($result);
    }
    
    /**
     * 
     * Support callback for inline images.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseInline($matches)
    {
        $whole_match = $matches[1];
        $alt         = $matches[2];
        $href        = $matches[3];
        
        $alt    = $this->_escape($alt);
        $href   = $this->_escape($href);
        
        $result = "<img src=\"$href\" alt=\"$alt\"";
        
        if (! empty($matches[6])) {
            $title = $this->_escape($matches[6]);
            $result .=  " title=\"$title\"";
        }
        
        $result .= " />";
        
        // encode special Markdown characters
        $result = $this->_encode($result);
        
        return $this->_toHtmlToken($result);
    }
}
