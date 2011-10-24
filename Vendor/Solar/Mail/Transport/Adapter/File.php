<?php
/**
 * 
 * Pseudo-transport that writes the message headers and content to a file.
 * 
 * The files are saved in a configurable directory location, and are named
 * "solar_email_{date('Y-m-d_H-i-s.u')}" by default.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: File.php 4263 2009-12-07 19:25:31Z pmjones $
 * 
 */
class Solar_Mail_Transport_Adapter_File extends Solar_Mail_Transport_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string dir The directory where email files should be saved.  Default
     *   is the system temp directory.
     * 
     * @config string prefix Prefix file names with this value; default is 'solar_email_'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Mail_Transport_Adapter_File = array(
        'dir'    => null,
        'prefix' => 'solar_email_',
    );
    
    /**
     * 
     * Sets the default directory to write emails to (the temp dir).
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        
        if (Solar::$system) {
            $tmp = Solar::$system . '/tmp/mail/';
        } else {
            $tmp = Solar_Dir::tmp('/Solar_Mail_Transport_Adapter_File/');
        }
        
        $this->_Solar_Mail_Transport_Adapter_File['dir'] = $tmp;
    }
    
    /**
     * 
     * Writes the Solar_Mail_Message headers and content to a file.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    protected function _send()
    {
        $file = Solar_Dir::fix($this->_config['dir'])
              . $this->_config['prefix']
              . date('Y-m-d_H-i-s')
              . '.' . substr(microtime(), 2, 6);
        
        $text = $this->_headersToString($this->_mail->fetchHeaders())
              . $this->_mail->getCrlf()
              . $this->_mail->fetchContent();
        
        $result = file_put_contents($file, $text);
        
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }
}