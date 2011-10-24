<?php
/**
 * 
 * Lets you execute a Solar-based script in a separate PHP process, then get
 * back its exit code, last line, and output.
 * 
 * Intended use is for documentation and testing, where you don't want the
 * classes loaded in the main environment to interact with the classes in the
 * current environment.
 * 
 * An example to run `echo "hello world!"` in a separate process:
 * 
 * {{code: php
 *     require_once 'Solar.php';
 *     Solar::start();
 *     
 *     $ini = array(
 *         'include_path'    =>  '/path/to/lib',
 *         'error_reporting' =>  E_ALL | E_STRICT,
 *         'error_display'   =>  1,
 *         'html_errors'     =>  0,
 *     );
 *     
 *     $php = Solar::factory('Solar_Php');
 *     
 *     $php->setIniFile(false)
 *         ->setIniArray($ini)
 *         ->runSolarCode('echo "hello world!\n"');
 *     
 *     Solar::stop();
 * }}
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Php.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
class Solar_Php extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string php Command to invoke the PHP binary.
     * 
     * @config string ini_file Which php.ini file to use; if null, use the
     * default php.ini file.
     * 
     * @config array ini_set Override php.ini settings with these settings
     * instead. The element key is the .ini setting name, and the element 
     * value is the .ini value to use.
     * 
     * @config mixed solar_config When calling Solar::start(), use this as the
     * config value.
     * 
     * @config bool echo Whether or not to echo the process output as it goes.
     *
     * @var array
     * 
     */
    protected $_Solar_Php = array(
        'php'          => 'php',
        'ini_file'     => null,
        'ini_set'      => null,
        'solar_config' => null,
        'echo'         => null,
    );
    
    /**
     * 
     * Command to invoke the PHP binary.
     * 
     * @var string
     * 
     */
    protected $_php = 'php';
    
    /**
     * 
     * Which php.ini file to use.
     * 
     * Null means to use the default php.ini file, but false means to use *no*
     * php.ini file.
     * 
     * @var string
     * 
     */
    protected $_ini_file = null;
    
    /**
     * 
     * Override php.ini file settings with these settings.
     * 
     * Format is an array of key-value pairs, where the key is the setting
     * name and the value is the setting value.
     * 
     * @var array
     * 
     */
    protected $_ini_set = array();
    
    /**
     * 
     * When calling Solar::start() in the new process, use this as the $config
     * value.
     * 
     * @var mixed
     * 
     */
    protected $_solar_config = null;
    
    /**
     * 
     * Whether or not to echo the process output as it goes.
     * 
     * @var bool
     * 
     */
    protected $_echo = true;
    
    /**
     * 
     * After the code runs, each line of output (if any).
     * 
     * @var array
     * 
     */
    protected $_output;
    
    /**
     * 
     * After the code runs, the last line of output (if any).
     * 
     * @var array
     * 
     */
    protected $_last_line;
    
    /**
     * 
     * After the code runs, the exit status code (if any).
     * 
     * Note that null is *not* the same as zero; zero is normally an "OK"
     * exit code, whereas null means "no exit code given".
     * 
     * @var array
     * 
     */
    protected $_exit_code;
    
    /**
     * 
     * Command-line arguments to pass to the code.
     * 
     * @var array
     * 
     */
    protected $_argv = array();
    
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
        
        // populate each of these properties with its config value ...
        $list = array_keys($this->_Solar_Php);
        foreach ($list as $key) {
            // ... but only if not null.
            if ($this->_config[$key] !== null) {
                $var = "_$key";
                $this->$var = $this->_config[$key];
            }
        }
    }
    
    /**
     * 
     * Sets the PHP command to call at the command line.
     * 
     * @param string $php The PHP command; e.g., "/usr/local/php".
     * 
     * @return Solar_Php
     * 
     */
    public function setPhp($php)
    {
        $this->_php = $php;
        return $this;
    }
    
    /**
     * 
     * Sets the location of the php.ini file to use.
     * 
     * If null, uses the default php.ini file location.
     * 
     * If false, uses *no* php.ini file (the `--no-php-ini` switch).
     * 
     * @param string $file The php.ini file location.
     * 
     * @return Solar_Php
     * 
     */
    public function setIniFile($file)
    {
        if ($file !== null && $file !== false) {
            $file = (string) $file;
        }
        $this->_ini_file = $file;
        return $this;
    }
    
    /**
     * 
     * Sets one php.ini value, overriding the php.ini file.
     * 
     * @param string $key The php.ini setting name.
     * 
     * @param string $val The php.ini setting value.
     * 
     * @return Solar_Php
     * 
     */
    public function setIniVal($key, $val)
    {
        $this->_ini_set[$key] = $val;
        return $this;
    }
    
    /**
     * 
     * Sets an array of php.ini values, overriding the php.ini file.
     * 
     * Each key in the array is a php.ini setting name, and each value is the
     * corresponding php.ini value.
     * 
     * @param string $list The array of php.ini key-value pairs.
     * 
     * @return Solar_Php
     * 
     */
    public function setIniArray($list)
    {
        foreach ($list as $key => $val) {
            $this->_ini_set[$key] = $val;
        }
        return $this;
    }
    
    /**
     * 
     * Add a command-line argument for the code.
     * 
     * @param mixed $val The command-line argument value.
     * 
     * @return Solar_Php
     * 
     */
    public function addArgv($val)
    {
        $this->_argv[] = $val;
        return $this;
    }
    
    /**
     * 
     * Set all command-line arguments for the code at one time; clears all
     * previous argument values.
     * 
     * @param array $array A sequential array of command-line arguments.
     * 
     * @return Solar_Php
     * 
     */
    public function setArgv($array)
    {
        $this->_argv = (array) $array;
        return $this;
    }
    
    /**
     * 
     * When calling Solar::start() in the new process, use this as the $config
     * value.
     * 
     * @param mixed $solar_config The value to use for Solar::start().
     * 
     * @return Solar_Php
     * 
     */
    public function setSolarConfig($solar_config)
    {
        $this->_solar_config = $solar_config;
        return $this;
    }
    
    /**
     * 
     * Turns execution process output on and off.
     * 
     * @param bool $echo True to echo the process as it runs, or false to
     * suppress output.
     * 
     * @return Solar_Php
     * 
     */
    public function setEcho($echo)
    {
        $this->_echo = (bool) $echo;
        return $this;
    }
    
    /**
     * 
     * Runs a file as a Solar script.
     * 
     * @param string $file The file to load as a Solar script.
     * 
     * @return Solar_Php
     * 
     */
    public function runSolar($file)
    {
        $code = file_get_contents($file);
        return $this->runSolarCode($code);
    }
    
    /**
     * 
     * Runs a code string as a Solar script.
     * 
     * @param string $code The code to run as a Solar script.
     * 
     * @return Solar_Php
     * 
     */
    public function runSolarCode($code)
    {
        $code = $this->_buildSolarCode($code);
        return $this->_run($code);
    }
    
    /**
     * 
     * Runs the named file as the PHP code for the process.
     * 
     * @param string $file The script file name.
     * 
     * @return Solar_Php
     * 
     */
    public function run($file)
    {
        $code = file_get_contents($file);
        return $this->runCode($code);
    }
    
    /**
     * 
     * Runs the given string as the PHP code for the process.
     * 
     * @param string $code The script code.
     * 
     * @return Solar_Php
     * 
     */
    public function runCode($code)
    {
        $code = $this->_buildCode($code);
        return $this->_run($code);
    }
    
    /**
     * 
     * Support method to actually run the code and retain information about
     * the run.
     * 
     * @param string $code The code to execute.
     * 
     * @return Solar_Php
     * 
     */
    protected function _run($code)
    {
        // clean up from last run
        $this->_output    = null;
        $this->_last_line = null;
        $this->_exit_code = null;
        
        // build the full command with PHP code
        $cmd = $this->_buildCommand($code);
        
        // open a process handle and send the command
        $handle = popen($cmd, 'rb');
        
        // read from the handle
        while (! feof($handle)) {
            $read = fread($handle, 4096);
            if ($this->_echo) {
                echo $read;
            }
            $this->_output .= $read;
        }
        
        // close the handle and retain the exit code
        $this->_exit_code = pclose($handle);
        
        // get the last line of output.
        $tmp = $this->_output;
        $len = strlen(PHP_EOL) * -1;
        if (substr($tmp, $len) == PHP_EOL) {
            $tmp = substr($tmp, 0, $len);
        }
        $tmp = explode(PHP_EOL, $tmp);
        $this->_last_line = end($tmp);
        
        // done!
        return $this;
    }
    
    /**
     * 
     * Gets the exit code from the separate process.
     * 
     * @return int
     * 
     */
    public function getExitCode()
    {
        return $this->_exit_code;
    }
    
    /**
     * 
     * Gets all lines of output from the separate process.
     * 
     * @return array
     * 
     */
    public function getOutput()
    {
        return $this->_output;
    }
    
    /**
     * 
     * Gets the last line of output from the separate process.
     * 
     * @return string
     * 
     */
    public function getLastLine()
    {
        return $this->_last_line;
    }
    
    /**
     * 
     * Builds a code string appropriate for the PHP command-line call.
     * 
     * @param string $code The code to run.
     * 
     * @return string The "fixed" code.
     * 
     */
    protected function _buildCode($code)
    {
        // trim leading and trailing space so as not to mess up the removal of
        // opening and closing tags.
        $code = trim($code);
        
        // strip long opening tag
        if (substr($code, 0, 5) == '<?php') {
            $code = substr($code, 5);
        }
        
        // strip short opening tag
        if (substr($code, 0, 2) == '<?') {
            $code = substr($code, 2);
        }
        
        // strip closing tag
        if (substr($code, -2) == '?>') {
            $code = substr($code, 0, -2);
        }
        
        return $code;
    }
    
    /**
     * 
     * Wraps the given code string in extra code to load, start, and stop
     * Solar.
     * 
     * @param string $code The code to run in the separate process.
     * 
     * @return string
     * 
     */
    protected function _buildSolarCode($code)
    {
        // the core code
        $code = $this->_buildCode($code);
        
        // get the solar config as a variable
        $solar_config = var_export($this->_solar_config, true);
        
        // wrap the code in Solar::start() and Solar::stop()
        $code = "require 'Solar.php'; "
              . "Solar::start($solar_config); "
              . "$code; "
              . "Solar::stop();";
        
        // done!
        return $code;
    }
    
    /**
     * 
     * Builds the command-line invocation of PHP.
     * 
     * @param string $code The code to execute.
     * 
     * @return string The PHP command with the necessary switches.
     * 
     */
    protected function _buildCommand($code)
    {
        // the PHP binary
        $cmd = $this->_php;
        
        // using a php.ini file?
        if ($this->_ini_file) {
            // non-default file or path
            $cmd .= " --php-ini " . escapeshellarg($this->_ini_file);
        } elseif ($this->_ini_file === false) {
            // explicitly *no* file to be used
            $cmd .= " --no-php-ini";
        }
        
        // override php.ini values
        foreach ((array) $this->_ini_set as $key => $val) {
            $key = escapeshellarg($key);
            $val = escapeshellarg($val);
            $cmd .= " --define $key=$val";
        }
        
        // add the code
        $cmd .= " --run " . escapeshellarg($code);
        
        foreach ($this->_argv as $val) {
            $cmd .= " $val";
        }
        
        // done
        return $cmd;
    }
}
