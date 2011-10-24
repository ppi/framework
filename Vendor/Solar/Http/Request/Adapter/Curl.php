<?php
/**
 * 
 * Uses cURL for a standalone HTTP request.
 * 
 * @category Solar
 * 
 * @package Solar_Http
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Curl.php 4405 2010-02-18 04:27:25Z pmjones $
 * 
 */
class Solar_Http_Request_Adapter_Curl extends Solar_Http_Request_Adapter
{


    /**
     * 
     * Throws an exception if the curl extension isn't loaded
     * 
     * @return void
     * 
     * @author Bahtiar Gadimov <bahtiar@gadimov.de>
     * 
     */
    public function _preConfig()
    {
        parent::_preConfig();
        if (! extension_loaded('curl')) {
            throw $this->_exception('ERR_EXTENSION_NOT_LOADED', array(
                'extension' => 'curl',
            ));
        }
    }

    /**
     * 
     * Support method to make the request, then return headers and content.
     * 
     * @param string $uri The URI get a response from.
     * 
     * @param array $headers A sequential array of header lines for the request.
     * 
     * @param string $content A string of content for the request.
     * 
     * @return array A sequential array where element 0 is a sequential array of
     * header lines, and element 1 is the body content.
     * 
     * @todo Implement an exception for timeouts.
     * 
     */
    protected function _fetch($uri, $headers, $content)
    {
        // prepare the connection and get the response
        $ch = $this->_prepareCurlHandle($uri, $headers, $content);
        $response = curl_exec($ch);
        
        // did we hit any errors?
        if ($response === false || $response === null) {
            throw $this->_exception('ERR_CONNECTION_FAILED', array(
                'code' => curl_errno($ch),
                'text' => curl_error($ch),
            ));
        }
        
        // get the metadata and close the connection
        $meta = curl_getinfo($ch);
        curl_close($ch);
        
        // get the header lines from the response
        $headers = explode(
            "\r\n",
            substr($response, 0, $meta['header_size'])
        );
        
        // get the content portion from the response
        $content = substr($response, $meta['header_size']);
        
        // done!
        return array($headers, $content);
    }
    
    /**
     * 
     * Builds a cURL resource handle for _fetch() from property options.
     * 
     * @param Solar_Uri $uri The URI get a response from.
     * 
     * @param array $headers A sequential array of headers.
     * 
     * @param string $content The body content.
     * 
     * @return resource The cURL resource handle.
     * 
     * @see <http://php.net/curl>
     * 
     * @todo HTTP Authentication
     * 
     */
    protected function _prepareCurlHandle($uri, $headers, $content)
    {
        /**
         * the basic handle and the url for it
         */
        $ch = curl_init($uri);
        
        /**
         * request method
         */
        switch ($this->_method) {
        case Solar_Http_Request::METHOD_GET:
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case Solar_Http_Request::METHOD_POST:
            curl_setopt($ch, CURLOPT_POST, true);
            break;
        case Solar_Http_Request::METHOD_PUT:
            curl_setopt($ch, CURLOPT_PUT, true);
            break;
        case Solar_Http_Request::METHOD_HEAD:
            curl_setopt($ch, CURLOPT_HEAD, true);
            break;
        default:
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_method);
            break;
        }
        
        /**
         * headers
         */
        // HTTP version
        switch ($this->_version) {
        case '1.0':
            // HTTP/1.0
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            break;
        case '1.1':
            // HTTP/1.1
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            break;
        default:
            // let curl decide
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE);
            break;
        }
        
        // set specialized headers and retain all others
        foreach ($headers as $i => $header) {
            $pos = strpos($header, ':');
            $label = substr($header, 0, $pos);
            $value = substr($header, $pos + 2);
            switch ($label) {
            case 'Cookie':
                curl_setopt($ch, CURLOPT_COOKIE, $value);
                unset($headers[$i]);
                break;
            case 'User-Agent':
                curl_setopt($ch, CURLOPT_USERAGENT, $value);
                unset($headers[$i]);
                break;
            case 'Referer':
                curl_setopt($ch, CURLOPT_REFERER, $value);
                unset($headers[$i]);
                break;
            }
        }
        
        // all remaining headers
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        /**
         * content
         */
        
        // only send content if we're POST or PUT
        $send_content = $this->_method == Solar_Http_Request::METHOD_POST
                     || $this->_method == Solar_Http_Request::METHOD_PUT;
        
        if ($send_content && ! empty($content)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }
        
        /**
         * curl behaviors
         */
        // convert Unix newlines to CRLF newlines on transfers.
        curl_setopt($ch, CURLOPT_CRLF, true);
        
        // automatically set the Referer: field in requests where it
        // follows a Location: redirect.
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        
        // follow any "Location: " header that the server sends as
        // part of the HTTP header (note this is recursive, PHP will follow
        // as many "Location: " headers that it is sent, unless
        // CURLOPT_MAXREDIRS is set).
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // include the headers in the response
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        // return the transfer as a string instead of printing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // property-name => curlopt-constant
        $var_opt = array(
            '_proxy'         => CURLOPT_PROXY,
            '_max_redirects' => CURLOPT_MAXREDIRS,
            '_timeout'       => CURLOPT_TIMEOUT,
        );
        
        // set other behaviors
        foreach ($var_opt as $var => $opt) {
            // use this comparison so boolean false and integer zero values
            // are honored
            if ($this->$var !== null) {
                curl_setopt($ch, $opt, $this->$var);
            }
        }
        
        /**
         * secure transport behaviors
         */
        $is_secure = strtolower(substr($uri, 0, 5)) == 'https' ||
                     strtolower(substr($uri, 0, 3)) == 'ssl';
        
        if ($is_secure) {
            // property-name => curlopt-constant
            $var_opt = array(
                '_ssl_verify_peer' => CURLOPT_SSL_VERIFYPEER,
                '_ssl_cafile'      => CURLOPT_CAINFO,
                '_ssl_capath'      => CURLOPT_CAPATH,
                '_ssl_local_cert'  => CURLOPT_SSLCERT,
                '_ssl_passphrase'  => CURLOPT_SSLCERTPASSWD,
            );
            
            // set other behaviors
            foreach ($var_opt as $var => $opt) {
                // use this comparison so boolean false and integer zero
                // values are honored
                if ($this->$var !== null) {
                    curl_setopt($ch, $opt, $this->$var);
                }
            }
        }
        
        /**
         * Done
         */
        return $ch;
    }
}
