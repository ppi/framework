<?php
/**
 * 
 * Meta-container for the current user to hold auth and roles.
 * 
 * When prefs and permissions come along, will hold those too.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: User.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
class Solar_User extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency auth A Solar_Auth dependency injection to
     * deal with user authentication and identification.
     * 
     * @config dependency role A Solar_Role dependency injection to
     * read the user roles.
     * 
     * @config dependency access A Solar_Access dependency injection to
     * deal with user authorization and access.
     * 
     * @var array
     * 
     */
    protected $_Solar_User = array(
        'auth'   => null,
        'role'   => null,
        'access' => null
    );
    
    /**
     * 
     * User authentication object.
     * 
     * @var object
     * 
     */
    public $auth;
    
    /**
     * 
     * User roles (group membership) object.
     * 
     * @var object
     * 
     */
    public $role;
    
    /**
     * 
     * Authorized access object.
     * 
     * @var object
     * 
     */
    public $access;
    
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
        
        // setup
        $this->_setup();
        
        // start up authentication
        $this->_authStart();
        
        // is this a valid authenticated user?
        if ($this->auth->isValid()) {
            $this->_loadRoles();
        } else {
            // no, user is not valid.  
            // clear out any previous roles.
            $this->role->reset();
            $this->access->reset();
        }
        
        // load up the access list for the handle and roles
        $this->_loadAccess();
    }
    
    /**
     * 
     * Setup for the auth, role, and access objects.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        // set up an authentication object.
        $this->auth = Solar::dependency('Solar_Auth', $this->_config['auth']);
        
        // set up the roles object.
        $this->role = Solar::dependency('Solar_Role', $this->_config['role']);
        
        // set up the access object.
        $this->access = Solar::dependency('Solar_Access', $this->_config['access']);
    }
    
    /**
     * 
     * Starts authenticated session.
     * 
     * @return void
     * 
     */
    protected function _authStart()
    {
        $this->auth->start();
    }
    
    /**
     * 
     * Loads the role object.
     * 
     * @return void
     * 
     */
    protected function _loadRoles()
    {
        $this->role->load($this->auth->handle);
    }
    
    /**
     * 
     * Loads the access object.
     * 
     * @return void
     * 
     */
    protected function _loadAccess()
    {
        $this->access->load($this->auth, $this->role);
    }
}
