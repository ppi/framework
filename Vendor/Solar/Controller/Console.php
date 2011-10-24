<?php
/**
 * 
 * The CLI equivalent of a front-controller to find and invoke a command
 * (technically a sub-command).
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Console.php 4370 2010-02-11 15:41:19Z pmjones $
 * 
 */
class Solar_Controller_Console extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config array classes Base class names for commands.
     * 
     * @config array routing An array of commands to class names.
     * 
     * @config array disable An array of command names to disallow.
     * 
     * @config string default The default command to run.
     * 
     * @var array
     * 
     */
    protected $_Solar_Controller_Console = array(
        'classes' => array('Solar_Cli'),
        'routing' => array(),
        'disable' => array(),
        'default' => 'help',
    );
    
    /**
     * 
     * A list of command names that should be disallowed.
     * 
     * @var array
     * 
     */
    protected $_disable = array();
    
    /**
     * 
     * Explicit command-name to class-name mappings.
     * 
     * @var array
     * 
     */
    protected $_routing;
    
    /**
     * 
     * The list of commands this controller can invoke.
     * 
     * @var array
     * 
     */
    protected $_command_list = array();
    
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
        
        // get the current request environment
        $this->_request = Solar_Registry::get('request');
        
        // set convenience vars from config
        $this->_routing = $this->_config['routing'];
        $this->_default = $this->_config['default'];
        $this->_disable = (array)  $this->_config['disable'];
        
        // set up a class stack for finding commands
        $this->_stack = Solar::factory('Solar_Class_Stack');
        $this->_stack->add($this->_config['classes']);
        
        // extended setup
        $this->_setup();
    }
    
    /**
     * 
     * Finds and invokes a command.
     * 
     * @param array $argv The command-line arguments.
     * 
     * @return string The output of the page action.
     * 
     */
    public function exec($argv = null)
    {
        // get the command-line arguments
        if ($argv === null) {
            $argv = $this->_request->server['argv'];
            array_shift($argv);
        } else {
            $argv = (array) $argv;
        }
        
        // take the command name off the top of the path and
        $command = array_shift($argv);
        
        // is the command disallowed?
        if (in_array($command, $this->_disable)) {
            return $this->_notFound($command);
        }
        
        // try to get a controller class from it.
        $class = $this->_getCommandClass($command);
        
        // did we get a class from it?
        if (! $class) {
            // put the original segment back on top.
            array_unshift($argv, $command);
            // try to get a controller class from the default page name
            $class = $this->_getCommandClass($this->_default);
        }
        
        // last chance: do we have a class yet?
        if (! $class) {
            return $this->_notFound($command);
        }
        
        // instantiate and invoke the command
        $obj = Solar::factory($class);
        $obj->setConsoleController($this);
        return $obj->exec($argv);
    }
    
    /**
     * 
     * Returns a list of commands recognized by this console controller, and the
     * related classes for those commands.
     * 
     * @return array An associative array where the key is the command name, and
     * the value is the class for that command.
     * 
     */
    public function getCommandList()
    {
        if (! $this->_command_list) {
            $this->_setCommandList();
        }
        return $this->_command_list;
    }
    
    /**
     * 
     * Sets up the environment for all commands.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    /**
     * 
     * Populates the list of recognized commands.
     * 
     * @return void
     * 
     */
    protected function _setCommandList()
    {
        $list = array();
    
        // loop through class stack and add commands
        $stack = $this->_stack->get();
        foreach ($stack as $class) {
        
            $dir = Solar_Dir::exists(str_replace('_', DIRECTORY_SEPARATOR, $class));
            if (! $dir) {
                continue;
            }
        
            // loop through each file in the directory
            $files = scandir($dir);
            foreach ($files as $file) {
                // file must end in .php and start with an upper-case letter
                $char = $file[0];
                $keep = substr($file, -4) == '.php' &&
                        ctype_alpha($char) &&
                        strtoupper($char) == $char;
            
                if (! $keep) {
                    // doesn't look like a command class
                    continue;
                }
                
                // the list-value is the base class name, plus the file name,
                // minus the .php extension, to give us a class name
                $val = $class . substr($file, 0, -4);
            
                // the list-key is the command name; convert the file name to a
                // command name.  FooBar.php becomes "foo-bar".
                $key = substr($file, 0, -4);
                $key = preg_replace('/([a-z])([A-Z])/', '$1-$2', $key);
                $key = strtolower($key);
            
                // keep the command name and class name
                $list[$key] = $val;
            }
        }
    
        // override with explicit routings
        $this->_command_list = array_merge($list, $this->_routing);
        
        // sort, and done
        ksort($this->_command_list);
    }
    
    /**
     * 
     * Finds the command class from a command name.
     * 
     * @param string $command The command name.
     * 
     * @return string The related command class picked from
     * the routing, or from the list of available classes.  If not found,
     * returns false.
     * 
     */
    protected function _getCommandClass($command)
    {
        // skip on leading dashes, it can't be a command
        if (substr($command, 0, 1) == '-') {
            return;
        }
        
        // find the command class
        if (! empty($this->_routing[$command])) {
            // found an explicit route
            $class = $this->_routing[$command];
        } else {
            // no explicit route, try to find a matching class
            $command = str_replace('-',' ', $command);
            $command = str_replace(' ', '', ucwords(trim($command)));
            $class = $this->_stack->load($command, false);
        }
        
        // done!
        return $class;
    }
    
    /**
     * 
     * This runs when exec() cannot find a related command class.
     * 
     * The method throws an exception, which should be caught by the calling
     * script.
     * 
     * @param string $cmd The name of the command not found.
     * 
     * @throws Solar_Controller_Console_Exception
     * 
     * @return void
     * 
     */
    protected function _notFound($cmd)
    {
        $cmd = trim($cmd);
        if (empty($cmd)) {
            throw $this->_exception('ERR_NO_COMMAND');
        } else {
            throw $this->_exception('ERR_COMMAND_NOT_FOUND', array(
                'cmd' => $cmd,
                'classes' => $this->_config['classes'],
                'routing' => $this->_config['routing'],
            ));
        }
    }
}