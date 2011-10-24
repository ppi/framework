<?php
/**
 * 
 * Generic HTTP response object for sending headers, cookies, and content.
 * 
 * This is a fluent class; the set() methods can be chained together like so:
 * 
 * {{code: php
 *     $response = Solar::factory('Solar_Http_Response');
 *     $response->setStatusCode(404)
 *              ->setHeader('X-Foo', 'Bar')
 *              ->setCookie('baz', 'dib')
 *              ->setContent('Page not found.')
 *              ->display();
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Http
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Response.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 * @todo Add charset param so that headers get sent with right encoding?
 * 
 */
class Solar_Http_Response extends Solar_Base
{
    /**
     * 
     * The response body content.
     * 
     * @var string
     * 
     */
    public $content = null;
    
    /**
     * 
     * All headers to send at display() time.
     * 
     * @var array
     * 
     */
    protected $_headers = array();
    
    /**
     * 
     * All cookies to send at display() time.
     * 
     * @var array
     * 
     */
    protected $_cookies = array();
    
    /**
     * 
     * Whether or not cookies should default being sent by HTTP only.
     * 
     * @var bool
     * 
     */
    protected $_cookies_httponly = true;
    
    /**
     * 
     * The HTTP response status code.
     * 
     * @var int
     * 
     */
    protected $_status_code = 200;
    
    /**
     * 
     * The HTTP response status text.
     * 
     * @var int
     * 
     */
    protected $_status_text = null;
    
    /**
     * 
     * The HTTP version to send as.
     * 
     * @var string
     * 
     */
    protected $_version = '1.1';
    
    /**
     * 
     * Should the response disable browser caching?
     * 
     * When true, the response will send these headers:
     * 
     * {{code:
     *     Pragma: no-cache
     *     Cache-Control: no-store, no-cache, must-revalidate
     *     Cache-Control: post-check=0, pre-check=0
     *     Expires: 1
     * }}
     * 
     * @var bool
     * 
     * @see setNoCache()
     * 
     * @see redirectNoCache()
     * 
     */
    protected $_no_cache = false;
    
