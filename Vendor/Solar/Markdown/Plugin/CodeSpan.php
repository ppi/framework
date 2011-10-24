<?php
/**
 * 
 * Span plugin to change `` `text` `` to `<code>text</code>`.
 * 
 * Backtick quotes are used for `<code></code>` spans.
 * 
 * You can use multiple backticks as the delimiters if you want to
 * include literal backticks in the code span. So, this input ...
 * 
 *     Just type ``foo `bar` baz`` at the prompt.
 * 
 * ... will translate to ...
 * 
 *     <p>Just type <code>foo `bar` baz</code> at the prompt.</p>
 * 
 * There's no arbitrary limit to the number of backticks you
 * can use as delimters. If you need three consecutive backticks
 * in your code, use four for delimiters, etc.
 * 
 * You can use spaces to get literal backticks at the edges ...
 * 
 *     type `` `bar` ``
 * 
 * ... which turns into ...
 * 
 *     type <code>`bar`</code>
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
 * @version $Id: CodeSpan.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_CodeSpan extends Solar_Markdown_Plugin
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
     * These characters should be encoded as special Markdown characters.
     * 
     * @var string
     * 
     */
    protected $_chars = '`';
    
    /**
     * 
     * Creates code spans from backtick-delimited text.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $text = preg_replace_callback('@
                (?<!\\\) # Character before opening ` cannot be a backslash
                (`+)     # $1 = Opening run of `
                (.+?)    # $2 = The code block
                (?<!`)   
                \1       # Matching closer
                (?!`)
            @xs',
            array($this, '_parse'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for code spans.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $c = $matches[2];
        $c = preg_replace('/^[ \t]*/', '', $c); // leading whitespace
        $c = preg_replace('/[ \t]*$/', '', $c); // trailing whitespace
        $c = $this->_escape($c);
        $c = "<code>$c</code>";
        return $this->_toHtmlToken($c);
    }
}
