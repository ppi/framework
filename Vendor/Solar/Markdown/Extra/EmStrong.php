<?php
/**
 * 
 * Span plugin to insert emphasis and strong tags.
 * 
 * Differs from default Markdown in that underscores and stars inside a
 * word will not trigger the markup.
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
 * @version $Id: EmStrong.php 3732 2009-04-29 17:27:56Z pmjones $
 * 
 */
class Solar_Markdown_Extra_EmStrong extends Solar_Markdown_Plugin_EmStrong
{
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
        $text = preg_replace_callback(
            array(
                '{                                                  # __strong__
                    ( (?<!\w) __ )                                  # $1: Marker (not preceded by alphanum)
                    (?=\S)                                          # Not followed by whitespace 
                    (?!__)                                          #   or two others marker chars.
                    (                                               # $2: Content
                        (?>                                         
                            [^_]+?                                  # Anthing not em markers.
                        |                                           
                                                                    # Balance any regular _ emphasis inside.
                            (?<![a-zA-Z0-9])_ (?=\S) (?! _) (.+?) 
                            (?<=\S) _ (?![a-zA-Z0-9])
                        )+?
                    )
                    (?<=\S) __                                      # End mark not preceded by whitespace.
                    (?!\w)                                          # Not followed by alphanum.
                }sx',                                               
                '{                                                  # **strong**
                    ( (?<!\*\*) \*\* )                              # $1: Marker (not preceded by two *)
                    (?=\S)                                          # Not followed by whitespace 
                    (?!\1)                                          #   or two others marker chars.
                    (                                               # $2: Content
                        (?>                                         
                            [^*]+?                                  # Anthing not em markers.
                        |                                           
                                                                    # Balance any regular * emphasis inside.
                            \* (?=\S) (?! \*) (.+?) (?<=\S) \*
                        )+?
                    )
                    (?<=\S) \*\*                                    # End mark not preceded by whitespace.
                }sx',
            ),
            array($this, '_parseStrong'),
            $text
        );
        
        // Then <em>:
        $text = preg_replace_callback(
            array(
                '{ ( (?<!\w) _ ) (?=\S) (?! _)  (.+?) (?<=\S) _ (?!\w) }sx',
                '{ ( (?<!\*)\* ) (?=\S) (?! \*) (.+?) (?<=\S) \* }sx',
            ),
            array($this, '_parseEm'),
            $text
        );
        
        return $text;
    }
}
