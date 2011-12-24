<?php

/**
 *
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Core
 * @link      www.ppiframework.com
 *
 */
namespace PPI;
use Request\RequestException;

class Request {

	/**
	 * The COOKIE data
	 *
	 * @var null|object
	 */
	protected $_cookie  = null;

	/**
	 * The URI variables. eg: www.domain.com/some/uri/variables/here
	 *
	 * @var null|object
	 */
	protected $_get = null;

	/**
	 * The POST data
	 *
	 * @var null|object
	 */
	protected $_post = null;

	/**
	 * The SESSION data
	 *
	 * @var null|object
	 */
	protected $_session = null;

	/**
	 * The SERVER data
	 *
	 * @var null|object
	 */
	protected $_server = null;

	/**
	 * The query string data. eg: www.domain.com?query=string&data=here
	 *
	 * @var null|object
	 */

	protected $_getQuery = null;
	/**
	 * Remote vars cache for the getRemove() function
	 *
	 * @var array
	 */
	protected $_remoteVars = array(
		'ip'                 => '',
		'userAgent'          => '',
		'browser'            => '',
		'browserVersion'     => '',
		'browserAndVersion'  => ''
	);
	/**
	 * Vars cache for the is() function
	 *
	 * @var array
	 */
	protected $_isVars = array(
		'ajax'     => null,
		'mobile'   => null,
		'ssl'      => null
	);
	/**
	 * Mapping fields for get_browser()
	 *
	 * @var array
	 */
	protected $_userAgentMap = array(
		'browser'             => 'browser',
		'browserVersion'      => 'version',
		'browserAndVersion'   => 'parent'
	);
	/**
	 * The browser data from
	 *
	 * @var array|null
	 */
	protected $_userAgentInfo = null;
	/**
	 * The request method
	 *
	 * @var null|string
	 */
	protected $_requestMethod = null;
	/**
	 * The protocol being used
	 *
	 * @var null|string
	 */
	protected $_protocol = null;
	/**
	 * The full url including the protocol
	 *
	 * @var null|string
	 */
	protected $_url = null;
	/**
	 * The URI after the base url
	 *
	 * @var null|string
	 */
	protected $_uri = null;
	/**
	 * The quick keyval lookup array for URI parameters
	 *
	 * @var array
	 */
	protected $_uriParams = array();

	/**
	 * Constructor
	 *
	 * By default, it takes environment variables cookie, env, get, post, server and session
	 * from data collectors (PPI_Request_*)
	 *
	 * However, any of these can be overriden by an array or by an object that extends their
	 * representing PPI_Request_* class
	 *
	 * @param array $env Change environment variables
	 */
	function __construct(array $env = array()) {
		if (isset($env['session']) && (is_array($env['session']) || $env['session'] instanceof PPI\Request\Session)) {
			$this->_session = $env['session'];
		} else {
			$this->_session = new Request\Session();
		}

		if (isset($env['server']) && (is_array($env['server']) || $env['server'] instanceof Request\Server)) {
			$this->_server = $env['server'];
		} else {
			$this->_server = new Request\Server;
		}

		if (isset($env['cookie']) && (is_array($env['cookie'] || $env['cookie'] instanceof Request\Cookie))) {
			$this->_cookie = $env['cookie'];
		} else {
			$this->_cookie = new Request\Cookie();
		}
		if (isset($env['get']) && (is_array($env['get']) || $env['get'] instanceof Request\Url)) {
			$this->_get = $env['get'];
		} else {
			if(isset($env['uri'])) {
				$this->setUri($env['uri']);
			}
			$this->_get = new Request\Url($this->getUri());
		}

		if (isset($env['getQuery']) && (is_array($env['getQuery']) || $env['getQuery'] instanceof Request\Get)) {
			$this->_getQuery = $env['getQuery'];
		} else {
			$this->_getQuery = new Request\Get();
		}

		if (isset($env['post']) && (is_array($env['post']) || $env['post'] instanceof Request\Post)) {
			$this->_post = $env['post'];
		} else {
			$this->_post = new Request\Post();
		}
	}

