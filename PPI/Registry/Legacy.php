<?php
namespace PPI\Registry;
class Legacy {

	function set($key, $val) {
		return PPI_Registry::set($key, $val);
	}

	function get($key, $default = null) {
		return PPI_Registry::get($key, $default);
	}

	function exists($key) {
		return PPI_Registry::exists($key);
	}

	function remove($key) {
		return PPI_Registry::remove($key);
	}



}