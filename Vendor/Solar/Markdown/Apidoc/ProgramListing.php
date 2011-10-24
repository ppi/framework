<?php
/**
 * 
 * Block plugin to change indented text to <programlisting>...</programlisting>
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
 * @version $Id: ProgramListing.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_ProgramListing extends Solar_Markdown_Wiki_ColorCodeBlock
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
        $type = empty($matches[1]) ? '' : trim($matches[1]);
        $code = $this->_outdent($matches[2]);
        
        // trim leading newlines and trailing whitespace
        $code = preg_replace(
            array('/\A\n+/', '/\s+\z/'),
            '',
            $code
        );
        
        $lang = $type
              ? ' language="' . $this->_escape($type, ENT_NOQUOTES) . '"'
              : '';
        
        if (strtolower($type) == 'php') {
            $code = "<?php\n" . $code;
        }
        
        $html = "<programlisting$lang><![CDATA[\n$code]]></programlisting>";
        
        // done
        return "\n"
             . $this->_toHtmlToken($html)
             . "\n";
    }
}
