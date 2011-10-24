<?php
/**
 * 
 * Span class to convert ampersands and less-than angle brackets to 
 * their HTML entity equivalents.
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
 * @version $Id: AmpsAngles.php 3617 2009-02-16 19:47:30Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_AmpsAngles extends Solar_Markdown_Plugin
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
     * Smart processing for encoding ampersands and left-angle brackets.
     * 
     * Ampersand-encoding based entirely on Nat Irons's [Amputator MT][]
     * plugin.
     * 
     * [Amputator MT]: http://bumppo.net/projects/amputator/
     * 
     * @param string $text The source text to be parsed.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        // encode ampersands
        $text = preg_replace_callback(
            '/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', 
            array($this, '_processAmp'),
            $text
        );
        
        // encode naked <'s
        $text = preg_replace_callback(
            '{<(?![a-z/?\$!])}i',
            array($this, '_processLt'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Callback to place tokenized ampersands.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _processAmp($matches)
    {
        return $this->_toHtmlToken('&amp;');
    }
    
    /**
     * 
     * Callback to place tokenized less-than symbols.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _processLt($matches)
    {
        return $this->_toHtmlToken('&lt;');
    }
}
