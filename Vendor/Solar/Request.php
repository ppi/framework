<?php
/**
 * 
 * Class for gathering details about the request environment.
 * 
 * To be safe, treat everything in the superglobals as tainted.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Request.php 4589 2010-06-11 13:33:01Z pmjones $
 * 
 */
class Solar_Request extends Solar_Base
{
    /**
     * 
     * Imported $_ENV values.
     * 
     * @var array
     * 
     */
    public $env;
    
    /**
     * 
     * Imported $_GET values.
     * 
     * @var array
     * 
     */
    public $get;
    
    /**
     * 
     * Imported $_POST values.
     * 
     * @var array
     * 
     */
    public $post;
    
    /**
     * 
     * Imported $_COOKIE values.
     * 
     * @var array
     * 
     */
    public $cookie;
    
    /**
     * 
     * Imported $_SERVER values.
     * 
     * @var array
     * 
     */
    public $server;
    
    /**
     * 
     * Imported $_FILES values.
     * 
     * @var array
     * 
     */
    public $files;
    
    /**
     * 
     * Imported $_SERVER['HTTP_*'] values.
     * 
     * Header keys are normalized and lower-cased; keys and values are
     * filtered for control characters.
     * 
     * @var array
     * 
     */
    public $http;
    
    /**
     * 
     * Imported $_SERVER['argv'] values.
     * 
     * @var array
     * 
     */
    public $argv;
    
    /**
     * 
     * Is this GET request after a POST/PUT redirect?
     * 
     * @var bool
     * 
     */
    protected $_is_gap = null;
    
