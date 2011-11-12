<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppi.io
 */
namespace PPI;
use PPI\Autoload;
class DataSource {

	/**
	 * List of configuration sets
	 * 
	 * @var array
	 */
	protected $_config = array();
	
	/**
	 * List of connections to return via singleton-like
	 * 
	 * @var array
	 */
	protected $_handles = array();

	/**
	 * The constructor, taking in options which are currently
	 * 
	 * @param array $options
	 */
	function __construct(array $options = array()) {
		$this->_config = $options;
	}
	
	/**
	 * Create a new instance of ourself.
	 * 
	 * @static
	 * @param array $options
	 * @return PPI_DataSource
	 */
	static function create(array $options = array()) {
		return new self($options);
	}

	/**
	 * The DataSource Factory - this is where we manufacture our drivers
	 * 
	 * @throws DataSourceException
	 * @param string $key
	 * @return object
	 */
	function factory(array $options) {

		// Apply our default prefix
		if(!isset($options['prefix'])) {
			$options['prefix'] = 'PPI\\DataSource\\';
		}

		// Lets get our suffix, to load up the right adapter, (PPI\DataSource\[PDO|Mongo])
		if($options['type'] === 'mongo') {
			$suffix = 'Mongo';
		} elseif(substr($options['type'], 0, 4) === 'pdo_') {
			$suffix = 'PDO';
		} else {
			$suffix = $options['type'];
		}

		// Lets instantiate up and get our  driver
		$adapterName = $options['prefix'] . $suffix;
		$adapter     = new $adapterName();
		$driver      = $adapter->getDriver($options);

		return $driver;
	}
	
	/**
	 * Return the connection from the factory
	 * 
	 * @throws DataSourceException
	 * @param string $key
	 * @return object
	 */
	function getConnection($key) {
		
		// Connection Caching
		if(isset($this->_handles[$key])) {
			return $this->_handles[$key];
		}
		
		// Check that we asked for a valid key
		if(!isset($this->_config[$key])) {
			throw new \PPI\DataSource\DataSourceException('Invalid DataSource Key: ' . $key);
		}
		
		if(!Autoload::exists('Doctrine')) {
			Autoload::add('Doctrine', array('path' => VENDORPATH . 'Doctrine'));
		}
		
		$conn = $this->factory($this->_config[$key]);
		
		// Connection Caching
		$this->_handles[$key] = $conn;

		return $conn;
	}
	
	/**
	 * Get the connection configuration options for the specified key
	 * 
	 * @param string $key
	 * @todo maybe throw an exception if $key doesn't exist.
	 * @return array
	 */
	function getConnectionConfig($key) {
		return isset($this->_config[$key]) ? $this->_config[$key] : array();
	}

}