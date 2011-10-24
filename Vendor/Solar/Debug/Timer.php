<?php
/**
 * 
 * Tracks code execution times.
 * 
 * This class allows you to profile the execution time of your script:
 * you start the timer, set marks within your script, then stop the
 * timer and display the profile.
 * 
 * In the following example, we'll [[php::sleep() | ]] for random periods
 * under a second, then mark the timer profile.
 * 
 * {{code: php
 *     require_once 'Solar.php';
 *     Solar::start();
 * 
 *     // create a timer and start it
 *     $timer = Solar::factory('Solar_Debug_Timer');
 *     $timer->start();
 * 
 *     // loop and pause for a random period under 1 second,
 *     // then make a profile mark
 *     for ($i = 0; $i < 5; $i++) {
 *         time_nanosleep(0, rand(1,999999999));
 *         $timer->mark("iteration_$i");
 *     }
 * 
 *     // stop the timer and display the profile
 *     $timer->stop();
 *     $timer->display();
 * }}
 * 
 * The resulting profile might look something like this:
 * 
 *     name        : diff     : total   
 *     __start     : 0.000000 : 0.000000
 *     iteration_0 : 0.376908 : 0.376908
 *     iteration_1 : 0.395037 : 0.771945
 *     iteration_2 : 0.607002 : 1.378947
 *     iteration_3 : 0.202960 : 1.581907
 *     iteration_4 : 0.232987 : 1.814894
 *     __stop      : 0.000056 : 1.814950
 * 
 * In the above profile, the "name" is the mark name, the "diff"
 * is the difference (in seconds) from the previous mark, and 
 * the "total" is a running total (in seconds) for the execution
 * time.  Note that the "diff" for the __start mark is zero, as
 * there is no previous mark to get a difference from.
 * 
 * @category Solar
 * 
 * @package Solar_Debug Debugging tools.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Timer.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Debug_Timer extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string output Output mode.  Set to 'html' for HTML; 
     *   or 'text' for plain text.  Default autodetects by SAPI version.
     * 
     * @config bool auto_start When true, starts the timer at __construct() time.  Default false.
     * 
     * @config bool auto_display When true, calls display() at __destruct() time.  Default false.
     * 
     * @var array
     * 
     */
    protected $_Solar_Debug_Timer = array(
        'output'       => null,
        'auto_start'   => false,
        'auto_display' => false,
    );
    
    /**
     * 
     * Array of time marks.
     * 
     * @var array
     * 
     */
    protected $_marks = array();
    
    /**
     * 
     * The longest marker name length shown in profile().
     * 
     * @var boolean
     * 
     */
    protected $_maxlen = 8;
    
    /**
     * 
     * Modifies $this->_config after it has been built.
     * 
     * @return void
     * 
     */
    protected function _postConfig()
    {
        parent::_postConfig();
        if (empty($this->_config['output'])) {
            $mode = (PHP_SAPI == 'cli') ? 'text' 
                                        : 'html';
            $this->_config['output'] = $mode;
        }
    }
    
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
        if ($this->_config['auto_start']) {
            $this->start();
        }
    }
    
    /**
     * 
     * Destructor.
     * 
     * If the 'auto_display' config key is true, this will display the profile.
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
        if ($this->_config['auto_display']) {
            $this->display();
        }
    }
    
    
    /**
     * 
     * Resets the profile and marks a new starting time.
     * 
     * This resets the profile and adds a new mark labeled
     * `__start`.  Use it to start the timer.
     * 
     * @return void
     * 
     */
    public function start()
    {
        $this->_marks = array();
        $this->mark('__start');
    }
    
    /**
     * 
     * Stops the timer.
     * 
     * Use this to stop the timer, marking the time with the label `__stop`.
     * 
     * @return void
     * 
     */
    public function stop()
    {
        $this->mark('__stop');
    }
    
    /**
     * 
     * Marks the time.
     * 
     * Use this to mark the profile to see how much time has
     * elapsed since the last mark.  Labels do not have to be
     * unique, but should be distinctive enough so you can tell
     * which one is which on long profiles.
     * 
     * @param string $name Name of the marker to be set
     * 
     * @return void
     * 
     */
    public function mark($name)
    {
        $this->_marks[$name] = microtime(true);
    }
    
    /**
     * 
     * Returns profiling information as an array.
     * 
     * This will return the internal profile of marks as an array.
     * For example, given this code ...
     * 
     * {{code: php
     *     require_once 'Solar.php';
     *     Solar::start();
     * 
     *     $timer = Solar::factory('Solar_Debug_Timer');
     *     $timer->start();
     * 
     *     for ($i = 0; $i < 3; $i++) {
     *         time_nanosleep(0, rand(1,999999999));
     *         $timer->mark("iteration_$i");
     *     }
     * 
     *     $timer->stop();
     * 
     *     $profile = $timer->profile();
     *     Solar::dump($profile);
     * }}
     * 
     * ... the profile output might look like this:
     * 
     *     array(3) {
     *         [0] => array(4) {
     *             ["name"] => string(7) "__start"
     *             ["time"] => float(1121903570.8062)
     *             ["diff"] => int(0)
     *             ["total"] => int(0)
     *         }
     *         [1] => array(4) {
     *             ["name"] => string(11) "iteration_0"
     *             ["time"] => float(1121903571.1628)
     *             ["diff"] => float(0.35667991638184)
     *             ["total"] => float(0.35667991638184)
     *         }
     *         [2] => array(4) {
     *             ["name"] => string(11) "iteration_1"
     *             ["time"] => float(1121903571.6973)
     *             ["diff"] => float(0.53444910049438)
     *             ["total"] => float(0.89112901687622)
     *         }
     *     }
     * 
     * @return array An array of profile information.
     * 
     */
    public function profile()
    {
        // previous time
        $prev = 0;
        
        // total elapsed time
        $total = 0;
        
        // result array
        $result = array();
        
        // loop through all the marks
        foreach ($this->_marks as $name => $time) {
            
            // figure the time difference
            $diff = $time - $prev;
            
            // keep a running total; we always start at zero time.
            if ($name == '__start') {
                $total = 0;
            } else {
                $total = $total + $diff;
            }
            
            // record the profile result for this iteration
            $result[] = array(
                'name'  => $name,
                'time'  => $time,
                'diff'  => $diff,
                'total' => $total
            );
            
            // track the longest marker name
            if (strlen($name) > $this->_maxlen) {
                $this->_maxlen = strlen($name);
            }
            
            // track the previous time
            $prev = $time;
        }
        
        // by definition, the starting-point time-difference is zero
        $result[0]['diff'] = 0;
        
        // done!
        return $result;
    }
    
    /**
     * 
     * Fetches the current profile formatted as a table.
     * 
     * This fetches the profile information as a table; see the
     * [HomePage home page for this class] for an example.
     * 
     * @param string $title A title for the output.
     * 
     * @return string
     * 
     */
    public function fetch($title = null)
    {
        // get the profile info
        $profile = $this->profile();
        
        // format the localized column names
        $colname = array(
            'name'  => $this->locale('LABEL_NAME'),
            'time'  => $this->locale('LABEL_TIME'),
            'diff'  => $this->locale('LABEL_DIFF'),
            'total' => $this->locale('LABEL_TOTAL')
        );
        
        foreach ($colname as $key => $val) {
            // reduce to max 8 chars
            $val = substr($val, 0, 8);
            // pad to 8 spaces
            $colname[$key] = str_pad($val, 8);
        }
        
        // prep the output rows
        $row = array();
        
        // add a title
        if (trim($title != '')) {
            $row[] = $title;
        }
        
        // add the column names
        $row[] = sprintf(
            "%-{$this->_maxlen}s : {$colname['diff']} : {$colname['total']}",
            $colname['name']
        );
        
        // add each timer mark
        foreach ($profile as $key => $val) {
            $row[] = sprintf(
                "%-{$this->_maxlen}s : %f : %f",
                $val['name'],
                $val['diff'],
                $val['total']
            );
        }
        
        // finalize output and display
        $output = implode("\n", $row);
        
        if ($this->_config['output'] == 'html') {
            $output = '<pre>' . htmlspecialchars($output) . '</pre>';
        }
        
        return $output;
    }
    
    /**
     * 
     * Displays formatted output of the current profile.
     * 
     * This displays the profile information as a table; see the
     * [HomePage home page for this class] for an example.
     * 
     * @param string $title A title for the output.
     * 
     * @return void
     * 
     */
    public function display($title = null)
    {
        echo $this->fetch($title);
    }
}
