<?php
/**
 * 
 * Block plugin to change indented text to <screen>...</screen>
 * blocks.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Screen.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_Screen extends Solar_Markdown_Plugin_CodeBlock
{
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
             . $this->_toHtmlToken("<screen>" . $code . "\n</screen>")
             . "\n\n";
    }
}
