<?php
/**
 * 
 * Block plugin to add horizontal rule tags.
 * 
 * Start a line with three or more `*`, `-`, or `_` characters.  You can
 * have spaces between them if you like.
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
 * @version $Id: HorizRule.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Markdown_Plugin_HorizRule extends Solar_Markdown_Plugin
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
    protected $_chars = '-_*';
    
    /**
     * 
     * Replaces markup for horizontal rules.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        return preg_replace_callback(
            array('{^[ ]{0,2}([ ]?\*[ ]?){3,}[ \t]*$}mx',
                  '{^[ ]{0,2}([ ]? -[ ]?){3,}[ \t]*$}mx',
                  '{^[ ]{0,2}([ ]? _[ ]?){3,}[ \t]*$}mx'),
            array($this, '_parse'),
            $text
        );
    }
    
    /**
     * 
     * Support callback for horizontal rules.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        return "\n" . $this->_toHtmlToken("<hr />") . "\n";
    }
}
