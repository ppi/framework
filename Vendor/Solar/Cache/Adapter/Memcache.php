<?php
/**
 * 
 * Memcache cache controller.
 * 
 * This adapter lets you connect to a
 * [memcached](http://www.danga.com/memcached/) server, which uses system
 * memory to cache data. In general, you never need to instantiate it 
 * yourself; instead, use Solar_Cache as the frontend for it and specify
 * 'Solar_Cache_Memcache' in the config keys as the 'adapter' value.
 * 
 * This kind of cache is extremely fast, especially when on the same
 * server as the web process, although it may also be accessed via
 * network.  This particular adapter uses the PHP [[php::memcache | ]]
 * extension to manage the cache connection.  The extension is not
 * bundled with PHP; you will need to follow the
 * [installation instructions](http://php.net/memcache) before you can
 * use it.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Memcache.php 4618 2010-06-21 15:15:19Z pmjones $
 * 
 */
class Solar_Cache_Adapter_Memcache extends Solar_Cache_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string host The memcached host name, default 'localhost'.
     * 
     * @config int port The memcached port number, default 11211.
     * 
     * @config int timeout The timeout before the server connection is
     *   considered a miss, in seconds.  Default is 1 second, and should 
     *   not really be changed for reasons other than testing purposes.
     * 
     * @config array pool An array of memcache connections to connect to in a 
     *   multi-server pool. Each connection should be represented by an array
     *   with the following keys: `host`, `port`, `persistent`, `weight`, 
     *   `timeout`, `retry_interval`, `status` and `failure_callback`.
     *   The `pool` is empty by default, and will only be used instead of 
     *   a single-server connection if non-empty.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache_Adapter_Memcache = array(
        'host' => 'localhost',
        'port' => 11211,
        'timeout' => 1,
        'pool' => array(),
    );
    
    /**
     * 
     * Default configuration for a pool server node.
     * 
     * `host`
     * : (string) The memcached host name, default 'localhost'.
     * 
     * `port`
     * : (int) The memcached port number, default 11211.
     * 
     * `persistent`
     * : (bool) Controls the use of a persistent connection, default **TRUE**.
     * 
     * `weight`
     * : (int) Number of buckets to create for this server, which in turn 
     *   controls its probability of being selected. The probability is 
     *   relative to the total weight of all servers. Default 1.
     * 
     * `timeout`
     * : (int) Value in seconds which will be used for connecting to the 
     *   daemon. Default 1.
     * 
     * `retry_interval`
     * : (int) Controls how often a failed server will be retried. Default is
     *   15 seconds. A setting of -1 disables automatic retry.
     * 
     * `status`
     * : (bool) Controls if the server should be flagged as online. Setting 
     *   this parameter to **FALSE** and `retry_interval` to -1 allows a failed
     *   server to be kept in the pool so as not to affect the key distribution
     *   algorithm. Requests for this server will then failover or fail
     *   immediately depending on the *memcache.allow_failover* php.ini setting.
     *   Defaults to **TRUE**, meaning the server should be considered online.
     * 
     * `failure_callback`
     * : (callback) Allows specification of a callback function to run upon
     *   encountering a connection error. The callback is run before 
     *   failover is attempted, and takes two parameters: the hostname and port
     *   of the failed server. Default is null.
     * 
     * @var array
     * 
     */
    protected $_pool_node = array(
        'host'              => 'localhost',
        'port'              => 11211,
        'persistent'        => true,
        'weight'            => 1,
        'timeout'           => 1,
        'retry_interval'    => 1,
        'status'            => true,
        'failure_callback'  => null,        
    );
    
    
    /**
     * 
     * A memcache client object.
     * 
     * @var object
     * 
     */
    public $memcache;
    
    /**
     * 
     * Checks to make sure the memcache extension is available.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        if (! extension_loaded('memcache')) {
            throw $this->_exception('ERR_EXTENSION_NOT_LOADED', array(
                'extension' => 'memcache',
            ));
        }
    }
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        $this->memcache = new Memcache;
        
        // pool or single-server connection?
        if (empty($this->_config['pool'])) {
            
            // make sure we can connect
            $result = @$this->memcache->connect(
                $this->_config['host'],
                $this->_config['port'],
                $this->_config['timeout']
            );
        
            if (! $result) {
                throw $this->_exception('ERR_CONNECTION_FAILED', array(
                    'host'    => $this->_config['host'],
                    'port'    => $this->_config['port'],
                    'timeout' => $this->_config['timeout'],
                ));
            }
            
        } else {
            
            // set up a pool
            $this->_createPool();
        }
    }
    
    /**
     * 
     * Updates or inserts cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @param mixed $data The data to write into the entry.
     * 
     * @param int $life A custom lifespan, in seconds, for the entry; if null,
     * uses the default lifespan for the adapter instance.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function save($key, $data, $life = null)
    {
        if (! $this->_active) {
            return;
        }
        
        // modify the key to add the prefix
        $key = $this->entry($key);
        
        // life value
        if ($life === null) {
            $life = $this->_life;
        }
        
        // store in memcache
        return $this->memcache->set($key, $data, null, $life);
    }
    
    /**
     * 
     * Inserts cache entry data, but only if the entry does not already exist.
     * 
     * @param string $key The entry ID.
     * 
     * @param mixed $data The data to write into the entry.
     * 
     * @param int $life A custom lifespan, in seconds, for the entry; if null,
     * uses the default lifespan for the adapter instance.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function add($key, $data, $life = null)
    {
        if (! $this->_active) {
            return;
        }
        
        // modify the key to add the prefix
        $key = $this->entry($key);
        
        // life value
        if ($life === null) {
            $life = $this->_life;
        }
        
        // add to memcache
        return $this->memcache->add($key, $data, null, $life);
    }
    
    /**
     * 
     * Gets cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed Boolean false on failure, string on success.
     * 
     */
    public function fetch($key)
    {
        if (! $this->_active) {
            return;
        }
        
        // modify the key to add the prefix
        $key = $this->entry($key);
        
        // get from memcache
        return $this->memcache->get($key);
    }
    
    /**
     * 
     * Increments a cache entry value by the specified amount.  If the entry
     * does not exist, creates it at zero, then increments it.
     * 
     * @param string $key The entry ID.
     * 
     * @param string $amt The amount to increment by (default +1).  Using
     * negative values is effectively a decrement.
     * 
     * @return int The new value of the cache entry.
     * 
     */
    public function increment($key, $amt = 1)
    {
        if (! $this->_active) {
            return;
        }
        
        // make sure we have a key to increment (the add() method adds the 
        // prefix on its own, so no need to use entry() here)
        $this->add($key, 0, null, $this->_life);
        
        // modify the key to add the prefix
        $key = $this->entry($key);
        
        // let memcache do the increment and retain its value
        $val = $this->memcache->increment($key, $amt);
        
        // done
        return $val;
    }
    
    /**
     * 
     * Deletes a cache entry.
     * 
     * @param string $key The entry ID.
     * 
     * @return void
     * 
     */
    public function delete($key)
    {
        if (! $this->_active) {
            return;
        }
        
        // modify the key to add the prefix
        $key = $this->entry($key);
        
        // remove from memcache
        $this->memcache->delete($key);
    }
    
    /**
     * 
     * Removes all cache entries.
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
        if (! $this->_active) {
            return;
        }
        
        $this->memcache->flush();
    }
    
    /**
     * 
     * Adds servers to a memcache connection pool from configuration.
     * 
     * @return void
     * 
     */
    protected function _createPool()
    {
        $connection_count = 0;
        
        foreach ($this->_config['pool'] as $server) {
            // set all defaults
            $server = array_merge($this->_pool_node, $server);
            
            // separate addServer calls in case failure_callback is 
            // empty
            if (empty($server['failure_callback'])) {
                $result = $this->memcache->addServer(
                    (string) $server['host'],
                    (int)    $server['port'],
                    (bool)   $server['persistent'],
                    (int)    $server['weight'],
                    (int)    $server['retry_interval'],
                    (bool)   $server['status']
                );
                                
            } else {
                $result = $this->memcache->addServer(
                    (string) $server['host'],
                    (int)    $server['port'],
                    (bool)   $server['persistent'],
                    (int)    $server['weight'],
                    (int)    $server['retry_interval'],
                    (bool)   $server['status'],
                             $server['failure_callback']
                );
            }
            
            // Did connection to the last node succeed?
            if ($result === true) {
                $connection_count++;
            }
        
        }
        
        // make sure we connected to at least one
        if ($connection_count < 1) {
            $info = $this->_config['pool'];
            throw $this->_exception('ERR_CONNECTION_FAILED', $info);
        }
    }
}
