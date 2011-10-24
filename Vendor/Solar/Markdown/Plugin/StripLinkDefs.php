<?php
/**
 * 
 * Strips named-link definitions in the preparation phase.
 * 
 * This is in support of the Link and Image plugins.
 * 
 * A named link reference looks like this ...
 * 
 *     [name]: http://example.com "Optional Title"
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
 * @version $Id: StripLinkDefs.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_StripLinkDefs extends Solar_Markdown_Plugin
{
    /**
     * 
     * Run this plugin during the "prepare" phase.
     * 
     * @var bool
     * 
     */
    protected $_is_prepare = true;
    
    /**
     * 
     * Removes link definitions from source and saves for later use.
     * 
     * @param string $text Markdown source text.
     * 
     * @return string The text without link definitions.
     * 
     */
    public function prepare($text)
    {
        $less_than_tab = $this->_getTabWidth() - 1;
        
        // Link defs are in the form: ^[id]: url "optional title"
        $text = preg_replace_callback('{
                ^[ ]{0,'.$less_than_tab.'}\[(.+)\]:  # id = $1
                  [ \t]*                             
                  \n?                                # maybe *one* newline
                  [ \t]*                             
                <?(\S+?)>?                           # url = $2
                  [ \t]*                             
                  \n?                                # maybe one newline
                  [ \t]*                             
                (?:                                  
                    (?<=\s)                          # lookbehind for whitespace
                    ["(]                             
                    (.+?)                            # title = $3
                    [")]
                    [ \t]*
                )?    # title is optional
                (?:\n+|\Z)
            }xm',
            array($this, '_prepare'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for link definitions.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _prepare($matches)
    {
        $name  = strtolower($matches[1]);
        $href  = $matches[2];
        $title = empty($matches[3]) ? null : $matches[3];
        
        // save the link
        $this->_markdown->setLink($name, $href, $title);
        
        // done.
        // no return, it's supposed to be removed.
    }
}
