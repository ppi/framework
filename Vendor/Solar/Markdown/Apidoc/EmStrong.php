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
 * @package Solar_Markdown_Apidoc
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: EmStrong.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_EmStrong extends Solar_Markdown_Extra_EmStrong
{
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
        return $this->_toHtmlToken('<emphasis role="strong">')
             . $matches[2]
             . $this->_toHtmlToken('</emphasis>');
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
        return $this->_toHtmlToken('<emphasis>')
             . $matches[2]
             . $this->_toHtmlToken('</emphasis>');
    }
}
