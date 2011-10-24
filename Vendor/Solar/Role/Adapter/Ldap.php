<?php
/**
 * 
 * Adapter to fetch roles from an LDAP server.
 * 
 * @category Solar
 * 
 * @package Solar_Role
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Ldap.php 4405 2010-02-18 04:27:25Z pmjones $
 * 
 */
class Solar_Role_Adapter_Ldap extends Solar_Role_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string url URL to the LDAP server. Takes the format of "ldaps://example.com:389".
     * 
     * @config string basedn The base DN for the LDAP search; example: "o=my company,c=us".
     * 
     * @config string filter An sprintf() filter string for the LDAP search; %s represents the username.
     * Example: "uid=%s".
     * 
     * @config string attrib Use these attributes to find role names.
     * 
     * @config string binddn Bind to the LDAP server as this distinguished name.
     * 
     * @config string bindpw Bind to the LDAP server as with this password.
     * 
     * @var array
     * 
     */
    protected $_Solar_Role_Adapter_Ldap = array(
        'url'    => null,
        'basedn' => null,
        'filter' => null,
        'attrib' => array('ou'),
        'binddn' => null,
        'bindpw' => null,
    );
    
    /**
     * 
     * Checks to make sure the LDAP extension is loaded.
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
     * Fetch roles for a user.
     * 
     * @param string $handle Username to get roles for.
     * 
     * @return array An array of roles discovered in LDAP.
     * 
     */
    public function fetch($handle)
    {
        // connect
        $conn = @ldap_connect($this->_config['url']);
        
        // did the connection work?
        if (! $conn) {
            throw $this->_exception('ERR_CONNECTION_FAILED', array(
                'url' => $this->_config['url'],
            ));
        }
        
        // upgrade to LDAP3 when possible
        @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        
        // bind to the server
        if ($this->_config['binddn']) {
            // authenticated bind
            $bind = @ldap_bind($conn, $this->_config['binddn'], $this->_config['bindpw']);
        } else {
            // anonumous bind
            $bind = @ldap_bind($conn);
        }
        
        // did we bind to the server?
        if (! $bind) {
            // not using $this->_exception() because we need fine control
            // over the error text
            throw Solar::exception(
                get_class($this),
                @ldap_errno($conn),
                @ldap_error($conn),
                array($this->_config)
            );
        }
        
        // search for the groups
        $filter = sprintf($this->_config['filter'], $handle);
        $attrib = (array) $this->_config['attrib'];
        $result = ldap_search($conn, $this->_config['basedn'], $filter, $attrib);
        
        // get the first entry from the search result and free the result.
        $entry = ldap_first_entry($conn, $result);
        ldap_free_result($result);
        
        // now get the data from the entry and close the connection.
        $data = ldap_get_attributes($conn, $entry);
        ldap_close($conn);
        
        // go through the attribute data and add to the list. only
        // retain numeric keys; the ldap entry will have some
        // associative keys that are metadata and not useful to us here.
        $list = array();
        foreach ($attrib as $attr) {
            if (isset($data[$attr]) && is_array($data[$attr])) {
                foreach ($data[$attr] as $key => $val) {
                    if (is_int($key)) {
                        $list[] = $val;
                    }
                }
            }
        }
        
        // done!
        return $list;
    }
}
