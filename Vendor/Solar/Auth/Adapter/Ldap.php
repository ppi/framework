<?php
/**
 * 
 * Authenticate against an LDAP server.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Ldap.php 4405 2010-02-18 04:27:25Z pmjones $
 * 
 */
class Solar_Auth_Adapter_Ldap extends Solar_Auth_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string uri URL to the LDAP server, for example "ldaps://example.com:389".
     * 
     * @config string format Sprintf() format string for the LDAP query; %s
     *   represents the username.  Example: "uid=%s,dc=example,dc=com".
     * 
     * @config string filter A regular-expression snippet that lists allowed characters
     *   in the username.  This is to help prevent LDAP injections.  Default
     *   expression is '\w' (that is, only word characters are allowed).
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Adapter_Ldap = array(
        'uri'    => null,
        'format' => null,
        'filter' => '\w',
    );
    
    /**
     * 
     * Checks to make sure the LDAP extension is available.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        if (! extension_loaded('ldap')) {
            throw $this->_exception('ERR_EXTENSION_NOT_LOADED', array(
                'extension' => 'ldap',
            ));
        }
    }
    
    /**
     * 
     * Verifies a username handle and password.
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     * 
     */
    protected function _processLogin()
    {
        // connect
        $conn = @ldap_connect($this->_config['uri']);
        
        // did the connection work?
        if (! $conn) {
            throw $this->_exception('ERR_CONNECTION_FAILED', $this->_config);
        }
        
        // upgrade to LDAP3 when possible
        @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        
        // filter the handle to prevent LDAP injection
        $regex = '/[^' . $this->_config['filter'] . ']/';
        $this->_handle = preg_replace($regex, '', $this->_handle);
        
        // bind to the server
        $rdn = sprintf($this->_config['format'], $this->_handle);
        $bind = @ldap_bind($conn, $rdn, $this->_passwd);
        
        // did the bind succeed?
        if ($bind) {
            ldap_close($conn);
            return array('handle' => $this->_handle);
        } else {
            $this->_err = @ldap_errno($conn) . " " . @ldap_error($conn);
            ldap_close($conn);
            return false;
        }
    }
}
