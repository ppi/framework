<?php
/**
 * 
 * Adapter to fetch roles from a Unix-style groups file.
 * 
 * The file format is "group:user1,user2,user3\n".
 * 
 *     sysadmin:pmjones
 *     writer:pmjones,boshag,agtsmith
 *     editor:pmjones,agtsmith
 * 
 * @category Solar
 * 
 * @package Solar_Role
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: File.php 4405 2010-02-18 04:27:25Z pmjones $
 * 
 * @todo rename to Unix, add Ini file handler as well
 * 
 */
class Solar_Role_Adapter_File extends Solar_Role_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string file Where the roles file is located.
     * 
     * @var array
     * 
     */
    protected $_Solar_Role_Adapter_File = array(
        'file' => null
    );
    
    /**
     * 
     * Fetches the roles for a username.
     * 
     * @param string $handle User handle to get roles for.
     * 
     * @return array An array of discovered roles.
     * 
     */
    public function fetch($handle)
    {
        // force the full, real path to the file
        $file = realpath($this->_config['file']);
        
        // does the file exist?
        if (! Solar_File::exists($file)) {
            throw $this->_exception('ERR_FILE_NOT_READABLE', array(
                'file' => $file,
            ));
        }
        
        // load the file as an array of lines
        $lines = file($file);
        
        // the discovered roles
        $roles = array();
        
        // loop through each line, find the group, then see if the user
        // is on the line anywhere
        foreach ($lines as $line) {
            
            // eliminate all spaces, which cause mismatches when we get to
            // in_array() later
            $line = preg_replace("/\s/", '', $line);
            
            // break apart at first ':'
            $pos = strpos($line, ':');
            
            // the group name is the part before the ':'
            $group = substr($line, 0, $pos);
            
            // the list of user handles comes after
            $tmp = substr($line, $pos+1);
            $list = explode(',', $tmp);
            
            // is the user part of the group?
            if (in_array($handle, $list)) {
                $roles[] = $group;
            }
        }
        
        // done!
        return $roles;
    }
}
