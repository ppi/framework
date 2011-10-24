<?php
/**
 * 
 * Solar "help" command.
 * 
 * @category Solar
 * 
 * @package Solar_Cli CLI commands.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Help.php 4434 2010-02-25 21:31:44Z pmjones $
 * 
 */
class Solar_Cli_Help extends Solar_Controller_Command
{
    /**
     * 
     * Displays a list of help options for a command, or the list of commands
     * if no command was requested.
     * 
     * @param string $cmd The requested command.
     * 
     * @return void
     * 
     */
    protected function _exec($cmd = null)
    {
        if ($cmd) {
            $this->_displayCommandHelp($cmd);
        } else {
            $this->_displayCommandList();
        }
    }
    
    /**
     * 
     * Displays a list of help options for a command, or the list of commands
     * if no command was requested.
     * 
     * @param string $cmd The requested command.
     * 
     * @return void
     * 
     */
    protected function _displayCommandHelp($cmd = null)
    {
        // the list of known command-to-class mappings
        $list = $this->_console->getCommandList();
        
        // is this a known command?
        if (empty($list[$cmd])) {
            $this->_outln('ERR_UNKNOWN_COMMAND', 1, array('cmd' => $cmd));
            return;
        }
        
        $class = $list[$cmd];
        $obj = Solar::factory($class);
        $help = rtrim($obj->getInfoHelp());
        if ($help) {
            $this->_outln($help);
        } else {
            $this->_outln('ERR_NO_HELP');
        }
        
        $this->_outln();
        
        $opts = $obj->getInfoOptions();
        if ($opts) {
            
            $this->_outln('HELP_VALID_OPTIONS');
            $this->_outln();
        
            foreach ($opts as $key => $val) {
                $this->_outln($key);
                $val = str_replace("\n", "\n  ", wordwrap(": $val"));
                $this->_outln($val);
                $this->_outln();
            }
        }
    }
    
    /**
     * 
     * Displays a list of available commands.
     * 
     * @param string $cmd The requested command.
     * 
     * @return void
     * 
     */
    protected function _displayCommandList()
    {
        $this->_outln($this->getInfoHelp());
        $this->_outln('HELP_AVAILABLE_COMMANDS');
        
        // now get the list of available commands
        $list = $this->_console->getCommandList();
        foreach ($list as $key => $val) {
            $this->_outln("    $key");
        }
    }
}
