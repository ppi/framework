<?php
/**
 * 
 * Mail-transport adapter using an SMTP adapter connection.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Smtp.php 3858 2009-06-25 22:57:34Z pmjones $
 * 
 */
class Solar_Mail_Transport_Adapter_Smtp extends Solar_Mail_Transport_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency smtp A Solar_Smtp_Adapter dependency.  Default is 'smtp',
     *   which means to use the registered object named 'smtp'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Mail_Transport_Adapter_Smtp = array(
        'smtp' => 'smtp',
    );
    
    /**
     * 
     * The SMTP adapter dependency.
     * 
     * @var Solar_Smtp_Adapter
     * 
     */
    protected $_smtp;
    
    /**
     * 
     * Destructor; makes sure the SMTP connection is closed.
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
        if ($this->_smtp) {
            $this->_smtp->disconnect();
        }
    }
    
    /**
     * 
     * Sets the SMTP adapter.
     * 
     * @param Solar_Smtp_Adapter $smtp The SMTP adapter.
     * 
     * @return void
     * 
     */
    public function setSmtp(Solar_Smtp_Adapter $smtp)
    {
        $this->_smtp = $smtp;
    }
    
    /**
     * 
     * Sends the Solar_Mail_Message through an SMTP server connection; 
     * lazy-loads the SMTP dependency if needed.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    protected function _send()
    {
        // lazy-load the SMTP dependency if it's not already present
        if (! $this->_smtp) {
            $this->_smtp = Solar::dependency(
                'Solar_Smtp',
                $this->_config['smtp']
            );
        }
        
        // get the headers for the message
        $headers = $this->_mail->fetchHeaders();
        
        // who are we sending from?
        $from = null;
        foreach ($headers as $header) {
            if ($header[0] == 'Return-Path') {
                $from = trim($header[1], '<>');
                break;
            }
        }
        if (! $from) {
            throw $this->_exception('ERR_NO_RETURN_PATH');
        }
        
        // who are we sending to?
        $rcpt = $this->_mail->getRcpt();
        
        // get the content
        $content = $this->_mail->fetchContent();
        
        // change headers from array to string
        $headers = $this->_headersToString($headers);
        
        // prepare the message data
        $crlf = $this->_mail->getCrlf();
        $data = $headers . $crlf . $content;
        
        // make sure we're connected to the server
        if (! $this->_smtp->isConnected()) {
            $this->_smtp->connect();
            $this->_smtp->helo();
        }
        
        // reset previous connections
        $this->_smtp->rset();
        
        // tell who this is MAIL FROM
        $this->_smtp->mail($from);
        
        // tell who this is RCPT TO (each to, cc, and bcc)
        foreach ($rcpt as $addr) {
            $this->_smtp->rcpt($addr);
        }
        
        // send the message
        $this->_smtp->data($data, $crlf);
        
        // done!
        return true;
    }
}
