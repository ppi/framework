<?php
/**
 * 
 * Block plugin to create ordered and unordered lists.
 * 
 * Start a line with `-`, `+`, or `*` (and a space) to
 * indicate an unordered bullet list.
 * 
 * Start a line with a number and period (and a space)
 * (for example `1. `) to indicate a numbered list.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: List.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_List extends Solar_Markdown_Plugin_List
{
    /**
     * 
     * The tag to open an unordered (bulleted) list.
     * 
     * @var string
     * 
     */
    protected $_ul_open  = "<itemizedlist>";
    
    /**
     * 
     * The tag to close an unordered (bulleted) list.
     * 
     * @var string
     * 
     */
    protected $_ul_close = "</itemizedlist>";
    
    /**
     * 
     * The tag to open an ordered (numbered) list.
     * 
     * @var string
     * 
     */
    protected $_ol_open  = "<orderedlist>";
    
    /**
     * 
     * The tag to close an ordered (numbered) list.
     * 
     * @var string
     * 
     */
    protected $_ol_close = "</orderedlist>";
    
    /**
     * 
     * The tag to open a list item.
     * 
     * @var string
     * 
     */
    protected $_li_open  = "<listitem>";
    
    /**
     * 
     * The tag to close a list item.
     * 
     * @var string
     * 
     */
    protected $_li_close = "</listitem>";
    
    /**
     * 
     * Support callback for processing list items.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _processItemsCallback($matches)
    {
        $item          = $this->_outdent($matches[4]);
        $leading_line  =& $matches[1];
        $leading_space =& $matches[2];
        $has_paras     = preg_match('/\n{2,}/', $item);
        
        if ($leading_line || $has_paras) {
            $item = $this->_processBlocks($item);
        } else {
            // Recursion for sub-lists:
            $item = $this->parse($item);
            $item = preg_replace('/\n+$/', '', $item);
            $item = $this->_processSpans($item);
        }
        
        if (! $leading_line && ! $has_paras) {
            $item = "<para>$item</para>";
        }
        
        return $this->_li_open . $item . $this->_li_close . "\n";
    }
}
