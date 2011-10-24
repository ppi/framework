<?php
/**
 * 
 * Helper to build an escaped href or src attribute value for a named action
 * from the rewrite rules using data interpolation.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: NamedActionHref.php 4515 2010-03-15 16:42:04Z pmjones $
 * 
 */
class Solar_View_Helper_NamedActionHref extends Solar_View_Helper
{
    /**
     * 
     * Returns an escaped href or src attribute value for a named action
     * from the rewrite rules, using data interpolation.
     * 
     * @param string $name The named action from the rewrite rules.
     * 
     * @param array $data Data to interpolate into the token placeholders.
     * 
     * @return string
     * 
     */
    public function namedActionHref($name, $data = null)
    {
        $uri  = $this->_view->namedActionUri($name, $data);
        $href = $uri->get();
        return $this->_view->escape($href);
    }
}
