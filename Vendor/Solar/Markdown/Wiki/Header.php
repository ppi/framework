<?php
/**
 * 
 * Block plugin to convert wiki-fied headers into XHTML headers.
 * 
 * This code ...
 * 
 *     =======
 *      Title
 *     =======
 *     
 *     ---------------
 *      Super-Section
 *     ---------------
 *     
 *     Section
 *     =======
 *     
 *     Sub Section
 *     -----------
 *     
 * ... would become ...
 * 
 *     <h1>Title</h1>
 *     
 *     <h2>Super-Section</h2>
 *     
 *     <h3>Section</h3>
 *     
 *     <h4>Sub-Section</h4>
 * 
 * You can also suffix the header text with `{#id}` and that will become the
 * the header ID attribute.  For example, this code ...
 * 
 *     Section {#foo}
 *     ==============
 * 
 * ... would become ...
 * 
 *     <h3 id="foo">Section</h3>
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
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
class Solar_Markdown_Wiki_Header extends Solar_Markdown_Plugin
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
    protected $_chars = '-=';
    
    /**
     * 
     * Turns setext-style headers into XHTML header tags.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        // h2
        $text = preg_replace_callback(
            // '{ ^=+[ \t]*\n(.+)[ \t]*\n=+[ \t]*\n+ }mx',
            '{ ^=+[ \t]*\n(.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n=+[ \t]*\n+ }mx',
            array($this, '_parseTitle'),
            $text
        );
        
        // h3
        $text = preg_replace_callback(
            // '{ ^-+[ \t]*\n(.+)[ \t]*\n-+[ \t]*\n+ }mx',
            '{ ^-+[ \t]*\n(.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n-+[ \t]*\n+ }mx',
            array($this, '_parseSuperSection'),
            $text
        );
        
        // h4
        $text = preg_replace_callback(
            '{ (^.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n=+[ \t]*\n+ }mx',
            array($this, '_parseSection'),
            $text
        );
        
        // h5
        $text = preg_replace_callback(
            '{ (^.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n-+[ \t]*\n+ }mx',
            array($this, '_parseSubSection'),
            $text
        );
        
        // atx 1 through 4
        $text = preg_replace_callback(
            "{
                ^(\\#{1,4})                         # $1 = string of #'s
                [ \\t]*                             
                (.+?)                               # $2 = header text
                (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})?    # $3 = ID
                [ \\t]*
                \\#*                                # optional closing #'s
                \\n+
            }xm",
            array($this, '_parseAtx'),
            $text
        );
        
        // done
        return $text;
    }
    
    /**
     * 
     * Support callback for ATX headers.
     * 
     * Only supports 1-4 leading hash marks.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseAtx($matches)
    {
        $tag = 'h' . strlen($matches[1]);
        
        if (! empty($matches[3])) {
            $id = ' id="' . $this->_escape($matches[3]) . '"';
        } else {
            $id = '';
        }
        
        $html = "<$tag$id>"
              . $this->_processSpans($matches[2])
              . "</$tag>";
              
        return $this->_toHtmlToken($html) . "\n\n";
    }
    
    /**
     * 
     * Support callback for H1 headers.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseTitle($matches)
    {
        return $this->_header('h1', $matches);
    }
    
    /**
     * 
     * Support callback for H2 headers.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseSuperSection($matches)
    {
        return $this->_header('h2', $matches);
    }
    
    /**
     * 
     * Support callback for H3 headers.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseSection($matches)
    {
        return $this->_header('h3', $matches);
    }
    
    /**
     * 
     * Support callback for H4 headers.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseSubSection($matches)
    {
        return $this->_header('h4', $matches);
    }
    
    /**
     * 
     * Support callback for all headers.
     * 
     * @param string $tag The header tag ('h1', 'h5', etc).
     * 
     * @param string $matches The matched values, element 1 is the text,
     * optional element 2 is the ID (if any).
     * 
     * @return string The replacement header HTML token.
     * 
     */
    protected function _header($tag, $matches)
    {
        if (! empty($matches[2])) {
            $id = ' id="' . $this->_escape($matches[2]) . '"';
        } else {
            $id = '';
        }
        
        $html = "<$tag$id>"
              . $this->_processSpans($matches[1])
              . "</$tag>";
              
        return $this->_toHtmlToken($html) . "\n\n";
    }
}
