<?php
/**
 * 
 * Span plugin to insert emphasis and strong tags.
 * 
 * * `*foo*` and `_foo_` become `<em>foo</em>`.
 * 
 * * `**bar**` and `__bar__` become `<strong>bar</strong>`.
 * 
 * * `***zim***` and `___zim___` become `<strong><em>zim</em></strong>`.
 * 
 * * `**_zim_**` and `__*zim*__` become `<strong><em>zim</em></strong>`.
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
 * @version $Id: EmStrong.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_EmStrong extends Solar_Markdown_Plugin
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
     * Report these as special Markdown characters to be encoded.
     * 
     * @var string
     * 
     */
    protected $_chars = '*_';
    
    /**
     * 
     * Converts emphasis and strong text.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        // <strong> must go first:
        $text = preg_replace_callback('{
                (                                       # $1: Marker
                    (?<!\*\*) \*\* |                    #     (not preceded by two chars of
                    (?<!__)   __                        #      the same marker)
                )                       
                (?=\S)                                  # Not followed by whitespace 
                (?!\1)                                  #   or two others marker chars.
                (                                       # $2: Content
                    (?:                 
                        [^*_]+?                         # Anthing not em markers.
                    |
                                                        # Balance any regular emphasis inside.
                        ([*_]) (?=\S) .+? (?<=\S) \3    # $3: em char (* or _)
                    |                                   
                        (?! \1 ) .                      # Allow unbalanced * and _.
                    )+?                                 
                )                                       
                (?<=\S) \1                              # End mark not preceded by whitespace.
            }sx',
            array($this, '_parseStrong'),
            $text
        );
        
        // Then <em>:
        $text = preg_replace_callback(
            '{ ( (?<!\*)\* | (?<!_)_ ) (?=\S) (?! \1) (.+?) (?<=\S) \1 }sx',
            array($this, '_parseEm'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for strong tags.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseStrong($matches)
    {
        return $this->_toHtmlToken("<strong>")
             . $matches[2]
             . $this->_toHtmlToken("</strong>");
    }
    
    /**
     * 
     * Support callback for em tags.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseEm($matches)
    {
        return $this->_toHtmlToken("<em>")
             . $matches[2]
             . $this->_toHtmlToken("</em>");
    }
}
