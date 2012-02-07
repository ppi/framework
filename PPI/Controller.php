<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Controller
 * @link      www.ppiframework.com
 */
namespace PPI;
class Controller {

    /**
     * The PPI_View object
     *
     * @var PPI_View
     */
	protected $_view = null;

	protected $_request = null;

	protected $_response = null;

	/**
	 * The app object
	 *
	 * @var null
	 */
	protected $_app = null;

    /**
     * The constructor
     */
	function __construct () {}

	/**
	 * The base init function that the framework uses to initialise the controller
	 *
	 * @param string $app
	 * @todo make this some kind of DI container instead
	 * @return void
	 */
	function systemInit($app) {
		$this->_app      = $app;
		$this->_request  = $app->getEnv('request');
		$this->_response = $app->getEnv('response');
		$this->_view     = $app->getEnv('view');
	}

	function getApp() {
		return $this->_app;
	}

	function getRequest() {
		return $this->_request;
	}

	function getView() {
		return $this->_view;
	}

	function getResponse() {
		return $this->_response;
	}

	/**
	 * Perform redirect to internal framework url. Optionally redirect to external host
     *
	 * @todo Make this auto-detect the first X chars starting with http:// and remove the prependbase char
	 * @param string $p_sURL Optional param for where to redirect to
	 * @param boolean $p_bPrependBase Default is true. If true will prepend the framework's base Url.
 	 *									If false will redirect to absolute external url.
 	 * @throws PPI_Exception
     * @return boolean
	 */
	protected function redirect($p_sURL = '', $p_bPrependBase = true) {
		$sUrl = ($p_bPrependBase === true) ? $this->getConfig()->system->base_url . $p_sURL : $p_sURL;
		if(!headers_sent()) {
			header("Location: $sUrl");
			exit;
		} else {
			throw new \Exception('Unable to redirect to '.$sUrl.'. Headers already sent');
		}
	}

	/**
	 * Load a view
	 *
	 * @param string $p_tplFile The view filename. File extensions are optional.
	 * @param array $p_tplParams Optional parameters to the view file.
	 * @return void
	 */
	protected function load($p_tplFile, $p_tplParams = array()) {

		if(!isset($p_tplParams['isAjax']) && $this->is('ajax')) {
			$p_tplParams['isAjax'] = true;
		}
		return $this->render($p_tplFile, $p_tplParams);
	}

	/**
	 * Override the current set theme name
	 *
	 * @param string $p_sThemeName
	 * @return void
	 */
	protected function theme($p_sThemeName) {
		$this->_view->theme($p_sThemeName);
	}

	/**
	 * Set a view variable or a list of view variables.
	 *
	 * @param mixed $p_mKeys
	 * @param mixed $p_mValue
     * @return void
	 */
	protected function set($p_mKeys, $p_mValue = null) {

		if(is_scalar($p_mKeys)) {
			$p_mKeys = array($p_mKeys => $p_mValue);
		}
		foreach($p_mKeys as $key => $val) {
			$this->_view->set($key, $val);
		}
	}

	/**
	 * Load a view, but override the renderer to smarty
	 *
	 * @param string $p_tplFile The view filename. File extensions are optional.
	 * @param array $p_tplParams Optional parameters to the view file.
	 * @return void
	 *
	 */
	protected function loadSmarty($p_tplFile, $p_tplParams = array()) {
		$this->_view->loadsmarty($p_tplFile, $p_tplParams);
	}

    /**
     * Append to the list of stylesheets to be included
     *
     * @param mixed $p_mStylesheet This can be an existing array of stylesheets or a string.
     * @return void
     */
    protected function addStylesheet($p_mStylesheet) {
        $this->_response->addCSS(func_get_args());
    }

	protected function addCSS() {
		$this->_response->addCSS(func_get_args());
	}

	/**
	 * Clear all set stylesheets
	 *
	 * @return void
	 */
	protected function clearCSS() {
		$this->_response->clearCSS();
	}

    /**
     * Append to the list of javascript files to be included
     *
     * @return void
     */
    protected function addJavascript() {
        $this->_response->addJS(func_get_args());
    }

	/**
	 * Add a javascript file
	 *
	 * @return void
	 */
	protected function addJS() {
		$this->_response->addJS(func_get_args());
	}

	/**
	 * Override the default template file, with optional include for the .php or .tpl extension
     *
	 * @todo have this lookup the template engines default extension and remove the smarty param
	 * @param string $p_sNewTemplateFile New Template Filename
     * @return void
	 */
	protected function setTemplateFile($p_sNewTemplateFile) {
		$this->_view->setTemplateFile($p_sNewTemplateFile);
	}

