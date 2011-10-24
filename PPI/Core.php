<?php
namespace PPI;

use PPI\Registry\Exception\EntryNotFound;

class Core {

	/**
	 * Get the data source object from the registry
	 * 
	 * @static
	 * @throws Registry\Exception\EntryNotFound
	 * @return mixed
	 */
	static function getDataSource() {

		if(!Registry::exists('DataSource')) {
			throw new EntryNotFound('Entry DataSource Not Found In The Registry.');
		}
		return Registry::get('DataSource');
	}
	
	/**
	 * Get a data source connection
	 * 
	 * @static
	 * @param string $key
	 * @return object
	 */
	static function getDataSourceConnection($key) {
		return self::getDataSource()->getConnection($key);
	}

	/**
	 * Get the session object
	 * 
	 * @static
	 * @param null $options
	 * @return mixed
	 */
	static function getSession($options = null) {

		if ($options !== null && !is_array($options)) {
			$config  = Registry::get('PPI_Config');
			$options = isset($config->session) ? $config->session->toArray() : array();
			$options = $options;
		}
		$session = Registry::get('PPI_Session');
		if(is_array($options)) {
			$session->defaults($options);
		}
		return $session;
	}
	
	/**
	 * Get the cache object
	 * 
	 * @static
	 * @param null $options
	 * @return Cache
	 */
	static function getCache($options = null) {

		if (!is_array($options)) {
			$config  = Registry::get('PPI_Config');
			$options = isset($config->cache) ? $config->cache->toArray() : array();
			if (is_string($options) && $options !== '') {
				$options['handler'] = $options;
			}
			$options = $options;
		}
		return new Cache($options);
	}

	/**
	 * Get the config object from the registry
	 * 
	 * @static
	 * @throws Registry\Exception\EntryNotFound
	 * @return object
	 */
	static function getConfig() {

		if(!Registry::exists('PPI_Config')) {
			throw new EntryNotFound('Entry PPI_Config Not Found In The Registry.');
		}
		return Registry::get('PPI_Config');
	}
	
	/**
	 * Get an object from the registry if it exists, if it does not then we set it for next time.
	 * 
	 * @static
	 * @param $class
	 * @return mixed
	 */
	static function getObjectFromRegistry($class) {

		if(!Registry::exists($class)) {
			$oClass = new $class();
			Registry::set($class, $oClass);
			return $oClass;
		}
		return Registry::get($class);
	}
	

	/**
	 * Get the dispatcher object
	 *
	 * @return object
	 */
	static function getDispatcher() {
		return Registry::get('PPI_Dispatch');
	}
	
	/**
	 * Get the router object.
	 * 
	 * @static
	 * @return mixed
	 */
	static function getRouter() {
		return Registry::get('PPI_Router');
	}
	
}
