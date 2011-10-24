<?php
/**
 * 
 * Helper for a 'timestamp' pseudo-element.
 * 
 * For an element named 'foo[bar]', builds a series of selects:
 * 
 * - foo[bar][Y] : {:y_first} - {:y_last}
 * - foo[bar][m] : 01-12
 * - foo[bar][d] : 01-31
 * - foo[bar][H] : 00-24
 * - foo[bar][i] : 00-59
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
 * @version $Id: FormTimestamp.php 4580 2010-05-17 01:21:14Z pmjones $
 * 
 */
class Solar_View_Helper_FormTimestamp extends Solar_View_Helper_FormElement
{
    /**
     * 
     * Default configuration values.
     * 
     * @config int y_first The default year to show first in the year options.
     * Defaults to four years before the current year.
     * 
     * @config int y_last The default year to show last in the year options.
     * Defaults to four years after the current year.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_FormTimestamp = array(
        'y_first' => null,
        'y_last'  => null,
    );
    
    /**
     * 
     * Pre-config override to set default y_first and y_last values.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        $year = date('Y');
        $this->_Solar_View_Helper_FormTimestamp['y_first'] = $year - 4;
        $this->_Solar_View_Helper_FormTimestamp['y_last']  = $year + 4;
    }
    
    /**
     * 
     * The default year to show first in the year options.
     * 
     * @var int
     * 
     */
    protected $_y_first;
    
    /**
     * 
     * The default year to show last in the year options.
     * 
     * @var int
     * 
     */
    protected $_y_last;
    
    /**
     * 
     * Helper for a 'timestamp' pseudo-element.
     * 
     * For an element named 'foo[bar]', builds a series of selects:
     * 
     * - foo[bar][Y] : {:y_first} - {:y_last}
     * - foo[bar][m] : 01-12
     * - foo[bar][d] : 01-31
     * - foo[bar][H] : 00-24
     * - foo[bar][i] : 00-59
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
    public function formTimestamp($info)
    {
        $this->_prepare($info);
        return $this->_selectYear()  . '-'
             . $this->_selectMonth() . '-'
             . $this->_selectDay()   . ' @ '
             . $this->_selectHour()  . ':'
             . $this->_selectMinute();
    }
    
    /**
     * 
     * Overrides the parent _prepare() to honor `y_first` and `y_last` element
     * info keys.
     * 
     * @param array $info An array of element information.
     * 
     * @return void
     * 
     */
    protected function _prepare($info)
    {
        parent::_prepare($info);
        
        if (array_key_exists('y_first', $info)) {
            $this->_y_first = $info['y_first'];
        } else {
            $this->_y_first = $this->_config['y_first'];
        }
        
        if (array_key_exists('y_last', $info)) {
            $this->_y_last = $info['y_last'];
        } else {
            $this->_y_last = $this->_config['y_last'];
        }
        
        // if the value is required but empty, fill with current timestamp
        if (! $this->_value && $this->_require) {
            $this->_value = date('Y-m-d H:i:s');
        }
    }
    
