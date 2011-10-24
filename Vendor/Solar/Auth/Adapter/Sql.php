<?php
/**
 * 
 * Authenticate against an SQL database table.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Sql.php 4412 2010-02-22 20:08:22Z pmjones $
 * 
 */
class Solar_Auth_Adapter_Sql extends Solar_Auth_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency sql A Solar_Sql dependency injection.
     * 
     * @config string table Name of the table holding authentication data.
     * 
     * @config string handle_col Name of the column with the user handle ("username").
     * 
     * @config string passwd_col Name of the column with the MD5-hashed passwd.
     * 
     * @config string email_col Name of the column with the email address.
     * 
     * @config string moniker_col Name of the column with the display name (moniker).
     * 
     * @config string uri_col Name of the column with the website URI.
     * 
     * @config string uid_col Name of the column with the numeric user ID ("user_id").
     * 
     * @config string hash_algo The hashing algorithm for the password.  Default is 'md5'.
     *   See [[php::hash_algos() | ]] for a list of accepted algorithms.
     * 
     * @config string salt A salt prefix to make cracking passwords harder.
     * 
     * @config string|array where Additional _multiWhere() conditions to use
     *   when selecting rows for authentication.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Adapter_Sql = array(
        'sql'         => 'sql',
        'table'       => 'members',
        'handle_col'  => 'handle',
        'passwd_col'  => 'passwd',
        'email_col'   => null,
        'moniker_col' => null,
        'uri_col'     => null,
        'uid_col'     => null,
        'hash_algo'   => 'md5',
        'salt'        => null,
        'where'       => array(),
    );
    
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
        // get the dependency object of class Solar_Sql
        $obj = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // get a selection tool using the dependency object
        $select = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $obj)
        );
        
        // list of optional columns as (property => field)
        $optional = array(
            'email'   => 'email_col',
            'moniker' => 'moniker_col',
            'uri'     => 'uri_col',
            'uid'     => 'uid_col',
        );
        
        // always get the user handle
        $cols = array($this->_config['handle_col']);
        
        // get optional columns
        foreach ($optional as $key => $val) {
            if ($this->_config[$val]) {
                $cols[] = $this->_config[$val];
            }
        }
        
        // salt and hash the password
        $hash = hash(
            $this->_config['hash_algo'],
            $this->_config['salt'] . $this->_passwd
        );
        
        // make sure the handle col is dotted so it gets quoted properly
        $handle_col = $this->_config['handle_col'];
        if (strpos($handle_col, '.') === false) {
            $handle_col = "{$this->_config['table']}.{$handle_col}";
        }
        
        // make sure the passwd col is dotted so it gets quoted properly
        $passwd_col = $this->_config['passwd_col'];
        if (strpos($passwd_col, '.') === false) {
            $passwd_col = "{$this->_config['table']}.{$passwd_col}";
        }
        
        // build the select, fetch up to 2 rows (just in case there's actually
        // more than one, we don't want to select *all* of them).
        $select->from($this->_config['table'], $cols)
               ->where("$handle_col = ?", $this->_handle)
               ->where("$passwd_col = ?", $hash)
               ->multiWhere($this->_config['where'])
               ->limit(2);
               
        // get the results
        $rows = $select->fetchAll();
        
        // if we get back exactly 1 row, the user is authenticated;
        // otherwise, it's more or less than exactly 1 row.
        if (count($rows) == 1) {
            
            // set base info
            $info = array('handle' => $this->_handle);
            
            // set optional info from optional cols
            $row = current($rows);
            foreach ($optional as $key => $val) {
                if ($this->_config[$val]) {
                    $info[$key] = $row[$this->_config[$val]];
                }
            }
            
            // done
            return $info;
            
        } else {
            return false;
        }
    }
}
