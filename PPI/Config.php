<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   Config
 * @link      wwww.ppiframework.com
 */
namespace PPI;
use Config\ConfigException;
use PPI\File;
class Config {

	/**
	 * The config object doing the parsing
	 *
	 * @var null|object
	 */
	protected $_parser    = null;

	/**
	 * The configuration options
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * Initialise the config object
	 *
	 * Will check the file extension of your config filename and load up a specific parser
	 * 
	 * @param array $options The options
	 */
	function __construct(array $options = array()) {
		$this->_options = $options;
	}

	/**
	 * Get the current set config object
	 *
	 * @return object
	 */
	function getConfig() {

		if($this->_parser === null) {
			if(isset($this->_options['cacheConfig']) && $this->_options['cacheConfig']) {
				$this->_parser = $this->cacheConfig();
			} else {
				$this->_parser = $this->parseConfig();
			}
		}
		return $this->_parser;
	}

	/**
	 * Get a cached version of the framework, if no cached version exists it parses the config and caches it for you.
	 *
	 * @throws PPI_Exception
	 * @return void
	 */
	function cacheConfig() {

		if(!isset($this->_options['configCachePath'])) {
			throw new ConfigException('Missing path to the config cache path');
		}

		$path = sprintf('%s%s.%s.cache',
			$this->_options['configCachePath'],
			$this->_options['configFile'],
			$this->_options['configBlock']);

		if(file_exists($path)) {
			return unserialize(file_get_contents($path));
		}
		$config = $this->parseConfig();
		file_put_contents($path, serialize($config));
		return $config;
	}

	/**
	 * Parse the config file
	 *
	 * @return object
	 */
	function parseConfig() {

		// Make sure our config file exists
		if(!file_exists(CONFIGPATH . $this->_options['configFile'])) {
			die('Unable to find <b>'. CONFIGPATH . $this->_options['configFile'] .'</b> file, please check your application configuration');
		}

		// Switch the file extension
		switch(File::getFileExtension($this->_options['configFile'])) {
			case 'ini':
				return new Config\Ini(parse_ini_file(CONFIGPATH . $this->_options['configFile'], true, INI_SCANNER_RAW), $this->_options['configBlock']);

			case 'xml':
				throw new ConfigException('XML Config Object Not Yet Created.');
				break;

			case 'php':
				throw new ConfigException('PHP Config Object Not Yet Created.');
				break;
			
			case 'yaml':
				throw new ConfigException('YAML Config Object Not Yet Created.');
				break;

		}
	}

}
