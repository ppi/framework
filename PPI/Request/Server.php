<?php
namespace PPI\Request;
class Server extends RequestAbstract {
	/**
	 * Constructor
	 *
	 * Takes in an optional $server variable otherwise defaulting to $_SERVER
	 *
	 * @param array $server
	 */
	public function __construct(array $server = null) {
		if($server !== null) {
			$this->_isCollected = false;
			$this->_array = $server;
		} else {
			$this->_array = $_SERVER;
		}
	}
}