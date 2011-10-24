<?php
/**
 * 
 * Class for denying all access to all users.
 * 
 * @category Solar
 * 
 * @package Solar_Access
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: None.php 3333 2008-08-05 22:57:09Z pmjones $
 * 
 */
class Solar_Access_Adapter_None extends Solar_Access_Adapter
{
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
        return array(
            array(
                'allow'   => false,
                'type'    => '*',
                'name'    => '*',
                'class'   => '*',
                'action'  => '*',
            ),
        );
    }
    
    /**
     * 
     * Checks to see if the current user is the owner of application-specific
     * content; always returns false, since no access is allowed at all.
     * 
     * @param mixed $content The content to check ownership of.
     * 
     * @return bool
     * 
     */
    public function isOwner($content)
    {
        return false;
    }
}
