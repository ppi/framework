<?php
/**
 * 
 * Helper for <script> tags from a public Solar resource.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Script.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_Script extends Solar_View_Helper
{
    /**
     * 
     * Default configuration values.
     * 
     * @config bool anti_cache When true, paths in the src attribute will have a
     * cache-busting query string appended to them.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_Script = array(
        'anti_cache' => false,
    );
    
    /**
     * 
     * Returns a <script></script> tag.
     * 
     * @param string $src The source href for the script.
     * 
     * @param array $attribs Additional attributes for the <script> tag.
     * 
     * @return string The <script></script> tag.
     * 
     */
    public function script($src, $attribs = null)
    {
        settype($attribs, 'array');
        unset($attribs['src']);
        
        $src = $this->_view->publicHref($src);
        
        if ($this->_config['anti_cache']) {
            $src .= '?' . date('U');
        }
        
        if (empty($attribs['type'])) {
            $attribs['type'] = 'text/javascript';
        }
        
        return "<script src=\"$src\""
             . $this->_view->attribs($attribs) . '></script>';
    }
}
