<?php

/**
 * The base PPI controller class. 
 *
 * @package   Core
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ppi.io
 */

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
	 * The options for this controller
	 * 
	 * @var array
	 */
	protected $_options = array();
	
	/**
	 * Get the request object
	 * 
	 * @return object
	 */
	protected function getRequest() {
		return $this->_serviceLocator->get('request');
	}
	
	/**
	 * Get the response object
	 * 
	 * @return object
	 */
	protected function getResponse() {
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
	protected function server($key = null, $default = null, $deep = false) {
		return $key === null ? $this->getServer()->all() : $this->getServer()->get($key, $default, $deep);
	}
	
	/**
	 * Returns a post parameter by name.
	 *
	 * @param string  $key      The key
	 * @param mixed   $default  The default value if the parameter key does not exist
	 * @param boolean $deep     If true, a path like foo[bar] will find deeper items
	 * @return string
	 */
	protected function post($key = null, $default = null, $deep = false) {
		return $key === null ? $this->getPost()->all() : $this->getPost()->get($key, $default, $deep);
	}
	
	/**
	 * Returns a files parameter by name.
	 *
	 * @param string  $key      The key
	 * @param mixed   $default  The default value if the parameter key does not exist
	 * @param boolean $deep     If true, a path like foo[bar] will find deeper items
	 * @return string
	 */
	protected function files($key = null, $default = null, $deep = false) {
		return $key === null ? $this->getFiles()->all() : $this->getFiles()->get($key, $default, $deep);
	}
	
	/**
	 * Returns a query string parameter by name.
	 *
	 * @param string  $key      The key
	 * @param mixed   $default  The default value if the parameter key does not exist
	 * @param boolean $deep     If true, a path like foo[bar] will find deeper items
	 * @return string
	 */
	protected function queryString($key = null, $default = null, $deep = false) {
		return $key === null ? $this->getQueryString()->all() : $this->getQueryString()->get($key, $default, $deep);
	}
	
	/**
	 * Returns a server parameter by name.
	 *
	 * @param string  $key      The key
	 * @param mixed   $default  The default value if the parameter key does not exist
	 * @param boolean $deep     If true, a path like foo[bar] will find deeper items
	 * @return string
	 */
	protected function cookie($key = null, $default = null, $deep = false) {
		return $key === null ? $this->getCookie()->all() : $this->getCookie()->get($key, $default, $deep);
	}
	
	/**
	 * Get/Set a session value
	 * 
	 * @param string $key
	 * @param null|mixed $default If this is not null, it enters setter mode
	 * @todo TBC, this doesn't work yet.
	 */
	protected function session($key = null, $default = null) {
		return $key === null ? $this->getSession()->all() : $this->getSession()->get($key, $default);
	}
	
	/**
	 * Shortcut for getting the server object
	 * 
	 * @return object
	 */
	protected function getServer() {
		return $this->getService('request')->server;
	}
	
	/**
	 * Shortcut for getting the files object
	 * 
	 * @return object
	 */
	protected function getFiles() {
		return $this->getService('request')->files;
	}
	
	/**
	 * Shortcut for getting the cookie object
	 * 
	 * @return object
	 */
	protected function getCookie() {
		return $this->getService('request')->cookies;
	}
	
	/**
	 * Shortcut for getting the query string object
	 * 
	 * @return object
	 */
	protected function getQueryString() {
		return $this->getService('request')->query;
	}
	
	/**
	 * Shortcut for getting the post object
	 * 
	 * @return object
	 */
	protected function getPost() {
		return $this->getService('request')->request;
	}
	
	/**
	 * Shortcut for getting the session object
	 * 
	 * @return mixed
	 */
	protected function getSession() {
		return $this->getService('session');
	}
		
	/**
	 * Check if a condition 'is' true.
	 * 
	 * @param string $key
	 * @return boolean
	 * @throws InvalidArgumentException
	 */
	protected function is($key) {

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
	protected function getIP() {
		return $this->server('REMOTE_ADDR');
	}
	
	/**
	 * Get the remote users user agent
	 * 
	 * @param mixed $default
	 * @return string
	 */
	protected function getUserAgent() {
		return $this->server('HTTP_USER_AGENT');
	}
	
	/**
	 * Set the service locator
	 * 
	 * @param object $locator
	 * @return void
	 */
	public function setServiceLocator($locator) {
		$this->_serviceLocator = $locator;
	}
	
	/**
	 * Get service locator
	 * 
	 * @return object
	 */
	public function getServiceLocator() {
		return $this->_serviceLocator;
	}
	
	/**
	 * Get a registered service
	 * 
	 * @param string $service
	 * @return mixed
	 */
	protected function getService($service) {
		return $this->getServiceLocator()->get($service);
	}
	
	/**
	 * Get the data source service
	 * 
	 * @return mixed
	 */
	protected function getDataSource() {
		return $this->getService('DataSource');
	}
	
	/**
	 * Render a template
	 * 
	 * @param string $template The template to render
	 * @param array $params    The params to pass to the renderer
	 * @param array $options   Extra options
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
	 * @param string $flashType The flash type
	 * @param string $message The flash message
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
	
	/**
	 * Shortcut function for redirecting to a route without manually calling $this->generateUrl()
	 * You just specify a route name and it goes there.
	 * 
	 * @param string $route
	 * @return void
	 */
	protected function redirectToRoute($route) {
		$this->redirect($this->getService('router')->generate($route));
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
		return $this->getService('router')->generate($route, $parameters, $absolute);
	}
	
	/**
	 * Get the app's global configuration
	 * 
	 * @return mixed
	 */
	protected function getConfig() {
		return $this->getService('Config');
	}
	
	/**
	 * Set the options for this controller
	 * 
	 * @param array $options
	 * 
	 * @return $this
	 */
	public function setOptions($options) {
		$this->_options = $options;
		return $this;
	}
	
	/**
	 * Get an option from the controller
	 * 
	 * @param string $option The option name
	 * @param null   $default The default value if the option does not exist
	 * @return mixed
	 */
	public function getOption($option, $default = null) {
		return isset($this->_options[$option]) ? $this->_options[$option] : $default;
	}
	
	/**
	 * Get the environment type, defaulting to 'development' if it has not been set
	 * 
	 * @return string
	 */
	public function getEnv() {
		return $this->getOption('environment', 'development');
	}
	
	/**
	 * Inject services into our controller using setters matching against service names
	 * 
	 * @return void
	 */
	public function injectServices() {
		
		if($this->_serviceLocator === null) {
			return;
		}

		// A bunch of public methods that should be omitted.
		$blackList = array('setServiceLocator', 'getServiceLocator', 'injectServices');
		$r = new \ReflectionClass($this);
		
		foreach($r->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			if(!in_array($method->name, $blackList) 
				&& substr($method->name, 0, 3) === 'set'
				&& $this->_serviceLocator->has(($service = substr($method->name, 3)))
			) {
				$this->{$method->name}($this->_serviceLocator->get($service));
			}
		}

	}
	
}