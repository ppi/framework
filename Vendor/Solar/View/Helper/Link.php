<?php
/**
 * 
 * Helper for links.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Link.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_Link extends Solar_View_Helper
{
    /**
     * 
     * Returns a <link ... /> tag.
     * 
     * @param string $attribs The specification array, typically
     * with keys 'rel' and 'href'.
     * 
     * @return string The <link ... /> tag.
     * 
     */
    public function link($attribs)
    {
        return '<link' . $this->_view->attribs($attribs) . ' />';
    }
}
