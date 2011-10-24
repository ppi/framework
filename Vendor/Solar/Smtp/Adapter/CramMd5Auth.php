<?php
/**
 * 
 * SMTP adapter with "cram-md5" authentication at connection time.
 * 
 * @category Solar
 * 
 * @package Solar_Smtp
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: CramMd5Auth.php 3231 2008-07-09 18:14:30Z pmjones $
 * 
 */
class Solar_Smtp_Adapter_CramMd5Auth extends Solar_Smtp_Adapter_PlainAuth
{
    /**
     * 
     * Performs AUTH CRAM-MD5 with username, password, and server challenge.
     * 
     * @return bool
     * 
     */
    public function auth()
    {
        if (! $this->_auth) {
            
            // issue AUTH CRAM-MD5 and get the server challenge
            $this->_send('AUTH CRAM-MD5');
            $challenge = $this->_expect(334);
            $challenge = base64_decode($challenge);
            
            // send the password hashed with the server challenge
            $hash = $this->_hashHmacMd5($this->_password, $challenge); 
            $this->_send(base64_encode($this->_username . ' ' . $hash));
            $this->_expect(235);
            
            // guess it worked
            $this->_auth = true;
        }
        
        return $this->_auth;
    }
    
    /**
     * 
     * Prepare a hashed response to server challenge. Apparently hash_hmac()
     * doesn't do the trick.
     * 
     * Adapted with minor naming changes from the Zend Framework class method
     * Zend_Mail_Protocol_Smtp_Auth_Crammd5::_hmacMd5.
     * 
     * @param string $password The password to hash.
     * 
     * @param string $challenge The server challenge.
     * 
     * @return string The hashed response to the challenge.
     * 
     * @see [[php::hash_hmac() | ]]
     * 
     * @see 
     */
    protected function _hashHmacMd5($password, $challenge)
    {
        if (strlen($password) > 64) {
            $password = pack('H32', hash('md5', $password));
        } elseif (strlen($password) < 64) {
            $password = str_pad($password, 64, chr(0));
        }
        
        $k_ipad = substr($password, 0, 64) ^ str_repeat(chr(0x36), 64);
        $k_opad = substr($password, 0, 64) ^ str_repeat(chr(0x5C), 64);
        
        $inner = pack('H32', hash('md5', $k_ipad . $challenge));
        $hash = hash('md5', $k_opad . $inner);
        
        return $hash;
    }
}