    /**
     * 
     * Sends all headers and cookies, then returns the body.
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        $this->_sendHeaders();
        
        // cast to string to avoid fatal error when returning nulls
        return (string) $this->content;
    }
    
    /**
     * 
     * Sets the HTTP version to '1.0' or '1.1'.
     * 
     * @param string $version The HTTP version to use for this response.
     * 
     * @return Solar_Http_Response This response object.
     * 
     * @throws Solar_Http_Response_Exception_HttpVersion when the version number
     * is not '1.0' or '1.1'.
     * 
     */
    public function setVersion($version)
    {
        $version = trim($version);
        if ($version != '1.0' && $version != '1.1') {
            throw $this->_exception('ERR_HTTP_VERSION', array(
                'version' => $version
            ));
        } else {
            $this->_version = $version;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Returns the HTTP version for this response.
     * 
     * @return string
     * 
     */
    public function getVersion()
    {
        return $this->_version;
    }
    
    /**
     * 
     * Sets the HTTP response status code.
     * 
     * Automatically resets the status text to the default for this code.
     * 
     * @param int $code An HTTP status code, such as 200, 302, 404, etc.
     * 
     * @return Solar_Http_Response This response object.
     * 
     */
    public function setStatusCode($code)
    {
        $code = (int) $code;
        if ($code < 100 || $code > 599) {
            throw $this->_exception('ERR_STATUS_CODE', array(
                'code' => $code,
            ));
        }
        
        $this->_status_code = $code;
        $this->setStatusText(null);
        
        // done
        return $this;
    }
    
    /**
     * 
     * Sets the HTTP response status text.
     * 
     * @param string $text The status text; if empty, will set the text to the
     * default for the current status code.
     * 
     * @return Solar_Http_Response This response object.
     * 
     */
    public function setStatusText($text)
    {
        // trim and remove newlines from custom text
        $text = trim(str_replace(array("\r", "\n"), '', $text));
        if ($text) {
            // use custom text
            $this->_status_text = $text;
        } else {
            // use default text for status code
            $this->_status_text = $this->locale("STATUS_{$this->_status_code}");
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Returns the current status code.
     * 
     * @return int
     * 
     */
    public function getStatusCode()
    {
        return $this->_status_code;
    }
    
    /**
     * 
     * Returns the current status text.
     * 
     * @return string
     * 
     */
    public function getStatusText()
    {
        return $this->_status_text;
    }
    
    /**
     * 
     * By default, should cookies be sent by HTTP only?
     * 
     * @param bool $flag True to send by HTTP only, false to send by any
     * method.
     * 
     * @return Solar_Http_Response This response object.
     * 
     */
    public function setCookiesHttponly($flag)
    {
        $this->_cookies_httponly = (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * Sets a header value in $this->_headers; will be sent to the client at
     * display() time.
     * 
     * This method will not set 'HTTP' headers for response status codes; use
     * the [[Solar_Http_Response::setStatusCode() | ]] and 
     * [[Solar_Http_Response::setStatusText() | ]] methods instead.
     * 
     * @param string $key The header label, such as "Content-Type".
     * 
     * @param string $val The value for the header.
     * 
     * @param bool $replace This header value should replace any previous
     * values of the same key.  When false, the same header key is sent
     * multiple times with the different values.
     * 
     * @return Solar_Http_Response This response object.
     * 
     * @see [[php::header() | ]]
     * 
     */
    public function setHeader($key, $val, $replace = true)
    {
        // normalize the header key
        $key = Solar_Mime::headerLabel($key);
        
        // disallow HTTP header
        $lower = strtolower($key);
        if ($lower == 'http') {
            throw $this->_exception('ERR_USE_OTHER_METHOD', array(
                'name' => $key,
            ));
        }
        
        // add the header to the list
        if ($replace || empty($this->_headers[$key])) {
            // replacement, or first instance of the key
            $this->_headers[$key] = $val;
        } else {
            // second or later instance of the key
            settype($this->_headers[$key], 'array');
            $this->_headers[$key][] = $val;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Returns the value of a single header.
     * 
     * @param string $key The header name.
     * 
     * @return string|array A string if the header has only one value, or an
     * array if the header has multiple values, or null if the header does not
     * exist.
     * 
     */
    public function getHeader($key)
    {
        // normalize the header key
        $key = Solar_Mime::headerLabel($key);
        
        // get the value
        $headers = $this->getHeaders();
        if (array_key_exists($key, $headers)) {
            return $headers[$key];
        }
    }
    
    /**
     * 
     * Returns the array of all headers to be sent with the response.
     * 
     * @return array
     * 
     */
    public function getHeaders()
    {
        $headers = $this->_headers;
        
        if ($this->_no_cache) {
            $headers['Pragma'] = 'no-cache';
            $headers['Cache-Control'] = array(
                'no-store, no-cache, must-revalidate',
                'post-check=0, pre-check=0',
            );
            $headers['Expires'] = '1';
        }
        
        return $headers;
    }
    
    /**
     * 
     * Sets the content of the response.
     * 
     * While this is not strictly necessary (because $content is public), it
     * does serve to complete the fluency of this class.
     * 
     * @param string $content The body content of the response.
     * 
     * @return Solar_Http_Response This response object.
     * 
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * 
     * Gets the body content of the response.
     * 
     * While this is not strictly necessary (because $content is public), it
     * serves to complete the get/set method list.
     * 
     * @return string The body content of the response.
     * 
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * 
     * Sets a cookie value in $this->_cookies; will be sent to the client at
     * display() time.
     * 
     * @param string $name The name of the cookie.
     * 
     * @param string $value The value of the cookie.
     * 
     * @param int|string $expires The Unix timestamp after which the cookie
     * expires.  If non-numeric, the method uses strtotime() on the value.
     * 
     * @param string $path The path on the server in which the cookie will be
     * available on.
     * 
     * @param string $domain The domain that the cookie is available on.
     * 
     * @param bool $secure Indicates that the cookie should only be
     * transmitted over a secure HTTPS connection.
     * 
     * @param bool $httponly When true, the cookie will be made accessible
     * only through the HTTP protocol. This means that the cookie won't be
     * accessible by scripting languages, such as JavaScript.
     * 
     * @return Solar_Http_Response This response object.
     * 
     * @see [[php::setcookie() | ]]
     * 
     */
    public function setCookie($name, $value = '', $expires = 0,
        $path = '', $domain = '', $secure = false, $httponly = null)
    {
        // store the cookie value
        $this->_cookies[$name] = array(
            'value'     => $value,
            'expires'   => $expires,
            'path'      => $path,
            'domain'    => $domain,
            'secure'    => $secure,
            'httponly'  => $httponly,
        );
        
        // done
        return $this;
    }
    
    /**
     * 
     * Returns the value and options for a single cookie.
     * 
     * @param string $key The cookie name.
     * 
     * @return array An array of the value and options for the cookie.
     * 
     */
    public function getCookie($key)
    {
        if (! empty($this->_cookie[$key])) {
            return $this->_cookie[$key];
        }
    }
    
    /**
     * 
     * Returns the array of cookies that will be set by the response.
     * 
     * @return array
     * 
     */
    public function getCookies()
    {
        return $this->_cookies;
    }
    
    /**
     * 
     * Should the response disable HTTP caching?
     * 
     * When true, the response will send these headers:
     * 
     * {{code:
     *     Pragma: no-cache
     *     Cache-Control: no-store, no-cache, must-revalidate
     *     Cache-Control: post-check=0, pre-check=0
     *     Expires: 1
     * }}
     * 
     * @param bool $flag When true, disable browser caching.
     * 
     * @see setNoCache()
     * 
     * @see redirectNoCache()
     * 
     * @return void
     * 
     */
    public function setNoCache($flag = true)
    {
        $this->_no_cache = (bool) $flag;
    }
    
    /**
     * 
     * Sends all headers and cookies, then prints the response content.
     * 
     * @return void
     * 
     */
    public function display()
    {
        $this->_sendHeaders();
        echo $this->content;
    }
    
    /**
     * 
     * Issues an immediate "Location" redirect.  Use instead of display()
     * to perform a redirect.  You should die() or exit() after calling this.
     * 
     * @param Solar_Uri_Action|string $spec The URI to redirect to.
     * 
     * @param int|string $code The HTTP status code to redirect with; default
     * is '302 Found'.
     * 
     * @return void
     * 
     */
    public function redirect($spec, $code = '302')
    {
        if ($spec instanceof Solar_Uri_Action) {
            $href = $spec->get(true);
        } elseif (strpos($spec, '://') !== false) {
            // external link, protect against header injections
            $href = str_replace(array("\r", "\n"), '', $spec);
        } else {
            $uri = Solar::factory('Solar_Uri_Action');
            $href = $uri->quick($spec, true);
        }
        
        // kill off all output buffers
        while(@ob_end_clean());
        
        // make sure there's actually an href
        $href = trim($href);
        if (! $href) {
            throw $this->_exception('ERR_REDIRECT_NO_URI');
        }
        
        // set the status code
        $this->setStatusCode($code);
        
        // set the redirect location 
        $this->setHeader('Location', $href);
        
        // clear the response body
        $this->content = null;
        
        // is this a GET-after-(POST|PUT) redirect?
        $request = Solar_Registry::get('request');
        if ($request->isPost() || $request->isPut()) {
            
            // tell the next request object that it's a get-after-post
            $session = Solar::factory('Solar_Session', array(
                'class' => get_class($request),
            ));
            $session->setFlash('is_gap', true);
        }
        
        // save the session
        session_write_close();
        
        // send the response directly -- done.
        $this->display();
    }
    
    /**
     * 
     * Redirects to another page and action after disabling HTTP caching.
     * This effectively implements the "POST-Redirect-GET" pattern (also known
     * as the "GET after POST").
     * 
     * The redirect() method is often called after a successful POST
     * operation, to show a "success" or "edit" page. In such cases, clicking
     * clicking "back" or "reload" will generate a warning in the
     * browser allowing for a possible re-POST if the user clicks OK.
     * Typically this is not what you want.
     * 
     * In those cases, use redirectNoCache() to turn off HTTP caching, so
     * that the re-POST warning does not occur.
     * 
     * This method calls [[Solar_Http_Response::setNoCache() | ]] to disable
     * caching.
     * 
     * @param Solar_Uri_Action|string $spec The URI to redirect to.
     * 
     * @param int|string $code The HTTP status code to redirect with; default
     * is '303 See Other'.
     * 
     * @return void
     * 
     * @see <http://www.theserverside.com/tt/articles/article.tss?l=RedirectAfterPost>
     * 
     * @see setNoCache()
     * 
     */
    public function redirectNoCache($spec, $code = '303')
    {
        $this->setNoCache();
        return $this->redirect($spec, $code);
    }
    
    /**
     * 
     * Sends all headers and cookies.
     * 
     * @return void
     * 
     * @throws Solar_Http_Response_Exception_HeadersSent if headers have
     * already been sent.
     * 
     */
    protected function _sendHeaders()
    {
        // build the full status header string.  the values have already been
        // sanitized by setStatus() and setStatusText().
        $status = "HTTP/{$this->_version} {$this->_status_code}";
        if ($this->_status_text) {
            $status .= " {$this->_status_text}";
        }
        
        // send the status header
        header($status, true, $this->_status_code);
        
        // send each of the remaining headers
        foreach ($this->getHeaders() as $key => $list) {
            
            // sanitize and skip empty keys
            $key = Solar_Mime::headerLabel($key);
            if (! $key) {
                continue;
            }
            
            // send each value for the header
            foreach ((array) $list as $val) {
                // we don't need full MIME escaping here, just sanitize the
                //  value by stripping CR and LF chars
                $val = str_replace(array("\r", "\n"), '', $val);
                header("$key: $val");
            }
        }
        
        // send each of the cookies
        foreach ($this->_cookies as $key => $val) {
            
            // was httponly set for this cookie?  if not,
            // use the default.
            $httponly = ($val['httponly'] === null)
                ? $this->_cookies_httponly
                : (bool) $val['httponly'];
            
            // try to allow for times not in unix-timestamp format
            if (! is_numeric($val['expires'])) {
                $val['expires'] = strtotime($val['expires']);
            }
            
            // actually set the cookie
            setcookie(
                $key,
                $val['value'],
                (int) $val['expires'],
                $val['path'],
                $val['domain'],
                (bool) $val['secure'],
                (bool) $httponly
            );
        }
    }
    
    /**
     * 
     * Dumps the values of this object.
     * 
     * @param mixed $var If null, dump $this; if a string, dump $this->$var;
     * otherwise, dump $var.
     * 
     * @param string $label Label the dump output with this string.
     * 
     * @return void
     * 
     */
    public function dump($var = null, $label = null)
    {
        if ($var) {
            return parent::dump($var, $label);
        } else {
            $clone = clone($this);
            unset($clone->_config);
            return parent::dump($clone, $label);
        }
    }
}