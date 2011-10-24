<?php
/**
 * 
 * Helper for <style>...</style> tags.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Style.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_Style extends Solar_View_Helper
{
    /**
     * 
     * Returns a <style>...</style> tag.
     * 
     * Adds "media" attribute if not specified, and always uses
     * "type" attribute of "text/css".
     * 
     * @param string $href The source href for the stylesheet.
     * 
     * @param array $attribs Additional attributes for the <style> tag.
     * 
     * @return string The <style>...</style> tag.
     * 
     */
    public function style($href, $attribs = null)
    {
        settype($attribs, 'array');
        
        // force type="text/css"
        $attribs['type'] = 'text/css';
        
        // default to media="screen"
        if (empty($attribs['media'])) {
            $attribs['media'] = 'screen';
        }
        
        // build the URL as an href to a public resource,
        // get it back raw.
        $url = $this->_view->publicHref($href, true);
        
        // build and return the tag
        return '<style' . $this->_view->attribs($attribs) . '>'
             . "@import url(\"$url\");</style>";
    }
}
