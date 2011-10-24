<?php
/**
 * 
 * Authenticate against nothing; defaults all authentication to "failed".
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: None.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Auth_Adapter_None extends Solar_Auth_Adapter
{
    /**
     * 
     * Verifies a username handle and password.
     * 
     * **Never** verifies the user; this closes off authentication.
     * 
     * @return false
     * 
     * 
     */
    protected function _processLogin()
    {
        return false;
    }
}
