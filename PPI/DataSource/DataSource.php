<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppi.io
 */
namespace PPI\DataSource;

use PPI\Autoload;

class DataSource implements DataSourceInterface {

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
	 * @return PPI\DataSource\DataSource
	 */
	public static function create(array $options = array()) {
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
	 * Create an active query driver connection
	 * 
	 * @param string $type The type of driver to use for the active query factory
	 * @param array $options Options to be passed to the active query driver
	 * @return PDO\ActiveQuery
	 * @throws \InvalidArgumentException
	 */
	public function activeQueryFactory($type, array $options) {
		
		switch($type) {
			
			case 'mongodb':
				throw new \InvalidArgumentException('Invalid activeQueryFactory type. MongoDB not yet implemented');
				break;
			
			case 'couchdb':
				throw new \InvalidArgumentException('Invalid activeQueryFactory type. CouchDB not yet implemented');
				break;
			
			case 'pdo':
			default:
				return new \PPI\DataSource\PDO\ActiveQuery($options);

		}
		
		
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
			throw new \Exception('Invalid DataSource Key: ' . $key);
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
	 * @return array
	 */
	function getConnectionConfig($key) {
		
		if(isset($this->_config[$key])) {
			return $this->_config[$key];
		}
		
		throw new \InvalidArgumentException('DataSource Connection Key: ' . $key . ' does not exist');
		
	}

}