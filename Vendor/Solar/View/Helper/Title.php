<?php
/**
 * 
 * Helper for title tags.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Title.php 4569 2010-05-15 19:20:31Z pmjones $
 * 
 */
class Solar_View_Helper_Title extends Solar_View_Helper
{
    /**
     * 
     * Returns a <title ... /> tag.
     * 
     * @param string $text The title string.
     * 
     * @param bool $raw When true, output the title without escaping; default
     * false.
     * 
     * @return string The <title ... /> tag.
     * 
     */
    public function title($text, $raw = false)
    {
        if (! $raw) {
            $text = $this->_view->escape($text);
        }
        
        return "<title>{$text}</title>";
    }
}