    /**
     * 
     * Looks up a part of the element value based on a date() format character
     * key.
     * 
     * @param string $key The date() format character key: Y, d, h, H, i.
     * 
     * @return string
     * 
     */
    protected function _getValue($key)
    {
        if (! $this->_value) {
            return null;
        }
        
        if (is_array($this->_value)) {
            if (array_key_exists($key, $this->_value)) {
                return $this->_value[$key];
            } else {
                return null;
            }
        }
        
        switch ($key) {
            
        // work forward, to support date-only values
        // 0123456789
        // 1970-01-23
        case 'Y':
            return substr($this->_value, 0, 4);
            break;
        case 'm':
            return substr($this->_value, 5, 2);
            break;
        case 'd':
            return substr($this->_value, 8, 2);
            break;
            
        // work backward, to support time-only values
        // 87654321
        // 01:23:45
        case 'H':
            return substr($this->_value, -8, 2);
            break;
        case 'i':
            return substr($this->_value, -5, 2);
            break;
        case 's':
            return substr($this->_value, -2, 2);
            break;
        }
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the year.
     * 
     * @return string
     * 
     */
    protected function _selectYear()
    {
        $name    = $this->_name . '[Y]';
        $value   = $this->_getValue('Y');
        $options = array('' => '----');
        $first   = $this->_y_first;
        $last    = $this->_y_last;
        
        if ($first <= $last) {
            // low to high
            for ($year = $first; $year <= $last; $year ++) {
                $options[$year] = str_pad($year, 4, '0', STR_PAD_LEFT);
            }
        } else {
            // high to low
            for ($year = $first; $year >= $last; $year --) {
                $options[$year] = str_pad($year, 4, '0', STR_PAD_LEFT);
            }
        }
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the month.
     * 
     * @return string
     * 
     */
    protected function _selectMonth()
    {
        $name    = $this->_name . '[m]';
        $value   = $this->_getValue('m');
        $options = array(
            '' => '--',
            '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04', '05'=>'05',
            '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09', '10'=>'10',
            '11'=>'11', '12'=>'12',
        );
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the day of the month.
     * 
     * @return string
     * 
     */
    protected function _selectDay()
    {
        $name    = $this->_name . '[d]';
        $value   = $this->_getValue('d');
        $options = array(
            '' => '--',
            '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04', '05'=>'05',
            '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09', '10'=>'10',
            '11'=>'11', '12'=>'12', '13'=>'13', '14'=>'14', '15'=>'15',
            '16'=>'16', '17'=>'17', '18'=>'18', '19'=>'19', '20'=>'20',
            '21'=>'21', '22'=>'22', '23'=>'23', '24'=>'24', '25'=>'25',
            '26'=>'26', '27'=>'27', '28'=>'28', '29'=>'29', '30'=>'30',
            '31'=>'31',
        );
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the hour.
     * 
     * @return string
     * 
     */
    protected function _selectHour()
    {
        $name    = $this->_name . '[H]';
        $value   = $this->_getValue('H');
        $options = array(
            '' => '--',
            '00'=>'00', '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04',
            '05'=>'05', '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09',
            '10'=>'10', '11'=>'11', '12'=>'12', '13'=>'13', '14'=>'14',
            '15'=>'15', '16'=>'16', '17'=>'17', '18'=>'18', '19'=>'19',
            '20'=>'20', '21'=>'21', '22'=>'22', '23'=>'23', '24'=>'24',
        );
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
    
    /**
     * 
     * Returns a <select>...</select> tag for the minute.
     * 
     * @return string
     * 
     */
    protected function _selectMinute()
    {
        $name    = $this->_name . '[i]';
        $value   = $this->_getValue('i');
        $options = array(
            '' => '--',
            '00'=>'00', '01'=>'01', '02'=>'02', '03'=>'03', '04'=>'04',
            '05'=>'05', '06'=>'06', '07'=>'07', '08'=>'08', '09'=>'09',
            '10'=>'10', '11'=>'11', '12'=>'12', '13'=>'13', '14'=>'14',
            '15'=>'15', '16'=>'16', '17'=>'17', '18'=>'18', '19'=>'19',
            '20'=>'20', '21'=>'21', '22'=>'22', '23'=>'23', '24'=>'24',
            '25'=>'25', '26'=>'26', '27'=>'27', '28'=>'28', '29'=>'29',
            '30'=>'30', '31'=>'31', '32'=>'32', '33'=>'33', '34'=>'34',
            '35'=>'35', '36'=>'36', '37'=>'37', '38'=>'38', '39'=>'39',
            '40'=>'40', '41'=>'41', '42'=>'42', '43'=>'43', '44'=>'44',
            '45'=>'45', '46'=>'46', '47'=>'47', '48'=>'48', '49'=>'49',
            '50'=>'50', '51'=>'51', '52'=>'52', '53'=>'53', '54'=>'54',
            '55'=>'55', '56'=>'56', '57'=>'57', '58'=>'58', '59'=>'59',
        );
        
        return $this->_view->formSelect(array(
            'name'    => $name,
            'value'   => $value,
            'options' => $options,
        )) . "\n";
    }
}
