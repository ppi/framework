<?php
/**
 * 
 * Helper for meta name tags.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: MetaName.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_MetaName extends Solar_View_Helper
{
    /**
     * 
     * Returns a <meta name="" content="" /> tag.
     * 
     * @param string $name The name value.
     * 
     * @param string $content The content value.
     * 
     * @return string The <meta name="" content="" /> tag.
     * 
     */
    public function metaName($name, $content)
    {
        $spec = array(
            'name' => $name,
            'content' => $content,
        );
        return '<meta' . $this->_view->attribs($spec) . ' />';
    }
}