    /**
     * 
     * Cross-site request forgery detector.
     * 
     * @var Solar_Csrf
     * 
     */
    protected $_csrf;
    
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
        $this->reset();
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[Solar_Request::$get | ]] property,
     * or an alternate default value if that key does not exist.
     * 
     * @param string $key The $get key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $get[$key], or the alternate default
     * value.
     * 
     */
    public function get($key = null, $alt = null)
    {
        return $this->_getValue('get', $key, $alt);
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[Solar_Request::$post | ]] property,
     * or an alternate default value if that key does not exist.
     * 
     * @param string $key The $post key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $post[$key], or the alternate default
     * value.
     * 
     */
    public function post($key = null, $alt = null)
    {
        return $this->_getValue('post', $key, $alt);
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[Solar_Request::$cookie | ]] property,
     * or an alternate default value if that key does not exist.
     * 
     * @param string $key The $cookie key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $cookie[$key], or the alternate default
     * value.
     * 
     */
    public function cookie($key = null, $alt = null)
    {
        return $this->_getValue('cookie', $key, $alt);
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[Solar_Request::$env | ]] property,
     * or an alternate default value if that key does not exist.
     * 
     * @param string $key The $env key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $env[$key], or the alternate default
     * value.
     * 
     */
    public function env($key = null, $alt = null)
    {
        return $this->_getValue('env', $key, $alt);
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[Solar_Request::$server | ]] property,
     * or an alternate default value if that key does not exist.
     * 
     * @param string $key The $server key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $server[$key], or the alternate default
     * value.
     * 
     */
    public function server($key = null, $alt = null)
    {
        return $this->_getValue('server', $key, $alt);
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[Solar_Request::$files | ]] property,
     * or an alternate default value if that key does not exist.
     * 
     * @param string $key The $files key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $files[$key], or the alternate default
     * value.
     * 
     */
    public function files($key = null, $alt = null)
    {
        return $this->_getValue('files', $key, $alt);
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[Solar_Request::$argv | ]] property,
     * or an alternate default value if that key does not exist.
     * 
     * @param string $key The $argv key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $argv[$key], or the alternate default
     * value.
     * 
     */
    public function argv($key = null, $alt = null)
    {
        return $this->_getValue('argv', $key, $alt);
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[Solar_Request::$http | ]] property,
     * or an alternate default value if that key does not exist.
     * 
     * @param string $key The $http key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist.
     * 
     * @return mixed The value of $http[$key], or the alternate default
     * value.
     * 
     */
    public function http($key = null, $alt = null)
    {
        if ($key !== null) {
            $key = strtolower($key);
        }
        return $this->_getValue('http', $key, $alt);
    }
    
    /**
     * 
     * Retrieves an **unfiltered** value by key from the [[Solar_Request::$post | ]] *and* 
     * [[Solar_Request::$files | ]] properties, or an alternate default value if that key does 
     * not exist in either location.  Files takes precedence over post.
     * 
     * @param string $key The $post and $files key to retrieve the value of.
     * 
     * @param string $alt The value to return if the key does not exist in
     * either $post or $files.
     * 
     * @return mixed The value of $post[$key] combined with $files[$key], or 
     * the alternate default value.
     * 
     */
    public function postAndFiles($key = null, $alt = null)
    {
        $post  = $this->_getValue('post',  $key, false);
        $files = $this->_getValue('files', $key, false);
        
        // no matches in post or files
        if (! $post && ! $files) {
            return $alt;
        }
        
        // match in post, not in files
        if ($post && ! $files) {
            return $post;
        }
        
        // match in files, not in post
        if (! $post && $files) {
            return $files;
        }
        
        // are either or both arrays?
        $post_array  = is_array($post);
        $files_array = is_array($files);
        
        // both are arrays, merge them
        if ($post_array && $files_array) {
            return array_merge($post, $files);
        }
        
        // post array but single files, append to post
        if ($post_array && ! $files_array) {
            array_push($post, $files);
            return $post;
        }
        
        // files array but single post, append to files
        if (! $post_array && $files_array) {
            array_push($files, $post);
            return $files;
        }
        
        // now what?
        throw $this->_exception('ERR_POST_AND_FILES', array(
            'key' => $key,
        ));
    }
    
    /**
     * 
     * Is this a secure SSL request?
     * 
     * @return bool
     * 
     */
    public function isSsl()
    {
        return $this->server('HTTPS') == 'on'
            || $this->server('SERVER_PORT') == 443;
    }
    
    /**
     * 
     * Is this a command-line request?
     * 
     * @return bool
     * 
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }
    
    /**
     * 
     * Is the current request a cross-site forgery?
     * 
     * @return bool
     * 
     */
    public function isCsrf()
    {
        if (! $this->_csrf) {
            $this->_csrf = Solar::factory('Solar_Csrf');
        }
        
        return $this->_csrf->isForgery();
    }
    
    /**
     * 
     * Is this a GET-after-POST request?
     * 
     * @return bool
     * 
     */
    public function isGap()
    {
        if ($this->_is_gap === null) {
            $session = Solar::factory('Solar_Session', array(
                'class' => get_class($this),
            ));
            $this->_is_gap = (bool) $session->getFlash('is_gap');
        }
        
        return $this->_is_gap;
    }
    
    /**
     * 
     * Is this a 'GET' request?
     * 
     * @return bool
     * 
     */
    public function isGet()
    {
        return $this->server('REQUEST_METHOD') == 'GET';
    }
    
    /**
     * 
     * Is this a 'POST' request?
     * 
     * @return bool
     * 
     */
    public function isPost()
    {
        return $this->server('REQUEST_METHOD') == 'POST';
    }
    
    /**
     * 
     * Is this a 'PUT' request? Supports Google's X-HTTP-Method-Override
     * solution to languages like PHP not fully honoring the HTTP PUT method.
     * 
     * @return bool
     * 
     */
    public function isPut()
    {
        $is_put      = $this->server('REQUEST_METHOD') == 'PUT';
        
        $is_override = $this->server('REQUEST_METHOD') == 'POST' &&
                       $this->http('X-HTTP-Method-Override') == 'PUT';
        
        return ($is_put || $is_override);
    }
    
    /**
     * 
     * Is this a 'DELETE' request? Supports Google's X-HTTP-Method-Override
     * solution to languages like PHP not fully honoring the HTTP DELETE
     * method.
     * 
     * @return bool
     * 
     */
    public function isDelete()
    {
        $is_delete   = $this->server('REQUEST_METHOD') == 'DELETE';
        
        $is_override = $this->server('REQUEST_METHOD') == 'POST' &&
                       $this->http('X-HTTP-Method-Override') == 'DELETE';
        
        return ($is_delete || $is_override);
    }
    
    /**
     * 
     * Is this an XmlHttpRequest?
     * 
     * Checks if the `X-Requested-With` HTTP header is `XMLHttpRequest`.
     * Generally used in addition to the [[Solar_Request::isPost() | ]],
     * [[Solar_Request::isGet() | ]], etc. methods to identify Ajax-style 
     * HTTP requests.
     * 
     * @return bool
     * 
     */
    public function isXhr()
    {
        return strtolower($this->http('X-Requested-With')) == 'xmlhttprequest';
    }
    
    /**
     * 
     * Reloads properties from the superglobal arrays.
     * 
     * Normalizes HTTP header keys, dispels magic quotes.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        // load the "real" request vars
        $vars = array('env', 'get', 'post', 'cookie', 'server', 'files');
        foreach ($vars as $key) {
            $var = '_' . strtoupper($key);
            if (isset($GLOBALS[$var])) {
                $this->$key = $GLOBALS[$var];
            } else {
                $this->$key = array();
            }
        }
        
        // dispel magic quotes if they are enabled.
        // http://talks.php.net/show/php-best-practices/26
        if (get_magic_quotes_gpc()) {
            $in = array(&$this->get, &$this->post, &$this->cookie);
            while (list($k, $v) = each($in)) {
                foreach ($v as $key => $val) {
                    if (! is_array($val)) {
                        $in[$k][$key] = stripslashes($val);
                        continue;
                    }
                    $in[] =& $in[$k][$key];
                }
            }
            unset($in);
        }
        
        // load the "fake" argv request var
        $this->argv = (array) $this->server('argv');
        
        // load the "fake" http request var
        $this->http = array();
        foreach ($this->server as $key => $val) {
            
            // only retain HTTP headers
            if (substr($key, 0, 5) == 'HTTP_') {
                
                // normalize the header key to lower-case
                $nicekey = strtolower(
                    str_replace('_', '-', substr($key, 5))
                );
                
                // strip control characters from keys and values
                $nicekey = preg_replace('/[\x00-\x1F]/', '', $nicekey);
                $this->http[$nicekey] = preg_replace('/[\x00-\x1F]/', '', $val);
                
                // no control characters wanted in $this->server for these
                $this->server[$key] = $this->http[$nicekey];
                
                // disallow external setting of X-JSON headers.
                if ($nicekey == 'x-json') {
                    unset($this->http[$nicekey]);
                    unset($this->server[$key]);
                }
            }
        }
        
        // rebuild the files array to make it look more like POST
        if ($this->files) {
            $files = $this->files;
            $this->files = array();
            $this->_rebuildFiles($files, $this->files);
        }
    }
    
    /**
     * 
     * Recursive method to rebuild $_FILES structure to be more like $_POST.
     * 
     * @param array $src The source $_FILES array, perhaps from a sub-
     * element of that array/
     * 
     * @param array &$tgt Where we will store the restructured data when we
     * find it.
     * 
     * @return void
     * 
     */
    protected function _rebuildFiles($src, &$tgt)
    {
        // an array with these keys is a "target" for us (pre-sorted)
        $tgtkeys = array('error', 'name', 'size', 'tmp_name', 'type');
        
        // the keys of the source array (sorted so that comparisons work
        // regardless of original order)
        $srckeys = array_keys((array) $src);
        sort($srckeys);
        
        // is the source array a target?
        if ($srckeys == $tgtkeys) {
            // get error, name, size, etc
            foreach ($srckeys as $key) {
                if (is_array($src[$key])) {
                    // multiple file field names for each error, name, size, etc.
                    foreach ((array) $src[$key] as $field => $value) {
                        $tgt[$field][$key] = $value;
                    }
                } else {
                    // the key itself is error, name, size, etc., and the
                    // target is already the file field name
                    $tgt[$key] = $src[$key];
                }
            }
        } else {
            // not a target, create sub-elements and rebuild them too
            foreach ($src as $key => $val) {
                $tgt[$key] = array();
                $this->_rebuildFiles($val, $tgt[$key], $key);
            }
        }
    }
    
    /**
     * 
     * Common method to get a request value and return it.
     * 
     * @param string $var The request variable to fetch from: get, post,
     * etc.
     * 
     * @param string $key The array key, if any, to get the value of.
     * 
     * @param string $alt The alternative default value to return if the
     * requested key does not exist.
     * 
     * @return mixed The requested value, or the alternative default
     * value.
     * 
     */
    protected function _getValue($var, $key, $alt)
    {
        // get the whole property, or just one key?
        if ($key === null) {
            // no key selected, return the whole array
            return $this->$var;
        } elseif (array_key_exists($key, $this->$var)) {
            // found the requested key.
            // need the funny {} becuase $var[$key] will try to find a
            // property named for that element value, not for $var.
            return $this->{$var}[$key];
        } else {
            // requested key does not exist
            return $alt;
        }
    }
}
