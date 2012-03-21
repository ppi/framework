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
	 * Get a query string value
	 * 
	 * @param string $key
	 */
	function queryString($key) {
		
	}
	
	/**
	 * Get a post value
	 * 
	 * @param string $key
	 */
	function post($key) {
		
	}
	
	/**
	 * Get a server value
	 * 
	 * @param string $key
	 * @return void
	 */
	function server($key) {
		
	}
	
	/**
	 * Get/Set a session value
	 * 
	 * @param string $key
	 * @param null|mixed $val If this is not null, it enters setter mode
	 */
	function session($key, $val = null) {
		$session = $this->_serviceLocator->get('request')->getSession();
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
	
	function getRemote($key) {
		
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