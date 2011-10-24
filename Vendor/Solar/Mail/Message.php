<?php
/**
 * 
 * Class to build an email message for sending through a transport.
 * 
 * Heavily modified and refactored from Zend_Mail_Message and related classes.
 * 
 * @category Solar
 * 
 * @package Solar_Mail Email-sending tools.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Message.php 4555 2010-05-05 21:44:55Z pmjones $
 * 
 */
class Solar_Mail_Message extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string boundary The default boundary value for separating message parts.
     * 
     * @config string charset The character-set for messages; default is 'utf-8'.
     * 
     * @config string encoding The encoding for messages; default is '8bit'.
     * 
     * @config string crlf The line-ending string to use; default is "\r\n".
     * 
     * @config array headers An array of key-value pairs where the key is the header label
     *   and the value is the header value.  Default null.
     * 
     * @config dependency transport A Solar_Mail_Transport dependency injection, for use 
     *   with the send() method.  Default null, which means you need to send
     *   this message through a separate transport object.
     * 
     * @var array
     * 
     */
    protected $_Solar_Mail_Message = array(
        'boundary'    => null,
        'charset'     => 'utf-8',
        'encoding'    => '8bit',
        'crlf'        => "\r\n",
        'headers'     => null,
        'transport'   => null,
    );
    
    /**
     * 
     * Array of MIME part attachments for this message.
     * 
     * @var array
     * 
     */
    protected $_atch = array();
    
    /**
     * 
     * The MIME boundary string to separate the parts in this message.
     * 
     * @var string
     * 
     */
    protected $_boundary = null;
    
    /**
     * 
     * Encoding used for this message.
     * 
     * @var string
     * 
     */
    protected $_encoding = '8bit';
    
    /**
     * 
     * Character set used for this message.
     * 
     * @var string
     * 
     */
    protected $_charset = 'utf-8';
    
    /**
     * 
     * The line ending to use for this message.
     * 
     * @var string
     * 
     */
    protected $_crlf = "\r\n";
    
    /**
     * 
     * The "From:" address and display-name.
     * 
     * @var array
     * 
     */
    protected $_from = array('', '');
    
    /**
     * 
     * Array of custom additional headers.
     * 
     * @var array
     * 
     */
    protected $_headers = array();
    
    /**
     * 
     * The Solar_Mail_Message_Part for the "text/html" portion of the message.
     * 
     * @var Solar_Mail_Message_Part
     * 
     */
    protected $_html = null;
    
    /**
     * 
     * All recipient address and display-name values.
     * 
     * @var array
     * 
     */
    protected $_rcpt = array(
        'To'  => array(),
        'Cc'  => array(),
        'Bcc' => array(),
    );
    
    /**
     * 
     * The "Reply-To:" address and display-name.
     * 
     * @var array
     * 
     */
    protected $_reply_to = array('', '');
    
    /**
     * 
     * The "Return-Path" value.
     * 
     * @var string 
     * 
     */
    protected $_return_path = null;
    
    /**
     * 
     * The "Subject" value.
     * 
     * @var string
     * 
     */
    protected $_subject = null;
    
    /**
     * 
     * The Solar_Mail_Message_Part for the "text/plain" portion of the message.
     * 
     * @var Solar_Mail_Message_Part
     * 
     */
    protected $_text = null;
    
    /**
     * 
     * A Solar_Mail_Transport dependency object.
     * 
     * @var Solar_Mail_Transport_Adapter
     * 
     */
    protected $_transport = null;
    
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
        
        // custom boundary string
        if ($this->_config['boundary']) {
            $this->_boundary = $this->_config['boundary'];
        } else {
            $this->_boundary = '__' . hash('md5', uniqid());
        }
        
        // custom encoding
        if ($this->_config['encoding']) {
            $this->_encoding = $this->_config['encoding'];
        }
        
        // custom charset
        if ($this->_config['charset']) {
            $this->_charset = $this->_config['charset'];
        }
        
        // custom CRLF
        if ($this->_config['crlf']) {
            $this->_crlf = $this->_config['crlf'];
        }
        
        // custom headers
        if ($this->_config['headers']) {
            foreach ((array) $this->_config['headers'] as $label => $value) {
                $this->addHeader($label, $value);
            }
        }
        
        // do we have an injected transport?
        if ($this->_config['transport']) {
            $this->_transport = Solar::dependency(
                'Solar_Mail_Transport',
                $this->_config['transport']
            );
        }
    }
    
    /**
     * 
     * Sets the Solar_Mail_Transport dependency.
     * 
     * @param Solar_Mail_Transport_Adapter $transport The transport adapter.
     * 
     * @return Solar_Mail_Message
     * 
     */
    public function setTransport($transport)
    {
        $this->_transport = $transport;
        return $this;
    }
    
    /**
     * 
     * Sets the CRLF sequence for this message.
     * 
     * @param string $crlf The CRLF line-ending string.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setCrlf($crlf)
    {
        $this->_crlf = $crlf;
        return $this;
    }
    
    /**
     * 
     * Returns the CRLF sequence for this message.
     * 
     * @return string
     * 
     */
    public function getCrlf()
    {
        return $this->_crlf;
    }
    
    /**
     * 
     * Sets the encoding for this message.
     * 
     * @param string $encoding The encoding.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }
    
    /**
     * 
     * Returns the encoding for this message.
     * 
     * @return string
     * 
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }
    
    /**
     * 
     * Sets the character set for this message.
     * 
     * @param string $charset The character set.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setCharset($charset)
    {
        $this->_charset = $charset;
        return $this;
    }
    
    /**
     * 
     * Returns the character set for this message.
     * 
     * @return string
     * 
     */
    public function getCharset()
    {
        return $this->_charset;
    }
    
    /**
     * 
     * Sets the Return-Path header for an email.
     * 
     * @param string $addr The email address of the return-path.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setReturnPath($addr)
    {
        $this->_return_path = $addr;
        return $this;
    }
    
    /**
     * 
     * Returns the current Return-Path address for the email.
     * 
     * @return string
     * 
     */
    public function getReturnPath()
    {
        return $this->_return_path;
    }
    
    /**
     * 
     * Sets the "From:" (sender) on this message.
     * 
     * Strips CR/LF from the address and name to help avoid header injections.
     * 
     * @param string $addr The email address of the sender.
     * 
     * @param string $name The display-name for the sender, if any.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setFrom($addr, $name = '')
    {
        $this->_from = array(
            $addr,
            $name,
        );
        
        return $this;
    }
    
    /**
     * 
     * Returns the "From:" address for this message.
     * 
     * @return array
     * 
     */
    public function getFrom()
    {
        return $this->_from;
    }
    
    /**
     * 
     * Sets the "Reply-To:" on this message.
     * 
     * Strips CR/LF from the address and name to help avoid header injections.
     * 
     * @param string $addr The email address of the sender.
     * 
     * @param string $name The display-name for the sender, if any.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setReplyTo($addr, $name = '')
    {
        $this->_reply_to = array(
            $addr,
            $name,
        );
        
        return $this;
    }
    
    /**
     * 
     * Returns the "Reply-To:" address for this message.
     * 
     * @return array
     * 
     */
    public function getReplyTo()
    {
        return $this->_reply_to;
    }
    
    /**
     * 
     * Adds a "To:" address recipient.
     * 
     * @param string $addr The email address of the recipient.
     * 
     * @param string $name The display-name for the recipient, if any.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function addTo($addr, $name = null)
    {
        $this->_rcpt['To'][$addr] = $name;
        return $this;
    }
    
    /**
     * 
     * Sets the "To:" address recipient, removing previous "To:" recipients.
     * 
     * @param string $addr The email address of the recipient.
     * 
     * @param string $name The display-name for the recipient, if any.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setTo($addr, $name = null)
    {
        $this->_rcpt['To'] = array($addr => $name);
        return $this;
    }
    
    /**
     * 
     * Adds a "Cc:" address recipient.
     * 
     * @param string $addr The email address of the recipient.
     * 
     * @param string $name The display-name for the recipient, if any.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function addCc($addr, $name = null)
    {
        $this->_rcpt['Cc'][$addr] = $name;
        return $this;
    }
    
    /**
     * 
     * Sets the "Cc:" address recipient, removing previous "Cc:" recipients.
     * 
     * @param string $addr The email address of the recipient.
     * 
     * @param string $name The display-name for the recipient, if any.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setCc($addr, $name = null)
    {
        $this->_rcpt['Cc'] = array($addr => $name);
        return $this;
    }
    
    /**
     * 
     * Adds a "Bcc:" address recipient.
     * 
     * @param string $addr The email address of the recipient.
     * 
     * @param string $name The display-name for the recipient, if any.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function addBcc($addr, $name = null)
    {
        $this->_rcpt['Bcc'][$addr] = $name;
        return $this;
    }
    
    /**
     * 
     * Sets the "Bcc:" address recipient, removing previous "Bcc:" recipients.
     * 
     * @param string $addr The email address of the recipient.
     * 
     * @param string $name The display-name for the recipient, if any.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setBcc($addr, $name = null)
    {
        $this->_rcpt['Bcc'] = array($addr => $name);
        return $this;
    }
    
    /**
     * 
     * Returns an array of all recipient addresses.
     * 
     * @param string $type The recipient type to return: 'to', 'cc', or 'bcc'.
     * If empty (the default) will return all recipient addresses.
     * 
     * @return array A sequential array of recipient addresses.
     * 
     */
    public function getRcpt($type = null)
    {
        $type = ucfirst(trim($type));
        $list = array('To', 'Cc', 'Bcc');
        if ($type && in_array($type, $list)) {
            // just addresses of this type
            return array_keys($this->_rcpt[$type]);
        } elseif (! $type) {
            // no type, return all addresses
            return array_keys(array_merge(
                $this->_rcpt['To'],
                $this->_rcpt['Cc'],
                $this->_rcpt['Bcc']
            ));
        } else {
            // not a recognized type, so no addresses
            return array();
        }
    }

    /**
     * Resets all recipients in To, Cc and Bcc.
     * 
     * @return void
     */
    public function resetRcpt()
    {
        $this->_rcpt['To'] = array();
        $this->_rcpt['Cc'] = array();
        $this->_rcpt['Bcc'] = array();
    }
    
    /**
     * 
     * Sets the subject of the message.
     * 
     * @param string $subject The subject line for the message.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
        return $this;
    }
    
    /**
     * 
     * Returns the message subject.
     * 
     * @return string
     * 
     */
    public function getSubject()
    {
        return $this->_subject;
    }
    
    /**
     * 
     * Sets the part for the plain-text portion of this message.
     * 
     * @param string $text The plain-text message.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setText($text)
    {
        // create the part
        $part = Solar::factory('Solar_Mail_Message_Part');
        $part->setContent($text);
        $part->setCrlf($this->_crlf);
        $part->setType('text/plain');
        $part->setCharset($this->_charset);
        $part->setEncoding($this->_encoding);
        $part->setDisposition('inline');
        
        // keep it
        $this->_text = $part;
        
        // done!
        return $this;
    }
    
    /**
     * 
     * Returns the Solar_Mail_Message_Part for the plain-text portion.
     * 
     * @return Solar_Mail_Message_Part
     * 
     */
    public function getText()
    {
        return $this->_text;
    }
    
    /**
     * 
     * Sets the part for the HTML portion of this message.
     * 
     * @param string $html The HTML message.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function setHtml($html)
    {
        // create the part
        $part = Solar::factory('Solar_Mail_Message_Part');
        $part->setContent($html);
        $part->setCrlf($this->_crlf);
        $part->setType('text/html');
        $part->setCharset($this->_charset);
        $part->setEncoding($this->_encoding);
        $part->setDisposition('inline');
        
        // keep it
        $this->_html = $part;
        
        // done!
        return $this;
    }
    
    /**
     * 
     * Returns the Solar_Mail_Message_Part for the HTML portion.
     * 
     * @return Solar_Mail_Message_Part
     * 
     */
    public function getHtml()
    {
        return $this->_html;
    }
    
    /**
     * 
     * Attaches a Solar_Mail_Message_Part to the message.
     * 
     * @param Solar_Mail_Message_Part $part The part to add as an attachment.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function attachPart($part)
    {
        $this->_atch[] = $part;
        return $this;
    }
    
    /**
     * 
     * Attaches a file to the message.
     * 
     * @param string $file The absolute path and filename to attach.
     * 
     * @param string $type The Content-Type to use for the file. If empty,
     * uses the Solar_Mail_Message_Part default $type.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function attachFile($file, $type = null)
    {
        $part = Solar::factory('Solar_Mail_Message_Part');
        $part->setContent(file_get_contents($file));
        $part->setCrlf($this->_crlf);
        $part->setFilename(basename($file));
        
        if ($type) {
            $part->setType($type);
        }
        
        $this->_atch[] = $part;
        
        return $this;
    }
    
    /**
     * 
     * Adds a custom header to the message.
     * 
     * Canonicalizes the label, and strips CR/LF from the value, to help
     * prevent header injections.
     * 
     * If you need to wrap lines in the header value, don't worry; the header
     * encoding routine will wrap them for you.
     * 
     * @param string $label The header label.
     * 
     * @param string $value The header value.
     * 
     * @param bool $replace If true, resets all headers of the same label so
     * that this is the only value for that header.
     * 
     * @return Solar_Mail_Message This object.
     * 
     */
    public function addHeader($label, $value, $replace = true)
    {
        // sanitize the header label
        $label = Solar_Mime::headerLabel($label);
        
        // not allowed to add headers for these labels
        $list = array('to', 'cc', 'bcc', 'from', 'subject', 'return-path',
            'content-type', 'mime-version', 'content-transfer-encoding');
        if (in_array(strtolower($label), $list)) {
            throw $this->_exception('ERR_USE_OTHER_METHOD', array(
                'key' => $label,
            ));
        }
        
        // if replacing, or not already set, reset to a blank array
        if ($replace || empty($this->_headers[$label])) {
            $this->_headers[$label] = array();
        }
        
        // save the label and value
        $this->_headers[$label][] = $value;
        
        // done!
        return $this;
    }
    
    /**
     * 
     * Fetches all the headers of this message as a sequential array.
     * 
     * Each element is itself sequential array, where element 0 is the
     * header label, and element 1 is the encoded header value.
     * 
     * @return array
     * 
     */
    public function fetchHeaders()
    {
        // the array of headers to return
        $headers = array();
        
        // Return-Path: (alternatively, the From: address)
        if ($this->_return_path) {
            $headers[] = array('Return-Path', "<{$this->_return_path}>");
        } else {
            $headers[] = array('Return-Path', "<{$this->_from[0]}>");
        }
        
        // Date:
        $headers[] = array('Date', date('r'));

        // From:
        $value = "<{$this->_from[0]}>";
        if ($this->_from[1]) {
            $value = '"' . $this->_from[1] . '" ' . $value;
        }
        $headers[] = array("From", $value);
        
        // Reply-To: (optional)
        if ($this->_reply_to[0]) {
            $value = "<{$this->_reply_to[0]}>";
            if ($this->_reply_to[1]) {
                $value = '"' . $this->_reply_to[1] . '" ' . $value;
            }
            $headers[] = array("Reply-To", $value);
        }
        
        // To:, Cc:, Bcc:
        foreach ($this->_rcpt as $label => $rcpt) {
            foreach ($rcpt as $addr => $name) {
                $value = "<$addr>";
                if ($name) {
                    $value = '"' . $name . '" ' . $value;
                }
                $headers[] = array($label, $value);
            }
        }
        
        // Subject:
        $headers[] = array('Subject', $this->_subject);
        
        // Mime-Version:
        $headers[] = array('Mime-Version', '1.0');
        
        // Determine the content type and transfer encoding.
        // Default is no transfer encoding.
        $encoding = null;
        if ($this->_text && $this->_html && ! $this->_atch) {
            
            // both text and html, but no attachments: multipart/alternative
            $value = 'multipart/alternative; '
                   . 'charset="' . $this->_charset . '"; '
                   . $this->_crlf . " "
                   . 'boundary="' . $this->_boundary . '"';
            
        } elseif ($this->_atch) {
            
            // has attachments, use multipart/mixed
            $value = 'multipart/mixed; '
                   . 'charset="' . $this->_charset . '"; '
                   . $this->_crlf . " "
                   . 'boundary="' . $this->_boundary . '"';
            
        } elseif ($this->_html) {
            
            // no attachments, html only
            $value = 'text/html; '
                   . 'charset="' . $this->_charset . '"';
            
            // use the part's encoding
            $encoding = $this->_html->getEncoding();
            
        } elseif ($this->_text) {
            
            // no attachments, text only
            $value = 'text/plain; '
                   . 'charset="' . $this->_charset . '"';
            
            // use the part's encoding
            $encoding = $this->_text->getEncoding();
            
        } else {
            // final fallback
            $value = 'text/plain; '
                   . 'charset="' . $this->_charset . '"';
        }
        
        // Content-Type:
        $headers[] = array('Content-Type', $value);
        
        // Content-Transfer-Encoding:
        if ($encoding) {
            $headers[] = array('Content-Transfer-Encoding', $encoding);
        }
        
        // encode all the headers so far
        foreach ($headers as $key => $val) {
            // val[0] is the label, val[1] is the value
            $val[0] = Solar_Mime::headerLabel($val[0]);
            $headers[$key][1] = Solar_Mime::headerValue(
                $val[0],
                $val[1],
                $this->_charset,
                $this->_crlf
            );
        }
        
        // add and encode custom headers
        foreach ($this->_headers as $label => $list) {
            $label = Solar_Mime::headerLabel($label);
            foreach ($list as $value) {
                $headers[] = array(
                    $label,
                    Solar_Mime::headerValue(
                        $label,
                        $value,
                        $this->_charset,
                        $this->_crlf
                    ),
                );
            }
        }
        
        // done!
        return $headers;
    }
    
    /**
     * 
     * Fetches all the content parts of this message as a string.
     * 
     * See notes here:
     * <http://www.webcheatsheet.com/php/send_email_text_html_attachment.php#attachment>
     * 
     * If we have text *and* html, and attachments, the text and html are 
     * wrapped in their own multipart/alternative subpart, then the message as
     * a whole is built as multipart/mixed.
     * 
     * If we have text *or* html, and attachments, we build as multipart/mixed.
     * 
     * If we have text *or* html, no attachments, we build as a single part.
     * 
     * If we have only attachments, we build as a single part if there's one
     * attachment, or as multipart/mixed if there are more than one.
     * 
     * @return string
     * 
     */
    public function fetchContent()
    {
        // build a stack of all parts for the message: text, html, and
        // attachments
        $parts = array();
        
        // special treatment if we have text **and** html **and** attachments.
        if ($this->_text && $this->_html && $this->_atch) {
            
            // create a separate part to hold only the text and html as
            // alternatives, to keep the attachments separate.  otherwise
            // the text, html, and atches *all* show up in the email inline,
            // when we just want *either* the text *or* the html to show.
            // 
            // @todo this is kind of dumb; we should make the Message_Part be
            // smart enough to handle sub-parts and set up its own boundaries.
            $boundary = '____' . hash('md5', $this->_boundary . uniqid());
            $alt = Solar::factory('Solar_Mail_Message_Part');
            $alt->setCrlf($this->_crlf);
            $alt->setEncoding('7bit');
            $alt->setDisposition(null);
            $alt->setType('multipart/alternative');
            $alt->setCharset($this->_charset);
            $alt->setBoundary($boundary);
            $alt->setContent(ltrim($this->_boundarySep($boundary))
                           . $this->_text->fetch()
                           . $this->_boundarySep($boundary)
                           . $this->_html->fetch()
                           . $this->_boundaryEnd($boundary));
            
            // add the combined text/html alternative part
            $parts[] = $alt;
            
        } else {
            // we have *either* text *or* html, and possibly some attachments.
            // no need to wrap the main-message part, just show it inline.
            //
            // add the text part, if it exists
            if ($this->_text) {
                $parts[] = $this->_text;
            }
        
            // add the html part, if it exists
            if ($this->_html) {
                $parts[] = $this->_html;
            }
        }
        
        // add all the attachments
        $parts = array_merge($parts, $this->_atch);
        
        // we need at least *one* part to send
        if (! $parts) {
            throw $this->_exception('ERR_NO_PARTS');
        }
        
        // is this multi-part?
        if (count($parts) == 1) {
            // no, so it's easy to build
            $content = $parts[0]->fetchContent();
        } else {
            
            // multiple parts.
            // add a warning message ...
            $content = 'This is a message in MIME format. If you see this, '
                     . $this->_crlf
                     . 'your mail reader does not support the MIME format.'
                     . $this->_crlf;
            
            // then each of the parts with a boundary separator
            foreach ($parts as $part) {
                $content .= $this->_boundarySep()
                          . $part->fetch();
            }
        
            // add a boundary ending, and we're done
            $content .= $this->_boundaryEnd();
        }
        
        return trim($content);
    }
    
    /**
     * 
     * If a transport dependency has been injected, use it to send this email.
     * 
     * @return bool True on success, false on failure.
     * 
     * @throws Solar_Mail_Message_Exception_NoTransport
     * 
     */
    public function send()
    {
        if (! $this->_transport) {
            throw $this->_exception('ERR_NO_TRANSPORT');
        }
        
        return $this->_transport->send($this);
    }
    
    /**
     * 
     * Returns a boundary-line separator.
     * 
     * @param string $str The boundary text; if empty, uses $this->_boundary.
     * 
     * @return string
     * 
     */
    protected function _boundarySep($str = null)
    {
        if (! $str) {
            $str = $this->_boundary;
        }
        return "{$this->_crlf}--{$str}{$this->_crlf}";
    }
    
    /**
     * 
     * Returns a boundary-line ending.
     * 
     * @param string $str The boundary text; if empty, uses $this->_boundary.
     * 
     * @return string
     * 
     */
    protected function _boundaryEnd($str = null)
    {
        if (! $str) {
            $str = $this->_boundary;
        }
        return "{$this->_crlf}--{$str}--{$this->_crlf}";
    }
}
