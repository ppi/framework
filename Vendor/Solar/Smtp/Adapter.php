<?php
/**
 * 
 * Abstract SMTP adapter.
 * 
 * Heavily modified and refactored from the Zend_Protocol_Smtp package and
 * related classes.
 * 
 * Concrete classes should implement the auth() method.
 * 
 * @category Solar
 * 
 * @package Solar_Smtp
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Adapter.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
abstract class Solar_Smtp_Adapter extends Solar_Base {
    
    /**
     * 
     * Default configuration values.
     * 
     * @config string host The SMTP host to connect to.
     * 
     * @config string port Connect to the SMTP host on this port.
     * 
     * @config string crlf The CRLF string to use at the end of each line.
     * 
     * @config string secure The security protocol for the connection, if any
     * (e.g., 'ssl' or 'tls').
     * 
     * @config string client Use this as the client address making the SMTP
     * request.
     * 
     * @config string flags The stream connection flags to use.
     * 
     * @config string context The stream context to use, if any.
     *
     * @var array
     * 
     */
    protected $_Solar_Smtp_Adapter = array(
        'host'   => '127.0.0.1',
        'port'   => null,
        'crlf'   => "\r\n",
        'secure' => null,
        'client' => '127.0.0.1',
        'flags'  => STREAM_CLIENT_CONNECT,
        'context'=> null,
    );
    
    /**
     * 
     * The client address making SMTP request (that is, the local machine).
     * 
     * @var string
     * 
     */
    protected $_client = null;
    
    /**
     * 
     * Line-ending string; default "\r\n".
     * 
     * @var string
     * 
     */
    protected $_crlf = "\r\n";
    
    /**
     * 
     * Timeout in seconds for initiating session; default 30.
     * 
     * @var int
     * 
     */
    protected $_timeout = 30;
    
    /**
     * 
     * Hostname or IP address of SMTP server.
     * 
     * @var string
     * 
     */
    protected $_host;
    
    /**
     * 
     * Connect to SMTP server on this port.
     * 
     * @var int
     * 
     */
    protected $_port;
    
    /**
     * 
     * Connection resource.
     * 
     * @var resource
     * 
     */
    protected $_conn;
    
    /**
     * 
     * Log of requests and response strings.
     * 
     * @var array()
     * 
     */
    protected $_log = array();
    
    /**
     * 
     * The transport method for the socket; default is 'tcp'.
     * 
     * Values are 'tcp' and 'ssl'.
     * 
     * @var string
     * 
     */
    protected $_transport = 'tcp';
    
    /**
     * 
     * The security protocol for this connection, if any.
     * 
     * Values are 'ssl' and 'tls'.
     * 
     * @var string
     * 
     */
    protected $_secure;
    
    /**
     * 
     * The connection flags for this connection; default is 
     * STREAM_CLIENT_CONNECT.
     * 
     * @var int
     * 
     */
    protected $_flags = STREAM_CLIENT_CONNECT;
    
    /**
     * 
     * The stream context for this connection, if any.
     * 
     * @var array
     * 
     */
    protected $_context = array();
    
    /**
     * 
     * Has a session been started (that is, has HELO/EHLO been issued)?
     * 
     * @var bool
     * 
     */
    protected $_helo = false;
    
    /**
     * 
     * Has SMTP AUTH has been issued successfully?
     * 
     * @var bool
     * 
     */
    protected $_auth = false;
    
    /**
     * 
     * Has SMTP MAIL been issued?
     * 
     * @var bool
     */
    protected $_mail = false;
    
    /**
     * 
     * Has SMTP RCPT been issued?
     * 
     * @var bool
     * 
     */
    protected $_rcpt = false;
    
    /**
     * 
     * Has SMTP DATA has been issued successfully?
     * 
     * @var bool
     * 
     */
    protected $_data = null;
    
    /**
     * 
     * Expected timeouts for various activities.
     * 
     * @var array
     * 
     * @see _expect()
     * 
     */
    protected $_time = array(
        'conn' => 300, // connection success
        'ehlo' => 300,
        'tls'  => 180, // start tls
        'helo' => 300,
        'mail' => 300, // mail from
        'rcpt' => 300, // rcpt to
        'data' => 120,
        'dot'  => 600, // dot ending a message
        'rset' => 0,
        'noop' => 300,
        'vrfy' => 300,
        'quit' => 300,
    );
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // set explicit crlf
        if ($this->_config['crlf']) {
            $this->_crlf = $this->_config['crlf'];
        }
        
        // set explicit host
        if ($this->_config['host']) {
            $this->_host = $this->_config['host'];
        }
        
        // set secure connection?
        if ($this->_config['secure']) {
            $type = strtolower($this->_config['secure']);
            switch ($type) {
            case 'tls':
                $this->_secure = 'tls';
                break;
            case 'ssl':
                $this->_transport = 'ssl';
                $this->_secure = 'ssl';
                $this->_port = 465;
                break;
            default:
                throw $this->_exception('ERR_SECURE_TYPE', array(
                    'secure' => $type,
                ));
                break;
            }
        }
        
        // set explicit port; overrides secure port
        if ($this->_config['port']) {
            $this->_port = $this->_config['port'];
        } elseif (empty($this->_port)) {
            // set the default port to the one from php.ini
            $this->_port = ini_get('smtp_port');
        }
        
        // set explicit client
        if ($this->_config['client']) {
            $this->_client = $this->_config['client'];
        }
        
        // set explicit flags
        if ($this->_config['flags']) {
            $this->_flags = $this->_config['flags'];
        }
        
        // build the context property
        if (is_resource($this->_config['context'])) {
            // assume it's a context resource
            $this->_context = $this->_config['context'];
        } elseif (is_array($this->_config['context'])) {
            // create from scratch
            $this->_context = stream_context_create($this->_config['context']);
        } else {
            // not a resource, not an array, so ignore.
            // have to use a resource of some sort, so create
            // a blank context resource.
            $this->_context = stream_context_create(array());
        }
    }
    
    /**
     * 
     * Disconnects from the SMTP server if needed.
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
        $this->disconnect();
    }
    
    /**
     * 
     * Sets the line-ending string.
     * 
     * @param string $crlf The line-ending string.
     * 
     * @return void
     * 
     */
    public function setCrlf($crlf)
    {
        $this->_crlf = $crlf;
    }
    
    /**
     * 
     * Returns the line-ending string.
     * 
     * @param string $crlf The line-ending string.
     * 
     * @return void
     * 
     */
    public function getCrlf()
    {
        return $this->_crlf;
    }
    
    /**
     * 
     * Returns the connection log.
     * 
     * @return array
     * 
     */
    public function getLog()
    {
        return $this->_log;
    }
    
    /**
     * 
     * Clears the connection log.
     * 
     * @return void
     * 
     */
    public function resetLog()
    {
        $this->_log = array();
    }
    
    /**
     * 
     * Are we currently connected to the server?
     * 
     * @return bool
     * 
     */
    public function isConnected()
    {
        return (bool) $this->_conn;
    }
    
    /**
     * 
     * Connects to the SMTP server and sets the timeout.
     * 
     * @return void
     * 
     * @throws Solar_Smtp_Exception_CannotOpenSocket
     * 
     * @throws Solar_Smtp_Exception_CannotSetTimeout
     * 
     * @todo Can we combine this into helo() ?
     * 
     */
    public function connect()
    {
        $errstr = '';
        $errnum = 0;
        $server = $this->_transport . '://'
                . $this->_host . ':'
                . $this->_port;
        
        // open connection
        $this->_conn = stream_socket_client(
            $server,
            $errnum,
            $errstr,
            $this->_timeout,
            $this->_flags,
            $this->_context
        );
        
        // connected?
        if (! $this->_conn) {
            throw $this->_exception('ERR_CANNOT_OPEN_SOCKET', array(
                'errstr' => $errstr,
                'errnum' => $errnum,
            ));
        }
        
        $result = stream_set_timeout($this->_conn, $this->_timeout);
        if (! $result) {
            throw $this->_exception('ERR_CANNOT_SET_TIMEOUT');
        }
    }
    
    /**
     * 
     * Issues HELO/EHLO sequence to starts the session.
     * 
     * Automatically calls $this->auth() after starting the session.
     * 
     * @return void
     * 
     * @throws Solar_Smtp_Exception_CannotEnableTls
     * 
     */
    public function helo()
    {
        // don't try HELO more than once per session
        if ($this->_helo) {
            return;
        }
        
        // send HELO, timeout at 5 minutes
        $this->_expect(220, $this->_time['conn']);
        $this->_ehlo();
        
        // are we trying for a TLS connection?
        if ($this->_secure == 'tls') {
            
            // send STARTTLS, wait 3 minutes
            $this->_send('STARTTLS');
            $this->_expect(220, $this->_time['tls']);
            
            $result = stream_socket_enable_crypto($this->_conn, true,
                STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            if (! $result) {
                throw $this->_exception('ERR_CANNOT_ENABLE_TLS');
            }
            
            $this->_ehlo($this->_client);
        }
        
        // session has started
        $this->_helo = true;
        
        // automatically attempt authentication
        $this->auth();
    }
    
    /**
     * 
     * Send EHLO or HELO, depending on SMTP host capability.
     * 
     * @return void
     * 
     */
    protected function _ehlo()
    {
        try {
            // modern, timeout 5 minutes
            $this->_send('EHLO ' . $this->_client);
            $this->_expect(250, $this->_time['ehlo']);
        } catch (Solar_Smtp_Exception $e) {
            // legacy, timeout 5 minutes
            $this->_send('HELO ' . $this->_client);
            $this->_expect(250, $this->_time['helo']);
        }
    }
    
    /**
     * 
     * Issues SMTP MAIL FROM to indicate who the message is from.
     * 
     * @param string $addr The "From:" email address.
     * 
     * @return void
     * 
     * @throws Solar_Smtp_Exception_NoSession
     * 
     */
    public function mail($addr)
    {
        // need to have started a session via HELO
        if (! $this->_helo) {
            throw $this->_exception('ERR_NO_HELO');
        }
        
        // issue MAIL FROM, 5 minute timeout
        $this->_send('MAIL FROM:<' . $addr . '>');
        $this->_expect(250, $this->_time['mail']);
        
        // clear out previous flags
        $this->_mail = true;
        $this->_rcpt = false;
        $this->_data = false;
    }
    
    /**
     * 
     * Issues SMTP RCPT TO to indicate who the message is to.
     * 
     * @param string $addr One "To:" email address.
     * 
     * @throws Solar_Smtp_Exception_NoMail
     * 
     * @return void
     * 
     */
    public function rcpt($addr)
    {
        // need to have issued MAIL FROM first
        if (! $this->_mail) {
            throw $this->_exception('ERR_NO_MAIL');
        }
        
        // issue RCPT TO, 5 minute timeout
        $this->_send('RCPT TO:<' . $addr . '>');
        $this->_expect(array(250, 251), $this->_time['rcpt']);
        
        // it worked
        $this->_rcpt = true;
    }
    
    /**
     * 
     * Issues SMTP DATA to send the email message itself.
     * 
     * @param string $data The message data.
     * 
     * @param string $crlf The CRLF sequence used in the message; if empty,
     * will use $this->_crlf.
     * 
     * @return void
     * 
     * @throws Solar_Smtp_Exception_NoRcpt
     * 
     */
    public function data($data, $crlf = null)
    {
        if (! $crlf) {
            $crlf = $this->_crlf;
        }
        
        // needs to have issued RCPT TO first
        if (! $this->_rcpt) {
            throw $this->_exception('ERR_NO_RCPT');
        }
        
        // issue DATA and wait up to 2 minutes
        $this->_send('DATA');
        $this->_expect(354, $this->_time['data']); 
        
        // now send the message one line at a time
        $lines = explode($crlf, $data);
        foreach ($lines as $line) {
            // Escape lines prefixed with a '.'
            if ($line && $line[0] == '.') {
                $line = '.' . $line;
            }
            $this->_send($line);
        }
        
        // issue a single dot to indicate message ending.
        // timeout at 10 minutes.
        $this->_send('.');
        $this->_expect(250, $this->_time['dot']); 
        
        // data has been sent successfully
        $this->_data = true;
    }
    
    /**
     * 
     * Issues SMTP RSET to reset the connection and clear transaction flags.
     * 
     * @return void
     * 
     */
    public function rset()
    {
        $this->_send('RSET');
        $this->_expect(250, $this->_time['rset']);
        $this->_mail = false;
        $this->_rcpt = false;
        $this->_data = false;
    }
    
    /**
     * 
     * Issues SMTP NOOP to keep the connection alive (or check the connection).
     * 
     * @return void
     * 
     */
    public function noop()
    {
        // timeout at 5 minutes
        $this->_send('NOOP');
        $this->_expect(250, $this->_time['noop']);
    }
    
    /**
     * 
     * Issues SMTP VRFY to verify a username or email address at the server.
     * 
     * @param string $addr Username or address to verify.
     * 
     * @return void
     * 
     */
    public function vrfy($addr)
    {
        // timeout at 5 minutes
        $this->_send('VRFY ' . $addr);
        $this->_expect(array(250, 251, 252), $this->_time['vrfy']); 
    }
    
    /**
     * 
     * Issues SMTP QUIT to end the current session.
     * 
     * @return void
     * 
     */
    public function quit()
    {
        if ($this->_helo) {
            // issue QUIT to end the session.
            // timeout at 5 minutes.
            $this->_send('QUIT');
            $this->_expect(221, $this->_time['quit']); 
            
            // clear flags
            $this->_helo = false;
            $this->_mail = false;
            $this->_rcpt = false;
            $this->_data = false;
        }
    }
    
    /**
     * 
     * Issues SMTP AUTH (if not already issued) and returns success indicator.
     * 
     * The default implementation does not issue SMTP AUTH; extended classes
     * may implement this as needed.
     * 
     * @return bool
     * 
     */
    abstract public function auth();
    
    /**
     * 
     * Issues SMTP QUIT and disconnects from the SMTP server.
     * 
     * @return void
     * 
     */
    public function disconnect()
    {
        $this->quit();
        if (is_resource($this->_conn)) {
            fclose($this->_conn);
        }
    }
    
    /**
     * 
     * Sends a request line to the SMTP server.
     * 
     * @param string $line The request line.
     * 
     * @return int|bool Number of bytes written to remote host.
     * 
     */
    protected function _send($line)
    {
        // try to prevent command injections
        $line = str_replace(array("\r", "\n"), '', $line);
        
        // must be connected
        if (! is_resource($this->_conn)) {
            throw $this->_exception('ERR_NO_CONNECTION', array(
                'host' => $this->_host,
                'port' => $this->_port,
            ));
        }
        
        // save the request line to the internal log
        $this->_log[] = $line;
        
        // write the request line to the connection stream
        $result = fwrite(
            $this->_conn,
            $line . $this->_crlf
        );
        
        // did the send work?
        if ($result === false) {
            throw $this->_exception('ERR_SEND_FAILED', array(
                'host' => $this->_host,
                'port' => $this->_port,
            ));
        }
        
        return $result;
    }
    
    /**
     * 
     * Receives a response line from the SMTP server.
     * 
     * @param int $timeout Timeout in seconds.
     * 
     * @return string
     * 
     */
    protected function _recv($timeout = null)
    {
        if (! is_resource($this->_conn)) {
            throw $this->_exception('ERR_NO_CONNECTION', array(
                'host' => $this->_host,
                'port' => $this->_port,
            ));
        }
    
        // timeout specified?
        if ($timeout) {
           stream_set_timeout($this->_conn, $timeout);
        }
        
        // retrieve response and save to log (without crlf)
        $line = fgets($this->_conn, 1024);
        $this->_log[] = rtrim($line);
    
        // did we time out?
        $info = stream_get_meta_data($this->_conn);
        if (! empty($info['timed_out'])) {
            throw $this->_exception('ERR_CONNECTION_TIMEOUT', array(
                'host'    => $this->_host,
                'port'    => $this->_port,
            ));
        }
        
        // did we actually receive a response?
        if ($line === false) {
            throw $this->_exception('ERR_NO_RESPONSE', array(
                'host' => $this->_host,
                'port' => $this->_port,
            ));
        }
        
        return $line;
    }
    
    /**
     * 
     * Receive lines from the SMTP server and look for an expected response
     * code.
     * 
     * @param array $list A list of one or more expected response codes. 
     * 
     * @param int $timeout The timeout for this individual operation, if any.
     * 
     * @return string The last text message received from the server (not 
     * including the response code).
     * 
     */
    protected function _expect($list, $timeout = null)
    {
        // the list of expected codes, forced to an array
        $list = (array) $list;
        
        // the parsed response code
        $code = null;
        
        // the parsed response text
        $text = '';
        
        do {
            
            // get each line in turn
            $line = $this->_recv($timeout);
            
            // parse the line for a response code and message text.
            // e.g., "250 Ok".
            sscanf($line, '%d%s', $code, $text);
            
            // did we get a code **not** in the expected list?
            if ($code === null || ! in_array($code, $list)) {
                throw $this->_exception('ERR_UNEXPECTED_RESPONSE', array(
                    'host' => $this->_host,
                    'port' => $this->_port,
                    'code' => $code,
                    'text' => $line,
                ));
            }
    
        } while ($text && $text[0] == '-'); 
        // The '-' message prefix indicates an information string,
        // as opposed to an actual response string.
        
        // returns the last text message
        return $text;
    }
}