	/**
	 * Check if a template exists
	 *
	 * @param string $templateName The template Name
	 * @return bool
	 */
	protected function templateExists($templateName) {
		return $this->_view->templateExists($templateName);
	}


	/**
	 * Setter for setting the flash message to appear on next page load.
     *
	 * @param string $message
	 * @param boolean $success
	 * @return void
	 */
	protected function setFlashMessage($message, $success = true) {
		$this->_response->setFlash($message, $success);
	}

	/**
	 * Setter for setting the flash message to appear on next page load.
     *
	 * @param string $message
	 * @param boolean $success
	 * @return void
	 */
	protected function setFlash($message, $success = true) {
		$this->_response->setFlash($message, $success);
	}

	/**
	 * Getter for the flash message.
     *
	 * @return string
	 */
	protected function getFlashMessage() {
		$this->_response->getFlash();
	}

	/**
	 * Clear the flash message from the session
     *
	 * @return void
	 */
	protected function clearFlashMessage() {
		$this->_response->clearFlash();
	}

    /**
     * Get the full current URI
     *
     * @todo Maybe just strip off baseUrl from the URL and that's our URI
     * @return string
     */
	protected function getCurrUrl() {
		throw new PPI_Exception('Deprecated function - use getUri() instead');
	}

	/**
	 * Get the full URL
	 *
	 * @return string
	 */
	protected function getFullUrl() {
		return $this->getUrl();
	}

	/**
	 * Get the current URL
	 *
	 * @return string
	 */
	protected function getUrl() {
		return $this->_request->getUrl();
	}

	/**
	 * Get the current protocol in use
	 *
	 * @return string
	 */
	protected function getProtocol() {
		return $this->_request->getProtocol();
	}

	/**
	 * Get the current URI
	 *
	 * @return string
	 */
	protected function getUri() {
		return $this->_request->getUri();
	}

	/**
	 * Get the base url set in the config
     *
	 * @return string
	 */
	protected function getBaseUrl() {
		return $this->getConfig()->system->base_url;
	}

	/**
	 * PPI_Controller::getConfig()
	 * Returns the PPI_Config object
	 *
	 * @return object
	 */
	protected function getConfig() {
		return Core::getConfig();
	}

	public function setApp($app) {
		$this->_app = $app;
	}

	/**
	 * Returns the session object
     *
	 * @param mixed $p_mOptions
	 * @return object PPI_Model_Session
	 */
	protected function getSession($p_mOptions = null) {
		return Core::getSession($p_mOptions);
	}

	/**
	 * Get the dispatcher object
	 *
	 * @return object
	 */
	protected function getDispatcher() {
		return Core::getDispatcher();
	}

	/**
	 * Get the cache object from Core
	 *
	 * @param array $options
	 * @return object
	 */
	protected function getCache(array $options = array()) {
		return Core::getCache($options);
	}

	/**
	 * Get a new PPI_Form object
	 *
	 * @return PPI_Form
	 */
	protected function getForm() {
		return new Form();
	}

	/**
	 * Checks if the current user is logged in
     *
	 * @return boolean
	 */
	protected function isLoggedIn() {
		$aAuthData = $this->getAuthData();
		return !empty($aAuthData);
	}

	/**
	 * Gets the current logged in users authentication data
     *
     * @param boolean $p_bUseArray Default is true. If false then will return an object instead
	 * @return array|object
	 */
	protected function getAuthData($p_bUseArray = true) {
		$authData = $this->getSession()->getAuthData();
		return $p_bUseArray ? $authData : (object) $authData;
	}

	/**
	 * Get the current logged in users authentication data
	 *
	 * @todo make a setUser() function to perform setAuthData()
	 * @return object
	 */
	protected function getUser() {
		return (object) $this->getSession()->getAuthData();
	}

	/**
	 * Get a parameter from the URI
	 *
	 * @param string $p_sVar The key name
	 * @param mixed $p_mDefault The default value returned if the key doesn't exist
	 * @return mixed
	 */
	protected function get($p_sVar = null, $p_mDefault = null) {
		return $this->_request->get($p_sVar, $p_mDefault);
	}

	/**
	 * Get a parametere from the query string aka $_GET
	 *
	 * @param string $key The Key
	 * @param null $default The default value is $key doesn't exist
	 * @return mixed
	 */
	protected function getQuery($key, $default = null) {
		return $this->_request->getQuery($key, $default);
	}

	/**
	 * Access the HTTP POST variables
	 *
	 * @param string $p_sVar The variable name to access
	 * @param mixed $p_mDefault The default value if the key defined doesn't exist
	 * @return mixed
	 */
	protected function post($p_sVar = null, $p_mDefault = null) {
		return $this->_request->post($p_sVar, $p_mDefault);
	}

