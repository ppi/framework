<?php
/**
 * 
 * Abstract mail-transport adapter.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Adapter.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 */
abstract class Solar_Mail_Transport_Adapter extends Solar_Base {
    
    /**
     * 
     * The Solar_Mail_Message to be sent.
     * 
     * @var Solar_Mail_Message
     * 
     */
    protected $_mail;
    
    /**
     * 
     * Sends a Solar_Mail_Message.
     * 
     * @param Solar_Mail_Message $mail The message to send.
     * 
     * @return void
     * 
     */
    public function send(Solar_Mail_Message $mail)
    {
        $this->_mail = $mail;
        return $this->_send();
    }
    
    /**
     * 
     * Actual sending process for adapter classes.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    abstract protected function _send();
    
    /**
     * 
     * Converts an array of headers to a string.
     * 
     * @param array $headers The array of headers.
     * 
     * @return string The headers as a string.
     * 
     */
    protected function _headersToString($headers = null)
    {
        if (! $headers) {
            $headers = $this->_mail->fetchHeaders();
        }
        $crlf = $this->_mail->getCrlf();
        $output = '';
        foreach ($headers as $header) {
            $output .= $header[0] . ': ' . $header[1] . $crlf;
        }
        return $output;
    }
}