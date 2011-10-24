<?php
/**
 * 
 * Helper for a 'time' pseudo-element.
 * 
 * For an element named 'foo[bar]', builds a series of selects:
 * 
 * - foo[bar][H] : 00-23
 * - foo[bar][i] : 00-59
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormTime.php 3366 2008-08-26 01:36:49Z pmjones $
 * 
 */
class Solar_View_Helper_FormTime extends Solar_View_Helper_FormTimestamp
{
    /**
     * 
     * Helper for a 'time' pseudo-element.
     * 
     * For an element named 'foo[bar]', returns a series of selects:
     * 
     * - foo[bar][H] : 00-23
     * - foo[bar][i] : 00-59
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formTime($info)
    {
        $this->_prepare($info);
        return $this->_selectHour()  . ':'
             . $this->_selectMinute();
    }
}
