<?php
/**
 * 
 * Adapter to fetch roles from no source at all; always returns an empty array.
 * 
 * @category Solar
 * 
 * @package Solar_Role
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: None.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Role_Adapter_None extends Solar_Role_Adapter
{
    /**
     * 
     * Fetch the roles.
     * 
     * @param string $handle User handle to get roles for.
     * 
     * @return array An array of discovered roles.
     * 
     */
    public function fetch($handle)
    {
        return array();
    }
}
