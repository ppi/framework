<?php
/**
 * 
 * Block plugin to convert Markdown headers into XHTML headers.
 * 
 * For Setext-style headers, this code ...
 * 
 *     Header 1
 *     ========
 *     
 *     Header 2
 *     --------
 * 
 * ... would become ...
 * 
 *     <h1>Header 1</h1>
 * 
 *     <h2>Header 2</h2>
 * 
 * 
 * For ATX-style headers, this code ...
 * 
 *     # Header 1
 * 
 *     ## Header 2
 * 
 *     ##### Header 5
 * 
 * ... would become ...
 * 
 *     <h1>Header 1</h1>
 * 
 *     <h2>Header 2</h2>
 * 
 *     <h5>Header 5</h5>
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
 * @version $Id: Header.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_Header extends Solar_Markdown_Plugin
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
    protected $_chars = '-=#';
    
    /**
     * 
     * Turns ATX- and setext-style headers into XHTML header tags.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        // setext top-level
        $text = preg_replace_callback(
            '{ ^(.+)[ \t]*\n=+[ \t]*\n+ }mx',
            array($this, '_parseTop'),
            $text
        );
        
        // setext sub-level
        $text = preg_replace_callback(
            '{ ^(.+)[ \t]*\n-+[ \t]*\n+ }mx',
            array($this, '_parseSub'),
            $text
        );
        
        // atx
        $text = preg_replace_callback(
            "{
                ^(\\#{1,6}) # $1 = string of #'s
                [ \\t]*
                (.+?)       # $2 = Header text
                [ \\t]*
                \\#*        # optional closing #'s (not counted)
                \\n+
            }xm",
            array($this, '_parseAtx'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for top-level setext headers ("h1").
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseTop($matches)
    {   
        return $this->_header('h1', $matches[1]);
    }
    
    /**
     * 
     * Support callback for sub-level setext headers ("h2").
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseSub($matches)
    {
        return $this->_header('h2', $matches[1]);
    }
    
    /**
     * 
     * Support callback for ATX headers.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseAtx($matches)
    {
        $tag = 'h' . strlen($matches[1]); // h1, h2, h5, etc
        return $this->_header($tag, $matches[2]);
    }
    
    /**
     * 
     * Support callback for all headers.
     * 
     * @param string $tag The header tag ('h1', 'h5', etc).
     * 
     * @param string $text The header text.
     * 
     * @return string The replacement header HTML token.
     * 
     */
    protected function _header($tag, $text)
    {
        $html = "<$tag>"
              . $this->_processSpans($text)
              . "</$tag>";
        
        return $this->_toHtmlToken($html) . "\n\n";
    }
}
