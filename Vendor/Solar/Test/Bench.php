<?php
/**
 * 
 * Class for benchmarking the speed of different methods.
 * 
 * Benchmark methods are prefixed with "bench" and are automatically
 * run $loops number of times when you call Solar_Test_Bench::run($loops).
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Bench.php 3850 2009-06-24 20:18:27Z pmjones $
 * 
 */
class Solar_Test_Bench extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config int loop The number of times the benchmarking methods
     *   should be run when using loop().  Default 1000.
     * 
     * @config int time The time in minutes each method should run
     *   when using time().  Default 1.
     * 
     * @var array
     * 
     */
    protected $_Solar_Test_Bench = array(
        'loop'   => 1000,
        'time'   => 1,
    );
    
    /**
     * 
     * Executes this code before running any benchmarks.
     * 
     * @return void
     * 
     */
    public function setup()
    {
    }
    
    /**
     * 
     * Executes this code after running all benchmarks.
     * 
     * @return void
     * 
     */
    public function teardown()
    {
    }
    
    /**
     * 
     * Runs each benchmark method for a certain number of loops.
     * 
     * @param int $loops Loop benchmark methods this number of times.
     * 
     * @return string The Solar_Debug_Timer profile table; smaller diffs
     * are better.
     * 
     */
    public function loop($loops = null)
    {
        if (empty($loops)) {
            $loops = $this->_config['loop'];
        }
        
        // get the list of bench*() methods
        $bench = $this->_getMethods();
        
        // get a timer object
        $timer = Solar::factory(
            'Solar_Debug_Timer',
            array('auto_start' => false)
        );
        
        // pre-run
        $this->setup();
        
        // start timing
        $timer->start();
        
        // run each benchmark method...
        foreach ($bench as $method) {
            // ... multiple times.
            for ($i = 0; $i < $loops; ++$i) {
                $this->$method();
            }
            // how long did the method run take?
            $timer->mark($method);
        }
        
        // stop timing
        $timer->stop();
        
        // post-run
        $this->teardown();
        
        // done!
        return $timer->fetch();
    }
    
    /**
     * 
     * Runs each benchmark method for a certain number of minutes.
     * 
     * @param int $mins Run each method for this many minutes.
     * 
     * @return string The number of times each method ran in the
     * allotted time; larger numbers are better.
     * 
     */
    public function time($mins = null)
    {
        if (empty($mins)) {
            $mins = $this->_config['mins'];
        }
        
        // eventual report text
        $report = '';
        
        // get the list of bench*() methods
        $bench = $this->_getMethods();
        
        // pre-run
        $this->setup();
        
        // run each benchmark method...
        $list = array();
        foreach ($bench as $method) {
            
            $secs = $mins * 60;
            $stop = time() + $secs;
            set_time_limit($secs + 1);
            
            // ... for the number of minutes specified.
            $count = 0;
            while (time() <= $stop) {
                $this->$method();
                ++ $count;
            }
            
            // save the report line
            $report .= "$method ran $count iterations in $mins minutes "
                     . sprintf("(%d/second)\n", $count / $mins / 60);
        }
        
        // post-run
        $this->teardown();
        
        // done!
        return $report;
    }
    
    /**
     * 
     * Returns a list of benchmark methods in this class.
     * 
     * @return array An array of benchmark method names.
     * 
     */
    protected function _getMethods()
    {
        $reflect = new ReflectionClass($this);
        $list = array();
        $methods = $reflect->getMethods();
        foreach ($methods as $method) {
            $name = $method->getName();
            if (substr($name, 0, 5) == 'bench') {
                $list[] = $name;
            }
        }
        return $list;
    }
}

