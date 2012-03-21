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
	 * @param $key
	 */
	function is($key) {
		
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