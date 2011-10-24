<?php
/**
 * 
 * Helper for a formatted timestamp using [[php::date() | ]] format codes.
 * 
 * Default format is "Y-m-d H:i:s".
 * 
 * Note that this helper is timezone-aware.  For example, if all your input
 * timestamps are in the GMT timezone, but you want to show them as being in the
 * America/Chicago timezone, you can set these config keys:
 * 
 * {{code: php
 *     $config['Solar_View_Helper_Timestamp']['tz_origin'] = 'GMT';
 *     $config['Solar_View_Helper_Timestamp']['tz_output'] = 'America/Chicago';
 * }}
 * 
 * Then when you call call the timestamp helper, it will move the input time
 * back by 5 hours (or by 6, during daylight savings time) and output that 
 * instead of the GMT time.
 * 
 * This works for arbitrary timezones, so you can have your input times in any
 * timezone and convert them to any other timezone.
 * 
 * Note that Solar_View_Helper_Date and Solar_View_Helper_Time descend from 
 * this helper, so you only need to set the timezone configs in one place (i.e.,
 * for this helper).
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Timestamp.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_View_Helper_Timestamp extends Solar_View_Helper
{
    /**
     * 
     * Default configuration values.
     * 
     * @config bool strftime When true, uses strftime() instead of date() for formatting 
     *   dates. Default is false.
     * 
     * @config string format The default output formatting using [[php::date() | ]] codes.
     *   When `strftime` is true, uses [[php::strftime() | ]] codes instead.
     *   Default is 'Y-m-d H:i:s' (using date() format codes).
     * 
     * @config string tz_origin Consider all input timestamps as being from this timezone.
     *   Default is the value of [[php::date_default_timezone_get() | ]].
     * 
     * @config string tz_output Output all timestamps after converting to this timezone.
     *   Default is the value of [[php::date_default_timezone_get() | ]].
     * 
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_Timestamp = array(
        'strftime'  => false,
        'format'    => 'Y-m-d H:i:s',
        'tz_origin' => null,
        'tz_output' => null,
    );
    
    /**
     * 
     * The timezone that date-time strings originate from.
     * 
     * @var string
     * 
     */
    protected $_tz_origin = null;
    
    /**
     * 
     * The timezone that date-time strings should be converted to before output.
     * 
     * @var string
     * 
     */
    protected $_tz_output = null;
    
    /**
     * 
     * The offset in seconds between the origin and output timezones.
     * 
     * This value will be added to the time (in seconds) before formatting for
     * output.
     * 
     * @var int
     * 
     */
    protected $_tz_offset = 0;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // set the origin timezone
        $this->_tz_origin = $this->_config['tz_origin'];
        if (! $this->_tz_origin) {
            $this->_tz_origin = date_default_timezone_get();
        }
        
        // set the output timezone
        $this->_tz_output = $this->_config['tz_output'];
        if (! $this->_tz_output) {
            $this->_tz_output = date_default_timezone_get();
        }
        
        // if different zones, determine the offset between them
        if ($this->_tz_origin != $this->_tz_output) {
            
            // origin timestamp
            $origin_tz     = new DateTimeZone($this->_tz_origin);
            $origin_date   = new DateTime('now', $origin_tz);
            $origin_offset = $origin_tz->getOffset($origin_date);
            if ($origin_offset < 0) {
                $origin_offset += (12 * 3600); // move forward 12 hours
            }
            
            // output timestamp
            $output_tz     = new DateTimeZone($this->_tz_output);
            $output_date   = new DateTime('now', $output_tz);
            $output_offset = $output_tz->getOffset($output_date);
            if ($output_offset < 0) {
                $output_offset += (12 * 3600); // move forward 12 hours
            }
            
            // retain the differential offset
            $this->_tz_offset = $output_offset - $origin_offset;
        }
    }
    
    /**
     * 
     * Outputs a formatted timestamp using [[php::date() | ]] format codes.
     * 
     * @param string $spec Any date-time string suitable for
     * strtotime().
     * 
     * @param string $format An optional custom [[php::date() | ]]
     * formatting string.
     * 
     * @return string The formatted date string.
     * 
     */
    public function timestamp($spec, $format = null)
    {
        return $this->_process($spec, $format);
    }
    
    /**
     * 
     * Outputs a formatted timestamp using [[php::date() | ]] format codes.
     * 
     * @param string|int $spec Any date-time string suitable for strtotime();
     * if an integer, will be used as a Unix timestamp as-is.
     * 
     * @param string $format An optional custom [[php::date() | ]] formatting
     * string.
     * 
     * @return string The formatted date string.
     * 
     */
    protected function _process($spec, $format)
    {
        // must have an explicit spec; empty *does not* mean "now"
        if (! $spec) {
            return;
        }
        
        if (! $format) {
            $format = $this->_config['format'];
        }
        
        if (is_int($spec)) {
            $time = $spec;
        } else {
            $time = strtotime($spec);
        }
        
        // move by the offset
        $time += $this->_tz_offset;
        
        // use strftime() or date()?
        if ($this->_config['strftime']) {
            $val = strftime($format, $time);
        } else {
            $val = date($format, $time);
        }
        
        return $this->_view->escape($val);
    }
}
