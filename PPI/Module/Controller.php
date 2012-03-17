<?php
namespace PPI\Module;

class Controller {
	
	/**
	 * The controller's request object
	 * 
	 * @var null
	 */
	protected $_request = null;
	
	/**
	 * The controllers' response object
	 * 
	 * @var null
	 */
	protected $_response = null;
	
	/**
	 * Service Locator
	 * 
	 * @var null|object
	 */
	protected $_serviceLocator = null;
	
	/**
	 * Get the request object
	 * 
	 * @return null
	 */
	function getRequest() {
		return $this->_serviceLocator->get('request');
	}
	
	/**
	 * Get thre response object
	 * 
	 * @return null
	 */
	function getResponse() {
		return $this->_serviceLocator->get('response');
	}
	
	/**
	 * Set the service locator
	 * 
	 * @param object $locator
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
	
	protected function render($template, array $params = array(), array $options = array()) {
		return $this->_serviceLocator->get('templating')->render($template, $params);
	}
	
}