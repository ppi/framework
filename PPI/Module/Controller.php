<?php
namespace PPI\Module;

class Controller {
	
	protected $_request = null;
	
	protected $_response = null;
	
	function setRequest($request) {
		$this->_request = $request;
		return $this;
	}
	
	function setResponse($response) {
		$this->_response = $response;
		return $this;
	}
	
	function getRequest() {
		return $this->_request;
	}
	
	function getResponse() {
		return $this->_response;
	}
	
}