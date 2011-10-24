<?php
/**
 * 
 * Helper for <link rel="stylesheet" ... /> tags.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: LinkStylesheet.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_LinkStylesheet extends Solar_View_Helper
{
    /**
     * 
     * Returns a <link rel="stylesheet" ... /> tag.
     * 
     * @param string $href The source href for the stylesheet.
     * 
     * @param array $attribs Additional attributes for the <link> tag.
     * 
     * @return string The <link ... /> tag.
     * 
     */
    public function linkStylesheet($href, $attribs = null)
    {
        settype($attribs, 'array');
        $attribs['rel'] = 'stylesheet';
        $attribs['type'] = 'text/css';
        if (empty($attribs['media'])) {
            $attribs['media'] = 'screen';
        }
        $attribs['href'] = $this->_view->publicHref($href, true);
        return $this->_view->link($attribs);
    }
}
