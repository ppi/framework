<?php
/**
 * 
 * Log adapter to ignore all messages.
 * 
 * @category Solar
 * 
 * @package Solar_Log
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: None.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Log_Adapter_None extends Solar_Log_Adapter
{
    /**
     * 
     * Support method to save (write) an event and message to the log.
     * 
     * @param string $class The class name reporting the event.
     * 
     * @param string $event The event type (for example 'info' or 'debug').
     * 
     * @param string $descr A description of the event. 
     * 
     * @return mixed Boolean false if the event was not saved (usually
     * because it was not recognized), or a non-empty value if it was
     * saved.
     * 
     */
    protected function _save($class, $event, $descr)
    {
        return true;
    }
}
