<?php
/**
 * 
 * Block class to form definition lists.
 * 
 * Syntax is ...
 * 
 *     term
 *     : definition
 *     
 *     term1
 *     term2
 *     : definition
 *     
 *     term
 *     : definition 1
 *     : definition 2
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: VariableList.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_VariableList extends Solar_Markdown_Extra_DefList
{
    /**
     * 
     * The tag to open a variable-list term.
     * 
     * @var string
     * 
     */
    protected $_dt_open = "<term>";
    
    /**
     * 
     * The tag to close a variable-list term.
     * 
     * @var string
     * 
     */
    protected $_dt_close = "</term>";
    
    /**
     * 
     * The tag to open a variable-list item.
     * 
     * @var string
     * 
     */
    protected $_dd_open = "<listitem><para>";
    
    /**
     * 
     * The tag to close a variable-list item.
     * 
     * @var string
     * 
     */
    protected $_dd_close = "</para></listitem>";
    
    /**
     * 
     * Support callback for variable lists.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $list   = $matches[1];
        $result = trim($this->_processItems($list));
        $result = "<variablelist>\n\n<varlistentry>\n"
                . $result
                . "\n</varlistentry>\n\n</variablelist>";
        return $this->_toHtmlToken($result) . "\n\n";
    }
    
    /**
     * 
     * Process the contents of a definition list, splitting it into
     * individual list items.
     * 
     * @param string $list_str The source text of the list block.
     * 
     * @return string The replacement text.
     * 
     */
    protected function _processItems($list_str)
    {
        // trim trailing blank lines:
        $list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);
        
        // process terms and items
        $list_str = parent::_processItems($list_str);
        
        // wrap the entry blocks (i.e., each block of 1+ terms with 1+ items)
        $list_str = str_replace(
            "</listitem>\n\n<term>",
            "</listitem>\n</varlistentry>\n\n<varlistentry>\n<term>",
            $list_str
        );
        
        return $list_str;
    }
}
