<?php
/**
 * 
 * Wraps an HTTP stream to act as a standalone HTTP request.
 * 
 * N.b.: With this adapter, under PHP 5.2.x and earlier, if the request gets
 * back a response with a "4xx" (failure) status, the response content will
 * **not** be included.  This is a known issue in the way HTTP streams work
 * under PHP 5.2.x and earlier.  PHP 5.3 and later do not have this problem.
 * 
 * If you are on PHP 5.2.x or earlier and need the response content on when
 * a failure status code is returned, please use the curl-based adapter.
 * 
 * Cf. <http://php.net/manual/en/context.http.php>
 * 
 * @category Solar
 * 
 * @package Solar_Http
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Stream.php 4488 2010-03-02 15:16:57Z pmjones $
 * 
 */
class Solar_Http_Request_Adapter_Stream extends Solar_Http_Request_Adapter
{
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
     */
    protected function _fetch($uri, $headers, $content)
    {
        // prepare the stream context
        $context = $this->_prepareContext($headers, $content);
        
        // connect to the uri (suppress errors and deal with them later)
        $stream = @fopen($uri, 'r', false, $context);
        
        // did we hit any errors?
        if ($stream === false) {
            // the $http_response_header variable is automatically created
            // by the streams extension
            if (! empty($http_response_header)) {
                // server responded, but there's no content
                return array($http_response_header, null);
            } else {
                // no server response, must be some other error
                $info = error_get_last();
                throw $this->_exception('ERR_CONNECTION_FAILED', $info);
            }
        }
        
        // get the response message
        $content = stream_get_contents($stream);
        $meta = stream_get_meta_data($stream);
        fclose($stream);
        
        // did it time out?
        if ($meta['timed_out']) {
            throw $this->_exception('ERR_CONNECTION_TIMEOUT', array(
                'uri'     => $uri,
                'meta'    => $meta,
                'content' => $content,
            ));
        }
        
        // if php was compiled with --with-curlwrappers, then the field
        // 'wrapper_data' contains two arrays, one with headers and another
        // with readbuf.  cf. <http://darkain.livejournal.com/492112.html>
        $with_curlwrappers = isset($meta['wrapper_type'])
                          && strtolower($meta['wrapper_type']) == 'curl';
                         
        // return headers and content.
        if ($with_curlwrappers) {
            // compiled --with-curlwrappers
            return array($meta['wrapper_data']['headers'], $content);
        } else {
            // the "normal" case
            return array($meta['wrapper_data'], $content);
        }
    }
    
    /**
     * 
     * Builds the stream context from property options for _fetch().
     * 
     * @param array $headers A sequential array of headers.
     * 
     * @param string $content The body content.
     * 
     * @return resource A stream context resource for "http" and "https"
     * protocols.
     * 
     * @see <http://php.net/manual/en/wrappers.http.php>
     * 
     */
    protected function _prepareContext($headers, $content)
    {
        /**
         * HTTP context
         */
        
        // http options
        $http = array();
        
        // method
        if ($this->_method != Solar_Http_Request::METHOD_GET) {
            $http['method'] = $this->_method;
        }
        
        // send headers?
        if ($headers) {
            $http['header'] = implode("\r\n", $headers);
        }
        
        // send content?
        if ($content) {
            $http['content'] = $content;
        }
        
        // http: property-name => context-key
        $var_key = array(
            '_proxy'            => 'proxy',
            '_max_redirects'    => 'max_redirects',
            '_version'          => 'version',
            '_timeout'          => 'timeout',
        );
        
        // set other options
        foreach ($var_key as $var => $key) {
            // use this comparison so boolean false and integer zero values
            // are honored
            if ($this->$var !== null) {
                $http[$key] = $this->$var;
            }
        }
        
        // before php 5.3, a failure status in the response header means the
        // stream **will not** fetch the content of the response, even if
        // content was sent. in php 5.3 and later, the following context
        // setting will fetch the content regardless of failure code.
        $http['ignore_errors'] = true;
        
        /**
         * HTTPS context
         */
        
        // base on http options
        $https = $http;
        
        // property-name => context-key
        $var_key = array(
            '_ssl_verify_peer'       => 'verify_peer',
            '_ssl_cafile'            => 'cafile',
            '_ssl_capath'            => 'capath',
            '_ssl_local_cert'        => 'local_cert',
            '_ssl_passphrase'        => 'passphrase',
        );
        
        // set other options
        foreach ($var_key as $var => $key) {
            if ($this->$var) {
                $https[$key] = $this->$var;
            }
        }
        
        /**
         * Done
         */
        return stream_context_create(array(
            'http'  => $http,
            'https' => $https,
        ));
    }
}
