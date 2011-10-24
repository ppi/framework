<?php
/**
 * 
 * Abstract class for session adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Adapter.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
abstract class Solar_Session_Handler_Adapter extends Solar_Base {
    
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
        $this->_setSaveHandler();
    }
    
    /**
     * 
     * Destructor; calls session_write_close() so that the session gets
     * written before the object is destroyed.
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
        session_write_close();
    }
    
    /**
     * 
     * Sets session save handler to use the methods in this class.
     * 
     * @return void
     * 
     */
    protected function _setSaveHandler()
    {
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
    }
    
    /**
     * 
     * Opens the session handler.
     * 
     * @return bool
     * 
     */
    abstract public function open();
    
    /**
     * 
     * Closes the session handler.
     * 
     * @return bool
     * 
     */
    abstract public function close();
    
    /**
     * 
     * Reads session data.
     * 
     * @param string $id The session ID.
     * 
     * @return string The serialized session data.
     * 
     */
    abstract public function read($id);
    
    /**
     * 
     * Writes session data.
     * 
     * @param string $id The session ID.
     * 
     * @param string $data The serialized session data.
     * 
     * @return bool
     * 
     */
    abstract public function write($id, $data);
    
    /**
     * 
     * Destroys session data.
     * 
     * @param string $id The session ID.
     * 
     * @return bool
     * 
     */
    abstract public function destroy($id);
    
    /**
     * 
     * Removes old session data (garbage collection).
     * 
     * @param int $lifetime Removes session data not updated since this many
     * seconds ago.  E.g., a lifetime of 86400 removes all session data not
     * updated in the past 24 hours.
     * 
     * @return bool
     * 
     */
    abstract public function gc($lifetime);
}
