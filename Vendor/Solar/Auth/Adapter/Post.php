<?php
/**
 * 
 * Authenticate via simple HTTP POST request-and-reply.
 * 
 * Based in part on php.net user comments ...
 * 
 * - <http://us3.php.net/manual/en/function.fsockopen.php#57275>
 * 
 * - <http://us3.php.net/manual/en/function.fopen.php#58099>
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Post.php 3850 2009-06-24 20:18:27Z pmjones $
 * 
 */
class Solar_Auth_Adapter_Post extends Solar_Auth_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string uri URL to the HTTP service, for example "https://example.com/login.php".
     * 
     * @config string handle The handle element name.
     * 
     * @config string passwd The passwd element name.
     * 
     * @config array headers Additional headers to use in the POST request.
     * 
     * @config array replies Key-value pairs where the key is the server reply string, and
     *   and the value is a boolean indicating if it indicates success or
     *   failure in authenticating.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Adapter_Post = array(
        'uri'     => 'https://example.com/services/authenticate.php',
        'handle'  => 'handle',
        'passwd'  => 'passwd',
        'headers' => null, // additional heaaders
        'replies' => array('0' => false, '1' => true), // key-value array of replies
    );
    
    /**
     * 
     * Verifies a username handle and password.
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     * 
     */
    protected function _processLogin()
    {
        // create an array of POST data
        $content = array(
            $this->_config['handle'] => $this->_handle,
            $this->_config['passwd'] => $this->_passwd,
        );
        
        // build the base request
        $request = Solar::factory('Solar_Http_Request');
        $request->setUri($this->_config['uri'])
                ->setMethod('post')
                ->setContent($content);
        
        // add custom headers
        foreach ((array) $this->_config['headers'] as $label => $value) {
            $request->setHeader($label, $value);
        }
        
        // fetch the response body content
        $response = $request->fetch();
        $reply = trim($response->content);
        
        // is the reply string a known reply, and set to true?
        $ok = array_key_exists($reply, $this->_config['replies']) &&
              (bool) $this->_config['replies'][$reply];
             
        if ($ok) {
            return array('handle' => $this->_handle);
        } else {
            return false;
        }
    }
}
