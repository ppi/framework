<?php
/**
 * 
 * Block plugin to changes indented text to <pre><code>...</code></pre>
 * blocks.
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
 * @version $Id: CodeBlock.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_CodeBlock extends Solar_Markdown_Plugin
{
    /**
     * 
     * This is a block plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_block = true;
    
    /**
     * 
     * Makes <pre><code>...</code></pre> blocks.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $tab_width = $this->_getTabWidth();
        
        $text = preg_replace_callback('{
                (?:\n\n|\A)
                (                                 # $1 = the code block -- one or more lines, starting with a space/tab
                  (?:
                    (?:[ ]{'.$tab_width.'} | \t)  # Lines must start with a tab or a tab-width of spaces
                    .*\n+
                  )+
                )
                ((?=^[ ]{0,'.$tab_width.'}\S)|\Z) # Lookahead for non-space at line-start, or end of doc
            }xm',
            array($this, '_parse'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for code blocks.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $code = $this->_escape($this->_outdent($matches[1]), ENT_NOQUOTES);
        
        // trim leading newlines and trailing whitespace
        $code = preg_replace(
            array('/\A\n+/', '/\s+\z/'),
            '',
            $code
        );
        
        return "\n\n"
             . $this->_toHtmlToken("<pre><code>" . $code . "\n</code></pre>")
             . "\n\n";
    }
}
