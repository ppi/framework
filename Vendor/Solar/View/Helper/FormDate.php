<?php
/**
 * 
 * Helper for a 'date' pseudo-element.
 * 
 * For an element named 'foo[bar]', builds a series of selects:
 * 
 * - foo[bar][Y] : {:y_first} - {:y_last}
 * - foo[bar][m] : 01-12
 * - foo[bar][d] : 01-31
 * 
 * This helper makes use of two extra element information keys: `y_first`
 * to determine the first year shown, and `y_last` as the last year shown.
 * The default values are -4 years from the current year, and +4 years from
 * the current year, respectively.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormDate.php 4428 2010-02-23 22:47:40Z pmjones $
 * 
 */
class Solar_View_Helper_FormDate extends Solar_View_Helper_FormTimestamp
{
    /**
     * 
     * Helper for a 'date' pseudo-element.
     * 
     * For an element named 'foo[bar]', builds a series of selects:
     * 
     * - foo[bar][Y] : {:y_first} - {:y_last}
     * - foo[bar][m] : 01-12
     * - foo[bar][d] : 01-31
     * 
     * This helper makes use of two extra element information keys: `y_first`
     * to determine the first year shown, and `y_last` as the last year shown.
     * The default values are -4 years from the current year, and +4 years
     * from the current year, respectively.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formDate($info)
    {
        $this->_prepare($info);
        return $this->_selectYear()  . '-'
             . $this->_selectMonth() . '-'
             . $this->_selectDay();
    }
}
