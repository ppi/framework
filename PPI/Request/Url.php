<?php
namespace PPI\Request;
class Url extends RequestAbstract {

	/**
	 * Constructor
	 *
	 * If data is supplied then that data is used, otherwise we automatically
	 *
	 * @param array $data Supplied Data
	 */
	public function __construct($data = null) {
		if (is_string($data)) {
			$this->_array = $this->processUriParams($data);
		} elseif (is_array($data)) {
			$this->_array = $data;
		}

		$this->_isCollected = false;
	}

	/**
	 * Process the URI Parameters into a clean hashmap for isset() calling later.
	 *
	 * @param string $uri The URI
	 * @return array
	 */
	protected function processUriParams($uri) {

		$params    = array();
		$uriParams = explode('/', trim($uri, '/'));
		$count     = count($uriParams);
		for($i = 0; $i < $count; $i++) {
			$val = isset($uriParams[($i + 1)]) ? $uriParams[($i + 1)] : null;
			$params[$uriParams[$i]] = urldecode(is_numeric($val) ? $val : $val);
		}

		return $params;
	}
}