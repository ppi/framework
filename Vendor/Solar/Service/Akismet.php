<?php
/**
 * 
 * Acts as an interface to the Akismet spam-checking service.
 * 
 *   <http://akismet.com>
 * 
 * You will need to register for an Akismet API key to use the service. Once
 * you have that, you can use the service like so:
 * 
 * {{code: php
 *     $akismet = Solar::factory('Solar_Service_Akismet', array(
 *         'key'  => 'apikeyvalue',
 *         'blog' => 'http://example.com',
 *     ));
 *     
 *     $comment = array(
 *         'permalink'             => 'http://example.net/blog/read/1',
 *         'comment_type'          => 'comment',
 *         'comment_author'        => 'nobody',
 *         'comment_author_email'  => 'nobody@example.com',
 *         'comment_author_url'    => 'http://example.org/nobody.html',
 *         'comment_content'       => 'The comment text.',
 *     );
 *     
 *     $is_spam = $akismet->commentCheck($data);
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Service Web-service interfaces.
 * 
 * @subpackage Solar_Service_Akismet
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Akismet.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_Service_Akismet extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string key The Akismet service API key.
     * 
     * @config string blog The front page to the blog, wiki, site, etc.
     * 
     * @config array http A configuration array for the Solar_Http_Request object to
     *   be used internally.
     * 
     * @config dependency request A Solar_Request dependency injection. Default 'request'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Service_Akismet = array(
        'key'     => null,      // api key
        'blog'    => null,      // blog uri
        'http'    => array(),   // http request config elements
        'request' => 'request', // DI for request environment
    );
    
    /**
     * 
     * Has the API key been verified?
     * 
     * @var bool
     * 
     */
    protected $_key_verified = false;
    
    /**
     * 
     * The request environment.
     * 
     * @var Solar_Request
     * 
     */
    protected $_request;
    
    /**
     * 
     * The most-recent HTTP response from Akismet.
     * 
     * @var Solar_Http_Response
     * 
     * @see getResponse()
     * 
     */
    protected $_response;
    
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
        $this->_request = Solar::dependency(
            'Solar_Request',
            $this->_config['request']
        );
    }
    
    /**
     * 
     * Get the most-recent response sent from Akismet.
     * 
     * @return Solar_Http_Response
     * 
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /**
     * 
     * Verifies the API key with Akismet.
     * 
     * @return bool True if the key is valid, false if not.
     * 
     */
    public function verifyKey()
    {
        if ($this->_key_verified) {
            return true;
        }
        
        $response = $this->_fetchResponse('verify-key', array(
            'key'  => $this->_config['key'],
            'blog' => $this->_config['blog'],
        ));
        
        if ($response->content == 'valid') {
            $this->_key_verified = true;
        } else {
            $this->_key_verified = false;
            throw $this->_exception('ERR_INVALID_KEY', array(
                'key'   => $this->_config['key'],
                'blog'  => $this->_config['blog'],
                'debug' => $response->getHeader('X-Akismet-Debug-Help'),
            ));
        }
    }
    
    /**
     * 
     * Checks the comment data with Akismet to see if it is spam.
     * 
     * See the [[Solar_Service_Akismet::_prepareData() | ]] method for the 
     * list of data keys.
     * 
     * @param array $data The comment data to be checked for spam.
     * 
     * @return bool True if the comment data is spam, false if not.
     * 
     * @see _prepareData()
     * 
     */
    public function commentCheck($data)
    {
        // verify the key
        $this->verifyKey();
        
        // prep the data elements
        $this->_prepareData($data);
        
        // check the comment
        $response = $this->_fetchResponse('comment-check', $data);
        if ($response->content == 'true') {
            return true;
        } elseif ($response->content == 'false') {
            return false;
        } else {
            throw $this->_exception('ERR_UNKNOWN_RESPONSE', array(
                'response' => $response,
                'data'     => $data,
            ));
        }
    }
    
    /**
     * 
     * Submits data to Akismet to establish it as ham (i.e., not spam).
     * 
     * See the [[Solar_Service_Akismet::_prepareData() | ]] method for the 
     * list of data keys.
     * 
     * @param array $data The comment data to be established as ham.
     * 
     * @return void
     * 
     * @see _prepareData()
     * 
     */
    public function submitHam($data)
    {
        // verify the key
        $this->verifyKey();
        
        // prep the data elements
        $this->_prepareData($data);
        
        // submit as ham
        $this->_fetchResponse('submit-ham', $data);
    }
    
    /**
     * 
     * Submits data to Akismet to establish it as spam.
     * 
     * See the [[Solar_Service_Akismet::_prepareData() | ]] method for the 
     * list of data keys.
     * 
     * @param array $data The comment data to be established as spam.
     * 
     * @return void
     * 
     * @see _prepareData()
     * 
     */
    public function submitSpam($data)
    {
        // verify the key
        $this->verifyKey();
        
        // prep the data elements
        $this->_prepareData($data);
        
        // submit as spam
        $this->_fetchResponse('submit-spam', $data);
    }
    
    /**
     * 
     * Calls the Akismet REST API via an HTTP POST request, and returns the
     * HTTP response.
     * 
     * @param string $call The Akismet REST call to make: 'verify-key', 
     * 'comment-check', 'submit-ham', or 'submit-spam'.
     * 
     * @param array $data Data to send via POST.
     * 
     * @return Solar_Http_Response
     * 
     */
    protected function _fetchResponse($call, $data)
    {
        $key = $this->_config['key'];
        
        // get a URI based on the call type
        switch ($call) {
        case 'verify-key':
            $uri = "http://rest.akismet.com/1.1/verify-key";
            break;
        case 'comment-check':
            $uri = "http://$key.rest.akismet.com/1.1/comment-check";
            break;
        case 'submit-ham':
            $uri = "http://$key.rest.akismet.com/1.1/submit-ham";
            break;
        case 'submit-spam':
            $uri = "http://$key.rest.akismet.com/1.1/submit-spam";
            break;
        default:
            throw $this->_exception('ERR_UNKNOWN_CALL', array(
                'call' => $call,
                'data' => $data,
            ));
            break;
        }
        
        // build the request
        $request = Solar::factory(
            'Solar_Http_Request',
            $this->_config['http']
        );
        
        $request->setUri($uri)
                ->setUserAgent('Solar/1.0 | Akismet/1.1')
                ->setMethod('post')
                ->setContent($data);
        
        // get the response, and done
        $this->_response = $request->fetch();
        return $this->_response;
    }
    
    /**
     * 
     * Prepares the comment, spam, or ham comment data **by reference** for
     * submission to Akismet.
     * 
     * The $data keys are:
     * 
     * `blog`
     * : The front page or home URL of the instance making the
     *   request. For a blog or wiki this would be the front page. Must be a 
     *   full URI, including 'http://'. Default is the config value for
     *   `blog`.
     * 
     * `user_ip`
     * : IP address of the comment submitter.  Default is the
     *   server REMOTE_ADDR value.
     * 
     * `user_agent`
     * : User agent information.  Default is the server 
     *   HTTP_USER_AGENT value.
     * 
     * `referrer` (note spelling)
     * : Default is the HTTP_REFERER value.
     * 
     * `permalink`
     * : The permanent location of the entry the comment was submitted to.
     * 
     * `comment_type`
     * : May be blank, 'comment', 'trackback', 'pingback', or any other value 
     *   (e.g., 'registration').  Default blank.
     * 
     * `comment_author`
     * : Submitted name with the comment.  Default blank. Leaving blank is 
     *   highly likely to result in a "spam" result.
     * 
     * `comment_author_email`
     * : Submitted email address
     * 
     * `comment_author_url`
     * : Commenter URL.
     * 
     * `comment_content`
     * : The content that was submitted.
     * 
     * @param array &$data The data to prepare **by reference**.
     * 
     * @return void
     * 
     */
    protected function _prepareData(&$data)
    {
        $base = array(
            'blog'                  => $this->_config['blog'],
            'user_ip'               => $this->_request->server('REMOTE_ADDR'),
            'user_agent'            => $this->_request->http('user_agent'),
            'referrer'              => $this->_request->http('referer'),
            'permalink'             => null,
            'comment_type'          => null,
            'comment_author'        => null,
            'comment_author_email'  => null,
            'comment_author_url'    => null,
            'comment_content'       => null,
        );
        
        // merge the base info, data overrides, and the server info
        $data = array_merge($base, $data, $this->_request->server());
    }
}