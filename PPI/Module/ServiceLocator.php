<?php
namespace PPI\Module;
class ServiceLocator {
	
	protected $_services = array();
	
	function __construct(array $services = array()) {
		if(!empty($services)) {
			$this->_services = $services;
		}
	}
	
	function get($key) {
		return isset($this->_services[$key]) ? $this->_services[$key] : null;
	}
	
	function set($key, $service) {
		$this->_services[$key] = $service;
	}
	
	function has($key) {
		return isset($this->_services[$key]);
	}
	
}
