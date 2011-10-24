<?php
/**
 * 
 * Class for reading access privileges from a text file.
 * 
 * The file format is ...
 * 
 *     0:flag 1:type 2:name 3:class 4:action
 * 
 * For example ...
 * 
 *     deny handle * * * * 
 *     allow role sysadmin * * * 
 *     allow handle + Solar_App_Bookmarks * * 
 *     deny handle boshag Solar_App_Bookmarks edit * 
 * 
 * @category Solar
 * 
 * @package Solar_Access
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: File.php 4405 2010-02-18 04:27:25Z pmjones $
 * 
 */
class Solar_Access_Adapter_File extends Solar_Access_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string file The path to the access file.
     * 
     * @var array
     * 
     */
    protected $_Solar_Access_Adapter_File = array(
        'file'   => '/path/to/access.txt',
    );
    
    /**
     * 
     * Fetch access privileges for a user handle and roles.
     * 
     * @param string $handle The user handle.
     * 
     * @param array $roles The user roles.
     * 
     * @return array
     * 
     */
    public function fetch($handle, $roles)
    {
        // force the full, real path to the file
        $file = realpath($this->_config['file']);
        
        // does the file exist?
        if (! Solar_File::exists($file)) {
            throw $this->_exception('ERR_FILE_NOT_READABLE', array(
                'file' => $this->_config['file'],
                'realpath' => $file,
            ));
        }
        
        $handle = trim($handle);
        
        // eventual access list for the handle and roles
        $list = array();
        
        // get the access source and split into lines
        $src = file_get_contents($this->_config['file']);
        $src = preg_replace('/[ \t]{2,}/', ' ', trim($src));
        $lines = explode("\n", $src);
        
        foreach ($lines as $line) {
            
            $line = trim($line);
            
            // allow blank lines
            if ($line == '') {
                continue;
            }
            
            // allow comment lines
            $char = substr($line, 0, 1);
            if ($char == '#') {
                continue;
            }
            
            // $info keys are ...
            // 0 => "allow" or "deny"
            // 1 => "handle", "role", or "owner"
            // 2 => handle/role name (not used by 'owner' type)
            // 3 => class name
            // 4 => action name
            $info = explode(' ', $line);
            if ($info[1] == 'handle' && $info[2] == $handle ||        // direct user handle match
                $info[1] == 'handle' && $info[2] == '+' && $handle || // any authenticated user
                $info[1] == 'handle' && $info[2] == '*' ||            // any user (incl anon)
                $info[1] == 'handle' && $info[2] == '?' && ! $handle || // only anon user
                $info[1] == 'role'   && in_array($info[2], $roles) || // direct role match
                $info[1] == 'role'   && $info[2] == '*' ||            // any role (incl anon)
                $info[1] == 'owner' ) {                               // content owner
                
                // keep the line
                $list[] = array(
                    'allow'   => ($info[0] == 'allow' ? true : false),
                    'type'    => $info[1],
                    'name'    => $info[2],
                    'class'   => $info[3],
                    'action'  => $info[4],
                );
            }
        }
        
        return $list;
    }
}
