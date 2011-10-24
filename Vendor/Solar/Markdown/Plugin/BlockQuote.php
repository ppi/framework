<?php
/**
 * 
 * Block plugin to convert email-style blockquotes to <blockquote> tags.
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
 * @version $Id: BlockQuote.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_BlockQuote extends Solar_Markdown_Plugin
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
     * These should be encoded as special Markdown characters.
     * 
     * @var string
     * 
     */
    protected $_chars = '>';
    
    /**
     * 
     * Makes <blockquote>...</blockquote> tags from email-style block
     * quotes.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $text = preg_replace_callback('/
            (                       # Wrap whole match in $1
                (                 
                    ^[ \t]*>[ \t]?  # ">" at the start of a line
                    .+\n            # rest of the first line
                    (.+\n)*         # subsequent consecutive lines
                    \n*             # blanks
                )+
            )/xm',
            array($this, '_parse'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for block quotes.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $bq = $matches[1];
        
        // trim one level of quoting - trim whitespace-only lines
        $bq = preg_replace(array('/^[ \t]*>[ \t]?/m', '/^[ \t]+$/m'), '', $bq);
        
        // recursively parse for blocks inside block-quotes, including
        // other block-quotes
        $bq = $this->_processBlocks($bq);
        $bq = preg_replace('/^/m', "  ", $bq);
        
        // These leading spaces screw with <pre> content, so we need to
        // fix that:
        $bq = preg_replace_callback(
            '{(\s*<pre>.+?</pre>)}sx', 
            array($this, '_trimPreSpaces'),
            $bq
        );
        
        return $this->_toHtmlToken("<blockquote>\n$bq\n</blockquote>") . "\n\n";
    }
    
    /**
     * 
     * Trims 2 leading spaces from <pre> blocks.
     * 
     * @param array $matches Matches from _parse().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _trimPreSpaces($matches) {
        $pre = $matches[1];
        $pre = preg_replace('/^  /m', '', $pre);
        return $pre;
    }
}