	/**
	 * Obtain a url segments value pair by specifying the key.
	 * eg: /key/val/key2/val2 - by specifying key, you get val, by specifying key2, you get val2.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function get($key = null, $default = null) {

		if($key === null) {
			return $this->_get->all();
		}
		return isset($this->_get[$key]) ? $this->_get[$key] : $default;
	}

	/**
	 * Get variables from the Query String.
	 * eg: www.domain.com?foo=bar. Asking for 'foo' will return you 'bar'
	 *
	 * @param string $key The Key
	 * @param null $default The default value is $key doesn't exist.
	 * @return mixed
	 */
	function getQuery($key = null, $default = null) {

		if($key === null) {
			return $this->_getQuery->all();
		}
		return isset($this->_getQuery[$key]) ? $this->_getQuery[$key] : $default;	}

	/**
	 * Retrieve information passed via the $_POST array.
	 * Can specify a key and return that, else return the whole $_POST array
	 *
	 * @param string $key Specific $_POST key
	 * @param mixed $default null if not specified, mixed otherwise
	 * @return string|array Depending if you passed in a value for $p_sIndex
	 */
	function post($key = null, $default = null) {

		if($key === null) {
			return $this->_post->all();
		}
		return isset($this->_post[$key]) ? $this->_post[$key] : $default;
	}

	/**
	 * Get a value from the SERVER.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return array|null
	 */
	function server($key = null, $default = null) {

		if($key === null) {
			return $this->_server->all();
		}
		return isset($this->_server[$key]) ? $this->_server[$key] : $default;
	}

	/**
	 * Retrieve all $_POST elements that have a specific prefix
	 *
	 * @param string $sPrefix The prefix to get values with
	 * @return array
	 */
	function stripPost($p_sPrefix = '') {

		$aValues = array();
		if ($p_sPrefix !== '' && $this->is('post')) {
			$aPost = $this->post();
			$aPrefixKeys = preg_grep("/{$p_sPrefix}/", array_keys($aPost));
			foreach ($aPrefixKeys as $prefixKey) {
				$aValues[$prefixKey] = $aPost[$prefixKey];
			}
		}
		return $aValues;
	}

	/**
	 * Check whether a value has been submitted via post
	 *
	 * @param string $p_sKey The $_POST key
	 * @return boolean
	 */
	function hasPost($p_sKey) {
		return array_key_exists($p_sKey, $this->_post);
	}

	/**
	 * Remove a value from the $_POST superglobal.
	 *
	 * @param string $p_sKey The key to remove
	 * @return boolean True if the value existed, false if not.
	 */
	function removePost($p_sKey) {

		if (isset($this->_post[$p_sKey])) {
			unset($this->_post[$p_sKey]);
			return true;
		}
		return false;
	}

	/**
	 * Add a value to the $_POST superglobal
	 *
	 * @param string $p_sKey The key
	 * @param mixed $p_mValue The value to set the key with
	 * @return void
	 */
	function addPost($p_sKey, $p_mValue) {
		$this->_post[$p_sKey] = $p_mValue;
	}

	/**
	 * Wipe the $_POST superglobal
	 *
	 * @return void
	 */
	public function emptyPost() {
		$_POST = array();
	}

	/**
	 * The main getter/setter function for cookies
	 *
	 * @return boolean
	 */
	public function cookie($key = null, array $options = array()) {

		// All
		if($key === null) {
			return $this->_cookie->all();
		}

		// Getter
		if(empty($options)) {
			return $this->_cookie[$key];
		}

		// Setter
		return $this->_cookie->setCookie($key, $options);
	}

