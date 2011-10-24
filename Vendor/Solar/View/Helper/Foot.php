<?php
/**
 * 
 * Helper to collect elements for the footer of the body and display them in
 * the correct order; generally used only for scripts.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Foot.php 4600 2010-06-16 03:27:55Z pmjones $
 * 
 */
class Solar_View_Helper_Foot extends Solar_View_Helper
{
    /**
     * 
     * The indent string for each element; default is 4 spaces.
     * 
     * @var string
     * 
     */
    protected $_indent = '    ';
    
    /**
     * 
     * Array of baseline <script> values that come before all other scripts.
     * 
     * @var array
     * 
     */
    protected $_script_base = array();
    
    /**
     * 
     * Array of additional <script> values that come after the baseline
     * scripts.
     * 
     * @var array
     * 
     */
    protected $_script = array();
    
    /**
     * 
     * Array of inline <script> code.
     * 
     * @var array
     * 
     */
    protected $_script_inline = array();
    
    /**
     * 
     * Main helper method; fluent interface.
     * 
     * @return Solar_View_Helper_Head
     * 
     */
    public function foot()
    {
        return $this;
    }
    
    /**
     * 
     * Sets the indent string.
     * 
     * @param string $indent The indent string.
     * 
     * @return Solar_View_Helper_Head
     * 
     */
    public function setIndent($indent)
    {
        $this->_indent = $indent;
        return $this;
    }
    
    /**
     * 
     * Adds a <script> tag as part of the "baseline" (foundation) scripts.
     * Generally used by layouts, not views.  If the file has already been
     * added, it does not get added again.
     * 
     * @param string $src The file HREF for the script source.
     * 
     * @param array $attribs Attributes for the tag.
     * 
     * @return Solar_View_Helper_Head
     * 
     */
    public function addScriptBase($src, $attribs = null)
    {
        if (empty($this->_script_base[$src])) {
            $this->_script_base[$src] = array($src, (array) $attribs);
        }
        return $this;
    }
    
    /**
     * 
     * Adds a <script> tag as part of the "additional" (override) scripts.
     * Generally used by views, not layouts.  If the file has already been
     * added, it does not get added again.
     * 
     * @param string $src The file HREF for the script source.
     * 
     * @param array $attribs Attributes for the tag.
     * 
     * @return Solar_View_Helper_Head
     * 
     */
    public function addScript($src, $attribs = null)
    {
        if (empty($this->_script[$src])) {
            $this->_script[$src] = array($src, (array) $attribs);
        }
        return $this;
    }
    
    /**
     * 
     * Adds a <script> tag with inline code.
     * 
     * @param string $code The inline code for the tag.
     * 
     * @return Solar_View_Helper_Head
     * 
     */
    public function addScriptInline($code)
    {
        $this->_script_inline[] = $code;
        return $this;
    }
    
    /**
     * 
     * Builds and returns all the tags for the <head> section.
     * 
     * @return Solar_View_Helper_Head
     * 
     */
    public function fetch()
    {
        // array of lines for HTML output
        $html = array();
        
        // baseline scripts
        foreach ((array) $this->_script_base as $val) {
            $html[] = $this->_view->script($val[0], $val[1]);
        }
        
        // additional scripts (source)
        foreach ((array) $this->_script as $val) {
            $html[] = $this->_view->script($val[0], $val[1]);
        }
        
        // inline scripts collected into a single block
        $code = $this->_fetchScriptInline();
        if ($code) {
            $html[] = $this->_view->scriptInline($code);
        }
        
        // concat with indents and newlines, and done!
        return $this->_indent
             . implode("\n{$this->_indent}", $html)
             . "\n";
    }
    
    /**
     * 
     * Support method to fetch inline scripts; child classes may wish to
     * override this to wrap in a library-specific "when document is ready"
     * logic.
     * 
     * @return string The code for all inline scripts.
     * 
     */
    protected function _fetchScriptInline()
    {
        $code = null;
        foreach ((array) $this->_script_inline as $val) {
            $code .= $val . "\n\n";
        }
        return rtrim($code);
    }
}