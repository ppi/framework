<?php
/**
 * 
 * Block plugin to save literal blocks of HTML.
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
 * @version $Id: Html.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_Html extends Solar_Markdown_Plugin
{
    /**
     * 
     * Run this plugin during the "prepare" phase.
     * 
     * @var bool
     * 
     */
    protected $_is_prepare = true;
    
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
     * Run this plugin during the "cleanup" phase.
     * 
     * @var bool
     * 
     */
    protected $_is_cleanup = true;
    
    /**
     * 
     * When preparing text for parsing, remove pre-existing HTML blocks.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function prepare($text)
    {
        return $this->parse($text);
    }
    
    /**
     * 
     * When cleaning up after parsing, replace all HTML tokens with
     * their saved blocks.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function cleanup($text)
    {
        return $this->_unHtmlToken($text);
    }
    
    /**
     * 
     * Removes HTML blocks and replaces with delimited tokens.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $less_than_tab = $this->_getTabWidth() - 1;
        
        // We only want to do this for block-level HTML tags, such as
        // headers, lists, and tables. That's because we still want to
        // wrap <p>s around "paragraphs" that are wrapped in
        // non-block-level tags, such as anchors, phrase emphasis, and
        // spans. The list of tags we're looking for is hard-coded:
        $block_tags_a = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|'.
                        'script|noscript|form|fieldset|iframe|math|ins|del';
        
        $block_tags_b = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|'.
                        'script|noscript|form|fieldset|iframe|math';
        
        // First, look for nested blocks, for example:
        // 
        //     <div>
        //         <div>
        //         tags for inner block must be indented.
        //         </div>
        //     </div>
        //
        // The outermost tags must start at the left margin for this to
        // match, and the inner nested divs must be indented.
        // 
        // We need to do this before the next, more liberal match,
        // because the next match will start at the first `<div>` and
        // stop at the first `</div>`.
        $text = preg_replace_callback("{
                    (                           # save in $1
                        ^                       # start of line  (with /m)
                        <($block_tags_a)        # start tag = $2
                        \\b                     # word break
                        (.*\\n)*?               # any number of lines, minimally matching
                        </\\2>                  # the matching end tag
                        [ \\t]*                 # trailing spaces/tabs
                        (?=\\n+|\\Z)            # followed by a newline or end of document
                    )
            }xm",
            array($this, '_parse'),
            $text
        );
        
        // Now match more liberally, simply from `\n<tag>` to `</tag>\n`
        $text = preg_replace_callback("{
                    (                           # save in $1
                        ^                       # start of line  (with /m)
                        <($block_tags_b)        # start tag = $2
                        \\b                     # word break
                        (.*\\n)*?               # any number of lines, minimally matching
                        .*</\\2>                # the matching end tag
                        [ \\t]*                 # trailing spaces/tabs
                        (?=\\n+|\\Z)            # followed by a newline or end of document
                    )
            }xm",
            array($this, '_parse'),
            $text
        );
        
        // Special case just for <hr />. It was easier to make a special
        // case than to make the other regex more complicated.
        $text = preg_replace_callback('{
                (?:
                    (?<=\n\n)                   # Starting after a blank line
                    |                           # or
                    \A\n?                       # the beginning of the doc
                )
                (                               # save in $1
                    [ ]{0,'.$less_than_tab.'}
                    <(hr)                       # start tag = $2
                    \b                          # word break
                    ([^<>])*?                   # 
                    /?>                         # the matching end tag
                    [ \t]*
                    (?=\n{2,}|\Z)               # followed by a blank line or end of document
                )
            }x',
            array($this, '_parse'),
            $text
        );
        
        // Special case for standalone HTML comments:
        $text = preg_replace_callback('{
                (?:
                    (?<=\n\n)                   # Starting after a blank line
                    |                           # or
                    \A\n?                       # the beginning of the doc
                )
                (                               # save in $1
                    [ ]{0,'.$less_than_tab.'}
                    (?s:
                        <!
                        (--.*?--\s*)+
                        >
                    )
                    [ \t]*
                    (?=\n{2,}|\Z)               # followed by a blank line or end of document
                )
            }x',
            array($this, '_parse'),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Support callback for HTML blocks.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        return "\n\n"
             . $this->_toHtmlToken($matches[1])
             ."\n\n";
    }
}
