<?php
/**
 * 
 * Block plugin to change indented text to <pre><code>...</code></pre>
 * blocks, with code colorization.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ColorCodeBlock.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Wiki_ColorCodeBlock extends Solar_Markdown_Plugin
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
     * Makes <pre><code>...</code></pre> blocks and colorizes.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $tab_width = $this->_getTabWidth();
        
        $regex = '{
            \n\{\{code:(.*?)\n                          # $1 = the colorization type, if any
                (                                       # $2 = the code block -- one or more lines, starting with a space/tab
                  (?:                                   
                    (?:[ ]{' . $tab_width . '} | \t)    # Lines must start with a tab or a tab-width of spaces
                    .*\n+                               
                  )+                                    
                )                                       
            \}\}\s*?\n                                  # end of the block
        }mx';                                           
        
        $text = preg_replace_callback(
            $regex,
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
        $type = empty($matches[1]) ? '' : trim($matches[1]);
        $code = $this->_outdent($matches[2]);
        
        // trim leading newlines and trailing whitespace
        $code = preg_replace(
            array('/\A\n+/', '/\s+\z/'),
            '',
            $code
        );
        
        // colorize and escape
        $color = '_color' . ucfirst(strtolower($type));
        if (is_callable(array($this, $color))) {
            // colorize, which should escape on its own
            $code = $this->$color($code);
        } else {
            // simple escaping
            $code = "<pre><code>" . $this->_escape($code, ENT_NOQUOTES) . "</code></pre>";
        }
        
        // done
        return "\n"
             . $this->_toHtmlToken($code)
             . "\n";
    }
    
    /**
     * 
     * Returns colorized PHP code.
     * 
     * @param string $code The code block to colorize.
     * 
     * @return string The colorized code.
     * 
     */
    protected function _colorPhp($code)
    {
        // start using http://us3.php.net/manual/en/function.highlight-string.php#68274
        $code = "<?php\n" . $code . "\n?>";
        $code = highlight_string($code, true);
        $code = str_replace(
            array("<br />", "&nbsp;"),
            array("\n", " "),
            $code
        );
        
        // <code><span style="color: #000000">\n
        // </span>\n</code>\n
        $code = substr($code, 36, -16);
        
        // done!
        return "<pre><code>$code</code></pre>";
    }
}
