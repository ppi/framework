<?php
/**
 * 
 * Helper for action image anchors.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: ActionImage.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_View_Helper_ActionImage extends Solar_View_Helper
{
    /**
     * 
     * Returns an action image anchor.
     * 
     * @param string|Solar_Uri_Action $spec The action specification.
     * 
     * @param string $src The href to the image source.
     * 
     * @param array $attribs Additional attributes for the image tag.
     * 
     * @return string An <a href="..."><img ... /></a> tag set.
     * 
     * @see [[Solar_View_Helper_Image]]
     * 
     */
    public function actionImage($spec, $src, $attribs = array())
    {
        // get an escaped href action value
        $href = $this->_view->actionHref($spec);
        
        // get the <img /> tag
        $img = $this->_view->image($src, $attribs);
        
        // done!
        return "<a href=\"$href\">$img</a>";
    }
}
