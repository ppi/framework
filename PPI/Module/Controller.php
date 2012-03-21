<?php
namespace PPI\Module;

use Symfony\Component\HttpFoundation\RedirectResponse;

class Controller {
	
	/**
	 * Service Locator
	 * 
	 * @var null|object
	 */
	protected $_serviceLocator = null;
	
	/**
	 * Caching the results of results from $this->is() lookups.
	 * 
	 * @var array
	 */
	protected $_isCache = array();
	
	/**
	 * Get the request object
	 * 
	 * @return object
	 */
	function getRequest() {
		return $this->_serviceLocator->get('request');
	}
	
	/**
	 * Get the response object
	 * 
	 * @return object
	 */
	function getResponse() {
		return $this->_serviceLocator->get('response');
	}
	
	/**
	 * Returns a server parameter by name.
	 *
	 * @param string  $key      The key
	 * @param mixed   $default  The default value if the parameter key does not exist
	 * @param boolean $deep     If true, a path like foo[bar] will find deeper items
	 * @return string
	 */
	function server($key, $default = null, $deep = false) {
		return $this->getServer()->get($key, $default, $deep);
	}
	
	/**
	 * Returns a post parameter by name.
	 *
	 * @param string  $key      The key
	 * @param mixed   $default  The default value if the parameter key does not exist
	 * @param boolean $deep     If true, a path like foo[bar] will find deeper items
	 * @return string
	 */
	function post($key, $default = null, $deep = false) {
		return $this->getPost()->get($key, $default, $deep);
	}
	
	/**
	 * Returns a files parameter by name.
	 *
	 * @param string  $key      The key
	 * @param mixed   $default  The default value if the parameter key does not exist
	 * @param boolean $deep     If true, a path like foo[bar] will find deeper items
	 * @return string
	 */
	function files($key, $default = null, $deep = false) {
		return $this->getFiles()->get($key, $default, $deep);
	}
	
	/**
	 * Returns a query string parameter by name.
	 *
	 * @param string  $key      The key
	 * @param mixed   $default  The default value if the parameter key does not exist
	 * @param boolean $deep     If true, a path like foo[bar] will find deeper items
	 * @return string
	 */
	function queryString($key, $default = null, $deep = false) {
		return $this->getQueryString()->get($key, $default, $deep);
	}
	
	/**
	 * Returns a server parameter by name.
	 *
	 * @param string  $key      The key
	 * @param mixed   $default  The default value if the parameter key does not exist
	 * @param boolean $deep     If true, a path like foo[bar] will find deeper items
	 * @return string
	 */
	function cookie($key, $default = null, $deep = false) {
		return $this->getCookie()->get($key, $default, $deep);
	}
	
	/**
	 * Get/Set a session value
	 * 
	 * @param string $key
	 * @param null|mixed $val If this is not null, it enters setter mode
	 * @todo TBC, this doesn't work yet.
	 */
	function session($key, $val = null) {
		$session = $this->_serviceLocator->get('request')->getSession();
	}
	
	/**
	 * Shortcut for getting the server object
	 * 
	 * @return object
	 */
	function getServer() {
		return $this->getService('request')->server;
	}
	
	/**
	 * Shortcut for getting the files object
	 * 
	 * @return object
	 */
	function getFiles() {
		return $this->getService('request')->files;
	}
	
	/**
	 * Shortcut for getting the cookie object
	 * 
	 * @return object
	 */
	function getCookie() {
		return $this->getService('request')->cookies;
	}
	
	/**
	 * Shortcut for getting the query string object
	 * 
	 * @return object
	 */
	function getQueryString() {
		return $this->getService('request')->query;
	}
	
	/**
	 * Shortcut for getting the post object
	 * 
	 * @return object
	 */
	function getPost() {
		return $this->getService('request')->request;
	}
		
	/**
	 * Check if a condition 'is' true.
	 * 
	 * @param string $key
	 * @return boolean
	 * @throws InvalidArgumentException
	 */
	function is($key) {

		switch($key = strtolower($key)) {
			
			case 'ajax':
				if(!isset($this->_isCache['ajax'])) {
					return $this->_isCache['ajax'] = $this->getService('request')->isXmlHttpRequest();
				}
				return $this->_isCache['ajax'];
			
			case 'put':
			case 'delete':
			case 'post':
			case 'patch':
				if(!isset($this->_isCache['requestMethod'][$key])) {
					$this->_isCache['requestMethod'][$key] = $this->getService('request')->getMethod() === strtoupper($key);
				}
				return $this->_isCache['requestMethod'][$key];
			
			case 'ssl':
			case 'https':
			case 'secure':
				if(!isset($this->_isCache['secure'])) {
					$this->_isCache['secure'] = $this->getService('request')->isSecure();
				}
				return $this->_isCache['secure'];
				
			
			default:
				throw new \InvalidArgumentException("Invalid 'is' key supplied: {$key}");
			
		}
		
	}
	
	/**
	 * Get the remote users ip address
	 * 
	 * @param mixed $default
	 * @return string
	 */
	function getIP($default = null) {
		return $this->server('REMOTE_ADDR');
	}
	
	/**
	 * Get the remote users user agent
	 * 
	 * @param mixed $default
	 * @return string
	 */
	function getUserAgent($default = null) {
		return $this->server('HTTP_USER_AGENT');
	}
	
	/**
	 * Set the service locator
	 * 
	 * @param object $locator
	 * @return void
	 */
	function setServiceLocator($locator) {
		$this->_serviceLocator = $locator;
	}
	
	/**
	 * Get service locator
	 * 
	 * @return object
	 */
	function getServiceLocator() {
		return $this->_serviceLocator;
	}
	
	function getService($service) {
		return $this->getServiceLocator()->get($service);
	}
	
	/**
	 * Render a template
	 * 
	 * @param string $template The template to render
	 * @param array $params The params to pass to the renderer
	 * @param array $options Extra options
	 * @return string
	 */
	protected function render($template, array $params = array(), array $options = array()) {
		
		$renderer = $this->_serviceLocator->get('templating');
		
		// Helpers
		if(isset($options['helpers'])) {
			foreach($options['helpers'] as $helper) {
				$renderer->addHelper($helper);
			}
		}
		
		$params['view'] = $renderer;
		return $renderer->render($template, $params);
	}
	
	/**
	 * Set Flash Message
	 * 
	 * @param string $flashType
	 * @param string $message
	 */
	protected function setFlash($flashType, $message) {
		$this->getSession()->setFlash($flashType, $message);
	}
	
	/**
	 * Create a RedirectResponse object with your $url and $statusCode
	 * 
	 * @param string $url
	 * @param int $statusCode
	 * @return void
	 */
	protected function redirect($url, $statusCode = 302) {
		$this->getServiceLocator()->set('response', new RedirectResponse($url, $statusCode));
	}
	
	protected function getSession() {
		return $this->getService('session');
	}
	
	/**
	 * Generate a URL from the specified route name
	 * 
	 * @param string $route
	 * @param array $parameters
	 * @param bool $absolute
	 * @return string
	 */
	protected function generateUrl($route, $parameters = array(), $absolute = false) {
		return $this->getService('url.generator')->generate($route, $parameters, $absolute);
	}
	
}