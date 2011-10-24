<?php
/**
 * 
 * Block plugin to convert Markdown headers into DocBook sections.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc
 * 
 * @author John Gruber <http://daringfireball.net/projects/markdown/>
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Section.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_Section extends Solar_Markdown_Plugin
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
     * Turns setext-style headers into Docbook section tags.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     * @todo WORK IN TWO PASSES. Do sect3 first, then sect2.
     * 
     */
    public function parse($text)
    {
        // do innermost sections first, then outermost ones
        $text = $this->_parseSections($text, '-');
        $text = $this->_parseSections($text, '=');
        return $text;
    }
    
    /**
     * 
     * Parses the sections in a block of text.
     * 
     * @param string $text The text to parse.
     * 
     * @param string $char The underline character to use ('-' or '=').
     * 
     * @return string The parsed text.
     * 
     */
    protected function _parseSections($text, $char)
    {
        $title = '^(.+?)(\{\#(.+)\})?[ \t]*'; // the section title and optional {#xmlid}
        $under = '\n(' . $char . '+)[ \t]*\n+'; // this section (either = or -)
        $sect  = $title . $under; // title and underline
        $body  = "([\w\W]*?)?"; // the section body
        $next  = $title . '\n(=+|-+)[ \t]*\n+'; // next section (both = and -)
        $regex = "/($sect)($body)(($next)|(\z))/m";
        
        // find each section block in turn and tokenize it
        while (true) {
            
            // look for a section block
            $found = preg_match($regex, $text, $matches);
            if (! $found) {
                break;
            }
            
            // the section title
            $title = $matches[2];
            
            // the section id, if any
            $xmlid = $matches[4];
            
            // the title underline
            $under = $matches[5];
            
            // section body
            $body  = $matches[6];
            
            // the next section title/xmlid/underline, or end of text
            $tail  = $matches[8];
            
            // what section tag should we use?
            $tag = 'section';
            
            if ($xmlid) {
                $xmlid = ' xml:id="' . $this->_escape($xmlid) . '"';
            }
            
            // the opening section tag
            $open = "<$tag$xmlid>\n"
                  . "<title>"
                  . $this->_processSpans($title)
                  . "</title>";
            
            // the closing section tag
            $close = "</$tag>";
            
            // replacement text is a tokenized opening tag,
            // the body, a tokenized closing tag, and the tail.
            $repl = $this->_toHtmlToken($open)
                  . "\n\n" . trim($body) . "\n\n"
                  . $this->_toHtmlToken($close)
                  . "\n\n" . trim($tail) . "\n\n";
            
            // replace the whole found text with the replacement text
            $text = str_replace($matches[0], $repl, $text);
        }
        
        // done!
        return $text;
    }
}
