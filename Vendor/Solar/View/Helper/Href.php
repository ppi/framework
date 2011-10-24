<?php
/**
 * 
 * Helper to build an escaped href or src attribute value for a generic URI.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Href.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_Href extends Solar_View_Helper
{
    /**
     * 
     * Returns an escaped href or src attribute value for a generic URI.
     * 
     * @param Solar_Uri|string $spec The href or src specification.
     * 
     * @return string
     * 
     */
    public function href($spec)
    {
        if ($spec instanceof Solar_Uri) {
            // fetch the full href, not just the path/query/fragment
            $href = $spec->get(true);
        } else {
            $href = $spec;
        }
        
        return $this->_view->escape($href);
    }
}