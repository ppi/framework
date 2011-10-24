<?php
/**
 * 
 * Span plugin to create anchors from inline URIs.
 * 
 * Syntax looks like this ...
 * 
 *     <http://example.com>
 * 
 * That will create the following XHTML ...
 * 
 *     <a href="http://example.com">http://example.com</a>
 * 
 * You can use this for emails as well ...
 * 
 *     <pmjones@example.com>
 * 
 * ... and the plugin will obfuscate the email address for you.
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
 * @version $Id: Uri.php 4574 2010-05-15 21:36:33Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_Uri extends Solar_Markdown_Plugin
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
     * These should be encoded as special Markdown characters.
     * 
     * @var string
     * 
     */
    protected $_chars = '<>';
    
    /**
     * 
     * Recognizes these URI schemes when parsing.
     * 
     * @var array
     * 
     */
    protected $_schemes = array('http', 'https', 'ftp');
    
    /**
     * 
     * Converts inline URIs to anchors.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        
        $list = implode('|', $this->_schemes);
        $text = preg_replace_callback(
            "!<(($list):[^'\">\\s]+)>!", 
            array($this, '_parse'),
            $text
        );
        
        // email addresses: <address@domain.foo>
        $text = preg_replace_callback('{
                <
                    (?:mailto:)?
                    (
                        [-.\w]+
                        \@
                        [-a-z0-9]+(\.[-a-z0-9]+)*\.[a-z]+
                    )
                >
            }xi',
            array($this, '_parseEmail'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for inline URIs.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $href = $this->_escape($matches[1]);
        return $this->_toHtmlToken("<a href=\"$href\">$href</a>");
    }
    
    /**
     * 
     * Support callback for parsing email addresses.
     * 
     * From the original notes ...
     * 
     * > Input: an email address, for example "foo@example.com"
     * >
     * > Output: the email address as a mailto link, with each character
     * > of the address encoded as either a decimal or hex entity, in
     * > the hopes of foiling most address harvesting spam bots. For example:
     * >
     * >     <a href="&#x6D;&#97;&#105;&#108;&#x74;&#111;:&#102;&#111;&#111;&#64;&#101;
     * >     x&#x61;&#109;&#x70;&#108;&#x65;&#x2E;&#99;&#111;&#109;">&#102;&#111;&#111;
     * >     &#64;&#101;x&#x61;&#109;&#x70;&#108;&#x65;&#x2E;&#99;&#111;&#109;</a>
     * >
     * > Based by a filter by Matthew Wickline, posted to the BBEdit-Talk
     * > mailing list: <http://tinyurl.com/yu7ue>
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string An obfuscated mailto anchor.
     * 
     */
    protected function _parseEmail($matches)
    {
        $addr = $matches[1];
        $addr = "mailto:" . $addr;
        $length = strlen($addr);
        
        // leave ':' alone (to spot mailto: later)
        // this is super-slow; it makes the callback one time for each
        // character in the string except ':'.
        $addr = preg_replace_callback(
            '/([^\:])/',
            array($this, '_obfuscateEmail'),
            $addr
        );
        
        $addr = "<a href=\"$addr\">$addr</a>";
        
        // strip the mailto: from the visible part
        $addr = preg_replace('/">.+?:/', '">', $addr);
        
        // done
        return $this->_toHtmlToken($addr);
    }
    
    /**
     * 
     * Obfuscates email addresses with hex and decimal entities.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _obfuscateEmail($matches) {
        $char = $matches[1];
        $r = rand(0, 100);
        // roughly 10% raw, 45% hex, 45% dec
        // '@' *must* be encoded. I insist.
        if ($r > 90 && $char != '@') return $char;
        if ($r < 45) return '&#x'.dechex(ord($char)).';';
        return '&#'.ord($char).';';
    }
}
