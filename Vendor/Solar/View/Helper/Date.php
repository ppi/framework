<?php
/**
 * 
 * Helper for a formatted date using [[php::date() | ]] format codes.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Date.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_View_Helper_Date extends Solar_View_Helper_Timestamp
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string format The default output formatting using [[php::date() | ]] codes.
     *   Default is 'Y-m-d'.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_Date = array(
        'format' => 'Y-m-d',
    );
    
    /**
     * 
     * Outputs a formatted date.
     * 
     * @param string $spec Any date-time string suitable for
     * strtotime().
     * 
     * @param string $format An optional custom [[php::date() | ]]
     * formatting string; null by default.
     * 
     * @return string The formatted date string.
     * 
     */
    public function date($spec, $format = null)
    {
        return $this->_process($spec, $format);
    }
}
