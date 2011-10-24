<?php
/**
 * 
 * The CLI equivalent of a page-controller; a single command to be invoked
 * from the command-line.
 * 
 * @category Solar
 * 
 * @package Solar_Controller Front and page controllers for the web, plus
 * console and command controllers for the command line.
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Command.php 4548 2010-05-01 15:03:17Z pmjones $
 * 
 */
class Solar_Controller_Command extends Solar_Base
{
    /**
     * 
     * Option flags and values extracted from the command-line arguments.
     * 
     * @var array
     * 
     */
    protected $_options = array();
    
    /**
     * 
     * A Solar_Getopt object to manage options and parameters.
     * 
     * @var Solar_Getopt
     * 
     */
    protected $_getopt;
    
    /**
     * 
     * The Solar_Controller_Console object (if any) that invoked this command.
     * 
     * @var Solar_Controller_Console
     * 
     */
    protected $_console;
    
    /**
     * 
     * File handle pointing to STDOUT for normal output.
     * 
     * @var resource
     * 
     */
    protected $_stdout;
    
    /**
     * 
     * File handle pointing to STDERR for error output.
     * 
     * @var resource
     * 
     */
    protected $_stderr;
    
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
        
        // stdout and stderr
        $this->_stdout = fopen('php://stdout', 'w');
        $this->_stderr = fopen('php://stderr', 'w');
        
        // set the recognized options
        $options = $this->_fetchGetoptOptions();
        $this->_getopt = Solar::factory('Solar_Getopt');
        $this->_getopt->setOptions($options);
        
