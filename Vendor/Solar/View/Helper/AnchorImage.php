<?php
/**
 * 
 * Helper for anchored images.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: AnchorImage.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_View_Helper_AnchorImage extends Solar_View_Helper
{
    /**
     * 
     * Returns an image wrapped by an anchor href tag.
     * 
     * @param Solar_Uri|string $spec The anchor href specification.
     * 
     * @param string $src The href to the image source.
     * 
     * @param array $a_attribs Additional attributes for the anchor.
     * 
     * @param array $img_attribs Additional attributes for the image.
     * 
     * @return string An <a href="..."><img ... /></a> tag set.
     * 
     * @see [[Solar_View_Helper_Image]]
     * 
     */
    public function anchorImage($spec, $src, $a_attribs = array(),
        $img_attribs = array())
    {
        // get an escaped href value
        $href = $this->_view->href($spec);
        
        // get the <img /> tag
        $img = $this->_view->image($src, $img_attribs);
        
        // get the anchor attribs
        settype($a_attribs, 'array');
        unset($a_attribs['href']);
        $attr = $this->_view->attribs($a_attribs);
        
        // build the full anchor/img tag set
        return "<a href=\"$href\"$attr>$img</a>";
    }
}
