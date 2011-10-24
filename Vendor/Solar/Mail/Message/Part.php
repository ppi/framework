<?php
/**
 * 
 * Represents one MIME part of a Solar_Mail_Message.
 * 
 * Refactored and modified from Zend_Mail_Message and related classes.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Part.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
class Solar_Mail_Message_Part extends Solar_Base
{
    /**
     * 
     * The character set for this part.
     * 
     * @var string
     * 
     */
    protected $_charset = 'utf-8';
    
    /**
     * 
     * The CRLF sequence for this part.
     * 
     * @var string
     * 
     */
    protected $_crlf = "\r\n";
    
    /**
     * 
     * The Content-Disposition for this part.
     * 
     * Typically 'inline' or 'attachment'.
     * 
     * @var string
     * 
     */
    protected $_disposition = 'attachment';
    
    /**
     * 
     * The Content-Transfer-Encoding for this part.
     * 
     * @var string
     * 
     */
    protected $_encoding = 'base64';
    
    /**
     * 
     * When the part represents a file, use this as the filename.
     * 
     * @var string
     * 
     */
    protected $_filename = null;
    
    /**
     * 
     * The Content-Type for this part.
     * 
     * @var string
     * 
     */
    protected $_type = 'application/octet-stream';
    
    /**
     * 
     * The body content for this part.
     * 
     * @var string
     * 
     */
    protected $_content = null;
    
    /**
     * 
     * The boundary string to use in this part.
     * 
     * @var string
     * 
     */
    protected $_boundary = null;
    
    /**
     * 
     * Array of custom headers for this part.
     * 
     * @var array
     * 
     */
    protected $_headers = array();
    
    /**
     * 
     * Sets the CRLF sequence for this part.
     * 
     * @param string $crlf The CRLF line-ending string.
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
     * Returns the CRLF sequence for this part.
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
     * Sets the Content-Type for this part.
     * 
     * @param string $type The content type, e.g. 'image/jpeg'.
     * 
     * @return void
     * 
     */
    public function setType($type)
    {
        $this->_type = $type;
    }
    
    /**
     * 
     * Returns the Content-Type for this part.
     * 
     * @return string
     * 
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * 
     * Sets the Content-Type character set for this part.
     * 
     * @param string $charset The character set.
     * 
     * @return void
     * 
     */
    public function setCharset($charset)
    {
        $this->_charset = $charset;
    }
    
    /**
     * 
     * Returns the Content-Type character set for this part.
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
     * Sets the Content-Type boundary for this part.
     * 
     * @param string $boundary The boundary string.
     * 
     * @return void
     * 
     */
    public function setBoundary($boundary)
    {
        $this->_boundary = $boundary;
    }
    
    /**
     * 
     * Returns the Content-Type boundary for this part.
     * 
     * @return string
     * 
     */
    public function getBoundary()
    {
        return $this->_boundary;
    }
    
    /**
     * 
     * Sets the Content-Disposition for this part.
     * 
     * @param string $disposition Typically 'inline' or 'attachment'.
     * 
     * @return void
     * 
     */
    public function setDisposition($disposition)
    {
        $this->_disposition = $disposition;
    }
    
    /**
     * 
     * Returns the Content-Disposition for this part.
     * 
     * @return string
     * 
     */
    public function getDisposition()
    {
        return $this->_disposition;
    }
    
    /**
     * 
     * Sets the Content-Disposition filename for this part.
     * 
     * @param string $filename The file name.
     * 
     * @return void
     * 
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
    }
    
    /**
     * 
     * Returns the Content-Disposition filename for this part.
     * 
     * @return string
     * 
     */
    public function getFilename()
    {
        return $this->_filename;
    }
    
    /**
     * 
     * Sets the Content-Transfer-Encoding for this part.
     * 
     * @param string $encoding Typically 'base64' or 'quoted-printable'.
     * 
     * @return void
     * 
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
    }
    
    /**
     * 
     * Returns the Content-Transfer-Encoding for this part.
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
     * Sets the body content for this part.
     * 
     * @param string $content The body content.
     * 
     * @return void
     * 
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }
    
    /**
     * 
     * Returns the body content for this part.
     * 
     * @return string
     * 
     */
    public function getContent()
    {
        return $this->_content;
    }
    
    /**
     * 
     * Sets (or resets) one header in the part.
     * 
     * Canonicalizes the label, and strips CR/LF from the value, to help
     * prevent header injections.
     * 
     * You can only set one label to one value; you can't have multiple
     * repetitions of the same label to get multiple values.
     * 
     * @param string $label The header label.
     * 
     * @param string $value The header value.
     * 
     * @return void
     * 
     */
    public function setHeader($label, $value)
    {
        // sanitize the header label
        $label = Solar_Mime::headerLabel($label);
        
        // not allowed to add headers for these labels
        $list = array('content-type', 'content-transfer-encoding',
            'content-disposition');
        if (in_array(strtolower($label), $list)) {
            throw $this->_exception('ERR_USE_OTHER_METHOD', array(
                'key' => $label,
            ));
        }
        
        // save the label and value
        $this->_headers[$label] = $value;
    }
    
    /**
     * 
     * Returns the headers, a newline, and the content, all as a single block.
     * 
     * @return string
     * 
     */
    public function fetch()
    {
        return $this->fetchHeaders()
             . $this->_crlf
             . $this->fetchContent();
    }
    
    /**
     * 
     * Returns all the headers as a string.
     * 
     * @return string
     * 
     */
    public function fetchHeaders()
    {
        // start with all the "custom" headers.
        // we will apply header-value encoding at the end.
        $headers = $this->_headers;
        
        // Content-Type:
        $content_type = $this->_type;
        
        if ($this->_charset) {
            $content_type .= '; charset="' . $this->_charset . '"';
        }
        
        if ($this->_boundary) {
            $content_type .= ';' . $this->_crlf
                           . ' boundary="' . $this->_boundary . '"';
        }
        
        $headers['Content-Type'] = $content_type;
        
        // Content-Disposition:
        if ($this->_disposition) {
            $disposition = $this->_disposition;
            if ($this->_filename) {
                $disposition .= '; filename="' . $this->_filename . '"';
            }
            $headers['Content-Disposition'] = $disposition;
        }
        
        // Content-Transfer-Encoding:
        if ($this->_encoding) {
            $headers['Content-Transfer-Encoding'] = $this->_encoding;
        }
        
        // now loop through all the headers and build the header block,
        // using header-value encoding as we go.
        $output = '';
        foreach ($headers as $label => $value) {
            $label = Solar_Mime::headerLabel($label);
            $value = Solar_Mime::headerValue(
                $label,
                $value,
                $this->_charset,
                $this->_crlf
            );
            $output .= "$label: $value{$this->_crlf}";
        }
        
        return $output;
    }
    
    /**
     * 
     * Returns the body content of this part with the proper encoding.
     * 
     * @return string
     * 
     */
    public function fetchContent()
    {
        $content = Solar_Mime::encode(
            $this->_encoding,
            $this->_content,
            $this->_crlf
        );
        
        return $content;
    }
}
