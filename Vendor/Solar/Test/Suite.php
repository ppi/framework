<?php
/**
 * 
 * Class for running suites of unit tests.
 * 
 * Expects a directory structure like this ...
 * 
 *     Test/
 *       Solar.php      -- Test_Solar
 *       Solar/         
 *         Base.php     -- Test_Solar_Base
 *         Uri.php      -- Test_Solar_Uri
 *         Uri/     
 *           Action.php -- Test_Solar_Uri_Action
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Suite.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_Test_Suite extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency log A Solar_Log dependency for logging test results.
     * 
     * @config mixed test_config The config param to use for Solar::start() 
     * when running tests in the separate PHP environment.
     * 
     * @config bool verbose Whether or not to show verbose output.
     * 
     * @config bool stop_on_fail Stop running tests at the first failure.
     * 
     * @var array
     * 
     */
    protected $_Solar_Test_Suite = array(
        'log'           => array(
            'adapter'   => 'Solar_Log_Adapter_Echo',
            'format'    => '%m',
        ),
        'test_config'   => null,
        'verbose'       => null,
        'stop_on_fail'  => false,
    );
    
    /**
     * 
     * The directory where tests are located.
     * 
     * @var string
     * 
     */
    protected $_dir;
    
    /**
     * 
     * The log of pass/skip/todo/fail messages.
     * 
     * @var array
     * 
     */
    protected $_info;
    
    /**
     * 
     * The test classes (and their methods) to run.
     * 
     * In the form of array($class => array($method1, $method2, ...)).
     * 
     * @var array
     * 
     */
    protected $_tests;
    
    /**
     * 
     * A Solar_Log instance.
     * 
     * @var Solar_Log
     * 
     */
    protected $_log;
    
    /**
     * 
     * A Solar_Debug_Var instance.
     * 
     * @var Solar_Debug_Var
     * 
     */
    protected $_var;
    
    /**
     * 
     * When in 'verbose' mode, all diagnostic output will be displayed.
     * 
     * @var bool
     * 
     */
    protected $_verbose = false;
    
    /**
     * 
     * The result of the last test ('fail', 'todo', 'skip', or 'pass').
     * 
     * @var string
     * 
     */
    protected $_test_result = null;
    
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
        
        // verbosity
        if ($this->_config['verbose'] !== null) {
            $this->setVerbose($this->_config['verbose']);
        }
        
        // keep a Solar_Debug_Var object around for later
        $this->_var = Solar::factory('Solar_Debug_Var');
        
        // set the include directory
        $this->_dir = Solar::$system . "/include";
        
        // logging
        $this->_log = Solar::dependency(
            'Solar_Log',
            $this->_config['log']
        );
    }
    
    /**
     * 
     * Turns 'verbose' mode on and off.
     * 
     * @param bool $flag True for verbose, false for not.
     * 
     * @return void
     * 
     */
    public function setVerbose($flag)
    {
        $this->_verbose = (bool) $flag;
    }
    
    /**
     * 
     * Creates a new Solar_Php instance with some default settings.
     * 
     * @return Solar_Php
     * 
     */
    protected function _newPhp()
    {
        $php = Solar::factory('Solar_Php');
        $php->setEcho($this->_verbose)
            ->setIniVal('include_path', $this->_dir)
            ->setIniVal('error_reporting', E_ALL | E_STRICT)
            ->setIniVal('display_errors', true)
            ->setIniVal('html_errors', false)
            ->setIniVal('log_errors', true)
            ->setIniVal('error_log', '/tmp/php_errors.log');
        
        return $php;
    }
    
    /**
     * 
     * Finds tests and loads them into the plan.
     * 
     * @param string $class Start with this test class; e.g, "Test_Foo".
     * 
     * @param string $method Load only this test method; e.g, "testBar".
     * 
     * @param bool $only Load only the named class, or class and method, 
     * instead of descending into sub-tests.
     * 
     * @return void
     * 
     */
    public function loadTests($class = null, $method = null, $only = null)
    {
        // if no class, at least pass a string zero
        if (! $class) {
            $method = '0';
        }
        
        // if no method, at least pass a string zero
        if (! $method) {
            $method = '0';
        }
        
        // the load-tests file to run
        $file = Solar_Class::dir($this) . '/load-tests.php';
        
        // find the tests using a separate php process
        $php = $this->_newPhp();
        $php->addArgv($this->_dir . '/')
            ->addArgv($class)
            ->addArgv($method)
            ->addArgv((int) $only)
            ->run($file);
        
        // how'd it go?
        $exit_code = $php->getExitCode();
        if ($exit_code != Solar_Test::EXIT_PASS) {
            throw $this->_exception('ERR_LOAD_TESTS', array(
                'class' => $class,
                'method' => ($method ? $method : '*'),
                'exit_code' => $exit_code,
                'last_line' => $php->getLastLine(),
            ));
        }
        
        // retain the list of found tests
        $data = unserialize($php->getOutput());
        $this->_info['plan'] = $data['plan'];
        $this->_tests = $data['tests'];
    }
    
    /**
     * 
     * Runs the test suite (or the sub-test series) and logs as it goes.
     * 
     * Returns an array of statistics with these keys ...
     * 
     * `plan`
     * : (int) The planned number of tests.
     * 
     * `done`
     * : (int) The number of tests actually done.
     * 
     * `time`
     * : (int) The time, in seconds, it took to run all tests.
     * 
     * `pass`
     * : (array) Log of tests that passed.
     * 
     * `skip`
     * : (array) Log of tests that were skipped.
     * 
     * `todo`
     * : (array) Log of tests that are incomplete.
     * 
     * `fail`
     * : (array) Log of tests that failed.
     * 
     * @param string $class Run only this test class series. If empty, will
     * run all test classes.
     * 
     * @param string $method Run only this test method in the test class
     * series.  If empty, will run all test methods.
     * 
     * @param bool $only Run only the name class, or named class and method, 
     * instead of descending into sub-tests.
     * 
     * @return array A statistics array.
     * 
     */
    public function run($class = null, $method = false, $only = false)
    {
        // prepare
        $this->_prepare($class, $method, $only);
        
        // is there a plan?
        if (! $this->_info['plan']) {
            throw $this->_exception('ERR_FIND_TESTS', array(
                'class' => $class,
                'method' => ($method ? $method : '*'),
            ));
        }
        
        // show the plan
        $this->_log("1..{$this->_info['plan']}");
        
        // set up the PHP environment
        $php = $this->_newPhp();
        
        // the time before running the tests
        $time = time();
        
        // run the test cases
        foreach ($this->_tests as $class => $methods) {
            
            // set the config for this test case. we do this on each class
            // because there may be multiple vendors involved.
            $config = $this->_fetchTestCaseConfig($class);
            $this->_log("# $class config: $config");
            $php->setSolarConfig($config);
            
            // try constructing the test case once
            $this->_testConstruct($php, $class);
            
            // was it a stop-on-fail?
            $stop = $this->_test_result == 'fail'
                 && $this->_config['stop_on_fail'];
            if ($stop) {
                // stop testing entirely
                break;
            }
            
            // was it a fail/todo/skip?
            $continue = in_array($this->_test_result, array(
                'fail', 'todo', 'skip',
            ));
            if ($continue) {
                continue;
            }
            
            // run each test method
            foreach ($methods as $method) {
                // run the method
                $this->_testMethod($php, $class, $method);
                // did it fail?
                $fail = $this->_test_result == 'fail';
                // should we stop on failure?
                $stop = $this->_config['stop_on_fail'];
                if ($fail && $stop) {
                    // stop testing entirely
                    break 2;
                }
            }
        }
        
        // the test time duration
        $this->_info['time'] = time() - $time;
        
        // report, then return the run information
        $this->_report();
        return $this->_info;
    }
    
    /**
     * 
     * Finds the config file for a test case.
     * 
     * The order of precedence is:
     * 
     * 1. Use the value of --test-config when not empty.
     * 
     * 2. Look for `$system/config/test/Vendor.config.php` and use that if it
     *    exists.
     * 
     * 3. Look for `$system/source/vendor/tests/config.php` and use that if it
     *    exists.
     * 
     * 4. No config for the test case.
     * 
     * @param string $class The test case class to find configs for.
     * 
     * @return string The config file location for the test case.
     * 
     */
    protected function _fetchTestCaseConfig($class)
    {
        // explicit test-config
        if ($this->_config['test_config']) {
            return $this->_config['test_config'];
        }
        
        // convenience var
        $system = Solar::$system;
        
        // strip the 'Test_' prefix, then get the vendor name
        $vendor = Solar_Class::vendor(substr($class, 5));
        
        // the dashes version
        $dashes = Solar_Registry::get('inflect')->camelToDashes($vendor);
        
        // look for a config/run-tests/vendor.php file
        $path = "$system/config/run-tests/$dashes.php";
        $file = Solar_File::exists($path);
        if ($file) {
            return $file;
        }
        
        // look for a source/vendor/config/run-tests.php file
        $path = "$system/source/$dashes/config/run-tests.php";
        $file = Solar_File::exists($path);
        if ($file) {
            return $file;
        }
        
        // no test config
        return null;
    }
    
    /**
     * 
     * Test the construction of the test class to see if it works.
     * 
     * @param Solar_Php $php The PHP execution object.
     * 
     * @param string $class The test class for construction.
     * 
     * @return void
     * 
     */
    protected function _testConstruct($php, $class)
    {
        $this->_test_result = null;
        
        $file = Solar_Class::dir($this) . 'pre-test.php';
        
        $php->setArgv(array($class))
            ->runSolar($file);
        
        $exit = $php->getExitCode();
        
        if ($exit != Solar_Test::EXIT_PASS) {
            $this->_done($exit, $class, $php->getLastLine());
            $count = count($this->_tests[$class]);
            $type = $this->_test_result;
            $this->_log("# " . ucfirst($type) . ": $count test methods in $class");
            $k = $count - 1;
            for ($i = 0; $i < $k; $i ++) {
                $this->_info['done'] ++;
                $this->_info[$type]["{$class}::{$i}"] = array(null, "$type constructor");
            }
        }
    }
    
    /**
     * 
     * Run a single test method from the test class.
     * 
     * @param Solar_Php $php The PHP execution object.
     * 
     * @param string $class The test class.
     * 
     * @param string $method The test method.
     * 
     * @return void
     * 
     */
    protected function _testMethod($php, $class, $method)
    {
        $this->_test_result = null;
        
        $file = Solar_Class::dir($this) . 'run-test.php';
        
        $php->setArgv(array($class, $method))
            ->runSolar($file);
        
        $this->_done(
            $php->getExitCode(),
            "$class::$method",
            $php->getLastLine()
        );
    }
    
    /**
     * 
     * Prepares class properties for a test run.
     * 
     * @param string $class Only prepare tests for this class series.  Don't
     * include the 'Test_' prefix.  If empty, will run all test classes.
     * 
     * @param string $method When empty, recurse into subdirectories and run 
     * sub-test classes and methods.  When non-empty, run **only** this test 
     * method in the named test class; do not include the "test" prefix. 
     * Default null; ignored when $class is empty.
     * 
     * @param bool $only Load only the named class, or class and method, 
     * instead of descending into sub-tests.
     * 
     * @return void
     * 
     */
    protected function _prepare($class = null, $method = null, $only = false)
    {
        // reset
        $this->_info = array(
            'plan' => 0,
            'done' => 0,
            'time' => 0,
            'pass' => array(),
            'skip' => array(),
            'todo' => array(),
            'fail' => array(),
        );
        
        $this->loadTests($class, $method, $only);
    }
    
    /**
     * 
     * Generates a report from class properties.
     * 
     * @return void
     * 
     */
    protected function _report()
    {
        // report summary
        $done = $this->_info['done'];
        $plan = $this->_info['plan'];
        $time = $this->_info['time'];
        
        $this->_log("$done/$plan tests, $time seconds");
        
        $tmp = array();
        $show = array('fail', 'todo', 'skip', 'pass');
        foreach ($show as $type) {
            $count = count($this->_info[$type]);
            $tmp[] = "$count $type";
        }
        $this->_log(implode(', ', $tmp));
    }
    
    /**
     * 
     * Formats a test line, logs it, and saves the info.
     * 
     * @param int $exit Pass, skip, todo, or fail.
     * 
     * @param string $name The test name.
     * 
     * @param string $note Additional note about the test.
     * 
     * @param string $diag Diagnostics for the test.
     * 
     * @return void
     * 
     */
    protected function _done($exit, $name, $note = null, $diag = null)
    {
        $this->_info['done'] ++;
        
        $text = '';
        
        if (is_array($diag) || is_object($diag)) {
            $diag = $this->_var->dump($diag);
        }
        
        $diag = trim($diag);
        if ($diag) {
            $text = "$text\n# " . str_replace("\n", "\n# ", trim($diag));
        }
        
        if ($text) {
            $text .= "\n";
        }
        
        $num = $this->_info['done'];
        
        $note = ltrim($note, '# ');
        
        switch ($exit) {
        case Solar_Test::EXIT_FAIL:
            $type = 'fail';
            $text .= "not ok $num - $name # FAIL $note";
            break;
        
        case Solar_Test::EXIT_TODO:
            $type = 'todo';
            $text .= "not ok $num - $name # TODO $note";
            break;
        
        case Solar_Test::EXIT_SKIP:
            $type = 'skip';
            $text .= "ok $num - $name # SKIP $note";
            break;
        
        case Solar_Test::EXIT_PASS:
            $type = 'pass';
            $text .= "ok $num - $name";
            break;
        
        default:
            $type = 'fail';
            $text .= "not ok $num - $name # FAIL exit code '$exit'";
            break;
        }
        
        $this->_test_result = $type;
        $this->_log($text);
        $this->_info[$type][$name] = array($num, $note);
    }
    
    /**
     * 
     * Saves an event and message to the log.
     * 
     * @param string $event The log event type.
     * 
     * @param string $message The log message.
     * 
     * @return boolean True if saved, false if not, null if logging
     * not enabled.
     * 
     * @see Solar_Test_Suite::$_log
     * 
     * @see Solar_Log_Adapter::save()
     * 
     */
    protected function _log($message)
    {
        $this->_log->save(get_class($this), 'test', $message);
    }
}
