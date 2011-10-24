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
 * @version $Id: Link.php 4263 2009-12-07 19:25:31Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_Link extends Solar_Markdown_Plugin
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
    protected $_chars = '[]()';
    
    /**
     * 
     * Converts Markdown links into XHTML anchors.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        // First, handle reference-style links: [link text] [id]
        $text = preg_replace_callback("{
                (                                    # wrap whole match in $1
                  \\[
                    (".$this->_nested_brackets.")    # link text = $2
                  \\]
                  
                  [ ]?                               # one optional space
                  (?:\\n[ ]*)?                       # one optional newline followed by spaces
                                                 
                  \\[                            
                    (.*?)                            # id = $3
                  \\]
                )
            }xs",
            array($this, '_parseReference'),
            $text
        );
        
        // Next, inline-style links: [link text](url "optional title")
        $text = preg_replace_callback("{
                (                                   # wrap whole match in $1
                  \\[                           
                    (".$this->_nested_brackets.")   # link text = $2
                  \\]                           
                  \\(                               # literal paren
                    [ \\t]*                     
                    <?(.*?)>?                       # href = $3
                    [ \\t]*                     
                    (                               # $4
                      (['\"])                       # quote char = $5
                      (.*?)                         # Title = $6
                      \\5                           # matching quote
                    )?                              # title is optional
                  \\)
                )
            }xs",
            array($this, '_parseInline'),
            $text
        );
        
        return $text;
    }
    
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
            $result = "<a href=\"$href\"";
            
            if ($link['title']) {
                $title = $this->_escape($link['title']);
                $result .=  " title=\"$title\"";
            }
            
            $result .= ">" . $this->_escape($alt_text) . "</a>";
            
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
        
        $result   = "<a href=\"$href\"";
        
        if (! empty($matches[6])) {
            $title = $this->_escape($matches[6]);
            $result .=  " title=\"$title\"";
        }
    
        $result .= ">$alt_text</a>";
        
        // encode special Markdown characters
        $result = $this->_encode($result);
        
        return $this->_toHtmlToken($result);
    }
}
