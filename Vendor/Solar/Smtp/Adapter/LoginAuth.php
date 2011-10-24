<?php
/**
 * 
 * SMTP adapter with "login" authentication at connection time.
 * 
 * @category Solar
 * 
 * @package Solar_Smtp
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: LoginAuth.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Smtp_Adapter_LoginAuth extends Solar_Smtp_Adapter_PlainAuth
{
    /**
     * 
     * Performs AUTH LOGIN authentication with username and password.
     * 
     * @return bool
     * 
     */
    public function auth()
    {
        if (! $this->_auth) {
            
            // issue AUTH LOGIN
            $this->_send('AUTH LOGIN');
            $this->_expect(334);
            
            // send username
            $this->_send(base64_encode($this->_username));
            $this->_expect(334);
            
            // send password
            $this->_send(base64_encode($this->_password));
            $this->_expect(235);
            
            // guess it worked ;-)
            $this->_auth = true;
        }
        
        return $this->_auth;
    }
}
