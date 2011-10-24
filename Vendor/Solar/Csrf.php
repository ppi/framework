<?php
/**
 * 
 * Class to detect cross-site request forgery attempts.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Csrf.php 4506 2010-03-08 22:37:19Z pmjones $
 * 
 */
class Solar_Csrf extends Solar_Base
{
    /**
     * 
     * The current value of the anti-CSRF token.
     * 
     * @var string
     * 
     */
    static protected $_current = null;
    
    /**
     * 
     * The name of the $_POST key containing the anti-CSRF token.
     * 
     * @var string
     * 
     */
    static protected $_key = '__csrf_key';
    
    /**
     * 
     * A Solar_Request dependency.
     * 
     * @var Solar_Request
     * 
     */
    static protected $_request;
    
    /**
     * 
     * A Solar_Session object.
     * 
     * @var Solar_Session
     * 
     */
    static protected $_session;
    
    /**
     * 
     * Has the token value been updated?
     * 
     * @var bool
     * 
     */
    static protected $_updated = false;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        if (! self::$_session) {
            self::$_session = Solar::factory('Solar_Session', array(
                'class' => 'Solar_Csrf',
            ));
        }
        
        if (! self::$_request) {
            self::$_request = Solar_Registry::get('request');
        }
        
        // ignore construct-time configuration for the key, but honor
        // it from the config file.  we want the key name to be the
        // same everywhere all the time.
        $key = Solar_Config::get('Solar_Csrf', 'key');
        if ($key) {
            self::$_key = $key;
        }
    }
    
    /**
     * 
     * Updates this object with current values.
     * 
     * This helps to maintain transitions between not having a session and
     * then having one; in the non-session state, there will be no token,
     * so we can't expect its presence until the next page load.
     * 
     * @return void
     * 
     */
    protected function _update()
    {
        if (self::$_updated) {
            // already updated with current values
            return;
        }
        
        // lazy-start the session if one exists
        self::$_session->lazyStart();
        if (! self::$_session->isStarted()) {
            // not started, nothing left to do
            return;
        }
        
        // the session has started. is there an existing csrf token?
        if (self::$_session->has('token')) {
            // retain the existing token
            self::$_current = self::$_session->get('token');
        } else {
            // no token, create a new one for the session.
            // we're transitioning from a non-token state, and
            // incoming forms won't have it yet, so we don't retain
            // the new token as the current value.
            self::$_session->set('token', uniqid(mt_rand(), true));
        }
        
        self::$_updated = true;
    }
    
    /**
     * 
     * Returns the name of the token key in $_POST values.
     * 
     * @return string
     * 
     */
    public function getKey()
    {
        return self::$_key;
    }
    
    /**
     * 
     * Gets the token value to be used in outgoing forms.
     * 
     * @return string
     * 
     */
    public function getToken()
    {
        $this->_update();
        return self::$_session->get('token');
    }
    
    /**
     * 
     * Sets the token value to be used in outgoing forms.
     * 
     * @param string $token The new token value.
     * 
     * @return string
     * 
     */
    public function setToken($token)
    {
        $this->_update();
        self::$_session->set('token', $token);
    }
    
    /**
     * 
     * Is there a token value in the session already?
     * 
     * @return string
     * 
     */
    public function hasToken()
    {
        $this->_update();
        return self::$_session->has('token');
    }
    
    /**
     * 
     * Returns the expected incoming value for the token.
     * 
     * Note that this may be different from the outgoing value, especially in
     * transitions from not having a session to having one.
     * 
     * @return string
     * 
     */
    public function getCurrent()
    {
        $this->_update();
        return self::$_current;
    }
    
    /**
     * 
     * Does the incoming request look like a cross-site forgery?
     * 
     * Only works for POST requests.
     * 
     * @return string
     * 
     */
    public function isForgery()
    {
        $this->_update();
        
        if (! self::$_request->isPost()) {
            // only POST requests can be cross-site request forgeries
            return false;
        }
        
        if (! self::$_current) {
            // there is no current value so it doesn't matter
            return false;
        }
        
        // get the incoming csrf value from $_POST
        $key = $this->getKey();
        $val = self::$_request->post($key);
        
        // if they don't match, it's a forgery
        return $val != self::$_current;
    }
}