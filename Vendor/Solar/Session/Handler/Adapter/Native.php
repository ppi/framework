<?php
/**
 * 
 * Session adapter for native PHP sessions
 * 
 * This adapter does **not** set any save-handler or ini-settings.
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Native.php 3366 2008-08-26 01:36:49Z pmjones $
 * 
 */
class Solar_Session_Handler_Adapter_Native extends Solar_Session_Handler_Adapter
{
    /**
     * 
     * Sets the session save handler.
     * 
     * This doesn't actually do anything, because we're using the native PHP
     * handler, not the methods in this class.
     * 
     * @return void
     * 
     */
    protected function _setSaveHandler()
    {
        // do nothing
    }
    
    /**
     * 
     * Opens the session handler.
     * 
     * Provided only to override abstract method, never actually called.
     * 
     * @return bool
     * 
     */
    public function open()
    {
        return true;
    }
    
    /**
     * 
     * Closes session handler.
     * 
     * Provided only to override abstract method, never actually called.
     * 
     * @return bool
     * 
     */
    public function close()
    {
        return true;
    }
    
    /**
     * 
     * Reads session data.
     * 
     * Provided only to override abstract method, never actually called.
     * 
     * @param string $id The session ID.
     * 
     * @return string The serialized session data.
     * 
     */
    public function read($id)
    {
        return null;
    }
    
    /**
     * 
     * Writes session data.
     * 
     * Provided only to override abstract method, never actually called.
     * 
     * @param string $id The session ID.
     * 
     * @param string $data The serialized session data.
     * 
     * @return bool
     * 
     */
    public function write($id, $data)
    {
        return true;
    }
    
    /**
     * 
     * Destroys session data.
     * 
     * Provided only to override abstract method, never actually called.
     * 
     * @param string $id The session ID.
     * 
     * @return bool
     * 
     */
    public function destroy($id)
    {
        return true;
    }
    
    /**
     * 
     * Removes old session data (garbage collection).
     * 
     * Provided only to override abstract method, never actually called.
     * 
     * @param int $lifetime Removes session data not updated since this many
     * seconds ago.  E.g., a lifetime of 86400 removes all session data not
     * updated in the past 24 hours.
     * 
     * @return bool
     * 
     */
    public function gc($lifetime)
    {
        return true;
    }
}