        // follow-on setup
        $this->_setup();
    }
    
    /**
     * 
     * Destructor; closes STDOUT and STDERR file handles.
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
        fclose($this->_stdout);
        fclose($this->_stderr);
    }
    
    /**
     * 
     * Injects the console-controller object (if any) that invoked this command.
     * 
     * @param Solar_Controller_Console $console The console controller.
     * 
     * @return void
     * 
     */
    public function setConsoleController($console)
    {
        $this->_console = $console;
    }
    
    /**
     * 
     * Public interface to execute the command.
     * 
     * This method...
     * 
     * - populates and validates the option values
     * - calls _preExec()
     * - calls _exec() with the numeric parameters from the options
     * - calls _postExec()
     * 
     * @param array $argv The command-line arguments from the user.
     * 
     * @return void
     * 
     * @todo Accept a Getopt object in addition to $argv array?
     * 
     */
    public function exec($argv = null)
    {
        // get the command-line arguments
        if ($argv === null) {
            // use the $_SERVER values
            $argv = $this->_request->server['argv'];
            // remove the argument pointing to this command
            array_shift($argv);
        } else {
            $argv = (array) $argv;
        }
        
        // set options, populate values, and validate parameters
        $this->_getopt->populate($argv);
        if (! $this->_getopt->validate()) {
            // need a better way to throw exceptions with specific error
            // messages
            throw $this->_exception('ERR_INVALID_OPTIONS', array(
                'invalid' => $this->_getopt->getInvalid(),
                'options' => $this->_getopt->options,
            ));
        }
        
        // retain the option values, minus the numeric params
        $this->_options = $this->_getopt->values();
        $params = array();
        foreach ($this->_options as $key => $val) {
            if (is_int($key)) {
                $params[] = $val;
                unset($this->_options[$key]);
            }
        }
        
        // special behavior for -V/--version
        if ($this->_options['version']) {
            $vendor = Solar_Class::vendor($this);
            $this->_out("$vendor command-line tool, Solar version ");
            $this->_outln(Solar::apiVersion() . '.');
            return;
        }
        
        // call pre-exec
        $skip_exec = $this->_preExec();
        
        // should we skip the main execution?
        if ($skip_exec !== true) {
            // call _exec() with the numeric params from getopt
            call_user_func_array(
                array($this, '_exec'),
                $params
            );
        }
        
        // call post-exec
        $this->_postExec();
        
        // done, return terminal to normal colors
        $this->_out("%n");
    }
    
    /**
     * 
     * Returns an array of option flags and descriptions for this command.
     * 
     * @return array An associative array where the key is the short + long
     * option forms, and the value is the description for the option.
     * 
     */
    public function getInfoOptions()
    {
        $options = array();
        foreach ($this->_getopt->options as $name => $info) {
            
            $key = null;
            
            if ($info['short']) {
                $key .= "-" . $info['short'];
            }
            
            if ($key && $info['long']) {
                $key .= " | --" . $info['long'];
            } else {
                $key .= "--" . $info['long'];
            }
            
            $options[$key] = $info['descr'];
        }
        
        ksort($options);
        return $options;
    }
    
    /**
     * 
     * Returns the help text for this command.
     * 
     * @return string The contents of "Info/help.txt" for this class, or null
     * if the file does not exist.
     * 
     */
    public function getInfoHelp()
    {
        $file = Solar_Class::file($this, 'Info/help.txt');
        if ($file) {
            return file_get_contents($file);
        }
    }
    
    /**
     * 
     * Gets the option settings from the class hierarchy.
     * 
     * @return array
     * 
     */
    protected function _fetchGetoptOptions()
    {
        // the options to be set
        $options = array();
        
        // find the parents of this class, including this class
        $parents = Solar_Class::parents($this, true);
        array_shift($parents); // Solar_Base
        
        // get Info/options.php for each class in the stack
        foreach ($parents as $class) {
            $file = Solar_Class::file($class, 'Info/options.php');
            if ($file) {
                $options = array_merge(
                    $options, 
                    (array) Solar_File::load($file)
                );
            }
        }
        
        return $options;
    }
    
    /**
     * 
     * Prints text to STDOUT **without** a trailing newline.
     * 
     * If the text is a locale key, that text will be used instead.
     * 
     * Automatically replaces style-format codes for VT100 shell output.
     * 
     * @param string $text The text to print to STDOUT, usually a translation
     * key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return void
     * 
     */
    protected function _out($text = null, $num = 1, $replace = null)
    {
        $string = $this->locale($text, $num, $replace);
        Solar_Vt100::write($this->_stdout, $string);
    }
    
    /**
     * 
     * Prints text to STDOUT and appends a newline.
     * 
     * If the text is a locale key, that text will be used instead.
     * 
     * Automatically replaces style-format codes for VT100 shell output.
     * 
     * @param string $text The text to print to STDOUT, usually a translation
     * key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return void
     * 
     */
    protected function _outln($text = null, $num = 1, $replace = null)
    {
        $string = $this->locale($text, $num, $replace);
        Solar_Vt100::write($this->_stdout, $string, PHP_EOL);
    }
    
    /**
     * 
     * Prints text to STDERR **without** a trailing newline.
     * 
     * If the text is a locale key, that text will be used instead.
     * 
     * Automatically replaces style-format codes for VT100 shell output.
     * 
     * @param string $text The text to print to STDERR, usually a translation
     * key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return void
     * 
     */
    protected function _err($text = null, $num = 1, $replace = null)
    {
        $string = $this->locale($text, $num, $replace);
        Solar_Vt100::write($this->_stderr, $string);
    }
    
    /**
     * 
     * Prints text to STDERR and appends a newline.
     * 
     * If the text is a locale key, that text will be used instead.
     * 
     * Automatically replaces style-format codes for VT100 shell output.
     * 
     * @param string $text The text to print to STDERR, usually a translation
     * key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return void
     * 
     */
    protected function _errln($text = null, $num = 1, $replace = null)
    {
        $string = $this->locale($text, $num, $replace);
        Solar_Vt100::write($this->_stderr, $string, PHP_EOL);
    }
    
    /**
     * 
     * Post-construction setup logic.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    /**
     * 
     * Runs just before the main _exec() method.
     * 
     * @return bool True to skip _exec(), null otherwise.
     * 
     */
    protected function _preExec()
    {
    }
    
    /**
     * 
     * The main command method.
     * 
     * @return void
     * 
     */
    protected function _exec()
    {
    }
    
    /**
     * 
     * Runs just after the main _exec() method.
     * 
     * @return void
     * 
     */
    protected function _postExec()
    {
    }
}