	/**
	 * Get server values
	 *
	 * @param string|null $key
	 * @return string $default
	 */
	protected function server($key = null, $default = null) {
		return $this->_request->server($key, $default);

	}

	/**
	 * The cookie getter/setter
	 *
	 * @param string|null $key
	 * @param array|string $options
	 * @return string|boolean
	 */
	protected function cookie($key = null, $options = array()) {

		if(is_string($options)) {
			$options = array('content' => $options);
		}

		return $this->_request->cookie($key, $options);	
	}

	/**
	 * Does a particular post variable exist
	 *
	 * @param string $p_sKey The post variable
	 * @return boolean
	 */
	protected function hasPost($p_sKey) {
		return $this->_request->hasPost($p_sKey);
	}

	/**
	 * Has the form been submitted ?
	 *
	 * @return boolean
	 */
	protected function isPost() {
		return $this->_request->is('post');
	}

	/**
	 * Obtain strippost from the input object
	 * Will give all HTTP POST variables that match a specific prefix
	 *
	 * @param unknown_type $p_sPrefix
	 * @return unknown
	 */
	protected function stripPost($p_sPrefix) {
		return $this->_request->stripPost($p_sPrefix);
	}

	/**
	 * Remove a value from HTTP POST
	 *
	 * @param string $p_sKey
	 * @return void
	 */
	protected function removePost($p_sKey) {
		$this->_request->removePost($p_sKey);
	}

	/**
	 * Empty the entire HTTP POST
	 *
	 * @return void
	 */
	protected function emptyPost() {
		$this->_request->emptyPost();
	}

	/**
	 * Return a boolean values on this 'is' check
	 *
	 * @param string $var
	 * @return bool
	 */
	protected function is($var) {
		switch($var) {
			case 'ajax':
			case 'post':
			case 'get':
			case 'head':
			case 'put':
			case 'mobile':
			case 'ssl':
			case 'https':
				return $this->_request->is($var);
				break;
		}
		return false;
	}

	/**
	 * Get a remote variable from the request object
	 *
	 * @param string $var
	 * @return string|null
	 */
	protected function getRemote($var) {
		switch($var) {
			case 'ip':
			case 'browser':
			case 'browserAndVersion':
			case 'browserVersion':
			case 'userAgent':
			case 'referer':
			case 'referrer':
				return $this->_request->getRemote($var);
				break;
		}
		return null;
	}

	/**
	 * The main render function that pull in data from all framework components to render this page.
	 *
	 * @param string $template
	 * @param array $params
	 * @param array $options
	 * @return mixed
	 */
	public function render($template, array $params = array(), $options = array()) {

		$core = array();

		$core['charset'] = $this->_response->getCharset();
		$core['url']     = $this->_request->getUrl();
		$core['uri']     = $this->_request->getUri();
		$core['flash']   = $this->_response->getFlashAndClear();
		$core['is']      = array(
			'ajax'   => $this->_request->is('ajax'),
			'https'  => $this->_request->is('https'),
			'post'   => $this->_request->is('post'),
			'get'    => $this->_request->is('get'),
			'put'    => $this->_request->is('put'),
			'delete' => $this->_request->is('delete'),
			'head'   => $this->_request->is('head')
		);

		$core['files']['css'] = $this->_response->getCSSFiles();
		$core['files']['js']  = $this->_response->getJSFiles();

		$params['core'] = $core;

		return $this->_view->render($template, $params, $options);
	}

	/**
	 * Create a cache key for our cached template
	 *
	 * @param string $template
	 * @param array $options
	 * @return string
	 */
	protected  function createCachedRenderKey($template, array $options = array()) {
		return $this->_view->createCachedRenderKey($template, $options);
	}

	/**
	 * Check if a cachedRender item exists in the cache
	 *
	 * @param string $template
	 * @param array $options
	 * @return boolean
	 */
	protected function cachedRenderExists($template, array $options = array()) {
		return $this->_view->cachedRenderExists($template, $options);
	}
	
	/**
	 * Check if a view has been cached before.
	 * 
	 * @param $template
	 * @param array $options
	 * @return bool
	 */
	protected function isViewCached($template, array $options = array()) {
		return $this->cachedRenderExists($template, $options);
	}

	/**
	 * Get the cached render contents from the cache
	 *
	 * @param string $template
	 * @param array $options
	 * @return string
	 */
	protected function getCachedRender($template, array $options = array()) {
		return $this->_view->getCachedRender($template, $options);
	}
	
	protected function getCachedView($template, array $options = array()) {
		return $this->_view->getCachedRender($template, $options);
	}
}
