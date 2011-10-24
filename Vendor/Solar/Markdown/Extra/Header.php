<?php
/**
 * 
 * Block plugin to convert Markdown headers into XHTML headers.
 * 
 * This plugin is just like the normal Markdown header plugin, but lets
 * you set an ID on the header using {#id-word} after the header text.
 * 
 * For Setext-style headers, this code ...
 * 
 *     Header 1 {#id-word1}
 *     ===================
 *     
 *     Header 2 {#id-word2}
 *     -------------------
 * 
 * ... would become ...
 * 
 *     <h1 id="id-word1">Header 1</h1>
 * 
 *     <h2 id="id-word2">Header 2</h2>
 * 
 * The same applies for ATX-style headers.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Extra
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
class Solar_Markdown_Extra_Header extends Solar_Markdown_Plugin_Header
{
    /**
     * 
     * Reports these as special markdown characters.
     * 
     * @var string
     * 
     */
    protected $_chars = '-={}#';
    
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
            '{ (^.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n=+[ \t]*\n+ }mx',
            array($this, '_parseTop'),
            $text
        );
    
        // setext sub-level
        $text = preg_replace_callback(
            '{ (^.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n-+[ \t]*\n+ }mx',
            array($this, '_parseSub'),
            $text
        );
        
        // atx
        $text = preg_replace_callback(
            '{
                ^(\#{1,6})     # $1 = string of #\'s
                [ \t]*
                (.+?)          # $2 = Header text
                [ \t]*
                \#*            # optional closing #\'s (not counted)
                (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\}[ ]*)? # id attribute
                \n+
            }mx',
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
        if (! empty($matches[2])) {
            $id = ' id="' . $this->_escape($matches[2]) . '"';
        } else {
            $id = '';
        }
        
        $html = "<h1$id>"
              . $this->_processSpans($matches[1])
              . "</h1>";
        
        return $this->_toHtmlToken($html) . "\n\n";
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
        if (! empty($matches[2])) {
            $id = ' id="' . $this->_escape($matches[2]) . '"';
        } else {
            $id = '';
        }
        
        $html = "<h2$id>"
              . $this->_processSpans($matches[1])
              . "</h2>";
              
        return $this->_toHtmlToken($html) . "\n\n";
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
        if (! empty($matches[3])) {
            $id = ' id="' . $this->_escape($matches[3]) . '"';
        } else {
            $id = '';
        }
        
        $tag = 'h' . strlen($matches[1]); // h1, h2, h5, etc
        
        $html = "<$tag$id>"
              . $this->_processSpans($matches[2])
              . "</$tag>";
              
        return $this->_toHtmlToken($html) . "\n\n";
    }
}