	/**
	 * Series of request related boolean checks
	 *
	 * @param string $var
	 * @return bool
	 */
	public function is($var) {

		$var = strtolower($var);
		switch ($var) {
			case 'ajax':
				if ($this->_isVars['ajax'] === null) {
					$this->_isVars['ajax'] = isset($this->_server['HTTP_X_REQUESTED_WITH'])
							&& strtolower($this->_server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
				}
				return $this->_isVars['ajax'];

			case 'post':
			case 'get':
			case 'put':
			case 'delete':
			case 'head':
				return strtolower($this->getRequestMethod()) === $var;

			case 'mobile':
				if ($this->_isVars['mobile'] === null) {
					$this->_isVars['mobile'] = $this->isRequestMobile();
				}
				return $this->_isVars['mobile'];

			case 'https':
			case 'ssl':
				if ($this->_isVars['ssl'] === null) {
					$this->_isVars['ssl'] = $this->getProtocol() === 'https';
				}
				return $this->_isVars['ssl'];
		}
		return false; // So that all paths return a val
	}

	/**
	 * Get a value from the remote requesting user/browser
	 *
	 * @param string $var
	 * @return string
	 */
	function getRemote($var) {

		switch ($var) {

			case 'ip':
				return isset($this->_server['REMOTE_ADDR']) ? $this->_server['REMOTE_ADDR'] : '0.0.0.0';

			case 'referer':
			case 'referrer':
				return isset($this->_server['HTTP_REFERER']) ? $this->_server['HTTP_REFERER'] : '';

			case 'userAgent':
				return isset($this->_server['HTTP_USER_AGENT']) ? $this->_server['HTTP_USER_AGENT'] : '';

			case 'domain':
				$url = parse_url($this->getUrl());
				return isset($url['host']) ? $url['host'] : '';
				break;

			case 'subdomain':
				throw new RequestException('Not yet developed');
				break;

			case 'browser':
			case 'browserVersion':
			case 'browserAndVersion':
				// @tbc
				break;
		}
		return ''; // So all code paths return a value
	}

	/**
	 * Get the current request uri
	 *
	 * @todo substr the baseurl
	 * @return string
	 */
	function getUri() {

		if (null === $this->_uri) {
			$this->_uri = $this->_server['REQUEST_URI'];
		}
		return $this->_uri;
	}

	/**
	 * Set the uri
	 *
	 * @param string $uri
	 * @return void
	 */
	function setUri($uri) {
		$this->_uri = $uri;
	}

	/**
	 * Get the current protocol
	 *
	 * @return string
	 */
	function getProtocol() {

		if (null === $this->_protocol) {
			$this->_protocol = isset($this->_server['HTTPS']) && $this->_server['HTTPS'] == 'on' ? 'https' : 'http';
		}
		return $this->_protocol;
	}

	/**
	 * Get the current url
	 *
	 * @return string
	 */
	function getUrl() {

		if ($this->_url === null) {
			$this->_url = $this->getProtocol() . '://' . $this->_server['HTTP_HOST'] . $this->_server['REQUEST_URI'];
		}
		return $this->_url;
	}

	/**
	 * Is the current request a mobile request
	 *
	 * @todo see if there is an array based func to do the foreach and strpos
	 * @return boolean
	 */
	protected function isRequestMobile() {

		$mobileUserAgents = array(
			'iPhone', 'MIDP', 'AvantGo', 'BlackBerry', 'J2ME', 'Opera Mini', 'DoCoMo', 'NetFront',
			'Nokia', 'PalmOS', 'PalmSource', 'portalmmm', 'Plucker', 'ReqwirelessWeb', 'iPod', 'iPad',
			'SonyEricsson', 'Symbian', 'UP\.Browser', 'Windows CE', 'Xiino', 'Android'
		);
		$currentUserAgent = $this->getRemote('userAgent');
		foreach ($mobileUserAgents as $userAgent) {
			if (strpos($currentUserAgent, $userAgent) !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the current request method
	 *
	 * @return string
	 */
	protected function getRequestMethod() {

		if (null === $this->_requestMethod) {
			$this->_requestMethod = $this->_server['REQUEST_METHOD'];
		}
		return $this->_requestMethod;
	}

	/**
	 * Get the is vars
	 *
	 * @return array
	 */
	public function getIsVars() {
		return $this->_isVars;
	}
	
	/**
	 * Get the current full url
	 *
	 * @todo match this with $this->getCurrUrl()
	 * @return string
	 */
	static function getFullUrl() {
		$sProtocol  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
		return $sProtocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	static function getCurrUrl() {
		return rtrim($_SERVER['REQUEST_URI'], '/') . '/';
	}
}
