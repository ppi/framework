<?php
/**
 * 
 * Helper for base tags.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Base.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_Base extends Solar_View_Helper
{
    /**
     * 
     * Returns a <base ... /> tag.
     * 
     * @param string|Solar_Uri $spec The base HREF.
     * 
     * @return string The <base ... /> tag.
     * 
     */
    public function base($spec)
    {
        if ($spec instanceof Solar_Uri) {
            
            // work with a copy of the spec
            $uri = clone($spec);
            
            // remove any current path and query
            $uri->setPath(null);
            $uri->setQuery(null);
            
            // use that as the base
            $href = $uri->get(true);
            
        } else {
            $href = $spec;
        }
        
        $href = $this->_view->escape($href);
        return "<base href=\"$href\" />";
    }
}
