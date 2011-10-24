<?php
/**
 * 
 * Support class for models to work with with a Solar_Cache object.
 * 
 * This cache works slightly differently from "normal" caches, in that it
 * tracks a version number for the data in the source table. When you "delete"
 * the cache, what really happens is that the version number increases.  This
 * makes it particularly effective for memcache and other memory-based
 * caches, where old entries simply "drop out" when there's no more room.
 * 
 * This cache is not recommended for file-based caching. If you use
 * a file-based cache here, be prepared to clear out old data versions on
 * your own, as this system will not do it for you.
 * 
 * For background information on cache versioning, see the blog entry at
 * <http://blog.leetsoft.com/2007/5/22/the-secret-to-memcached>.
 * 
 * @category Solar
 * 
 * @package Solar_Sql_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Cache.php 4097 2009-09-25 02:13:15Z pmjones $
 * 
 */
class Solar_Sql_Model_Cache extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency cache A Solar_Cache dependency.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Model_Cache = array(
        'cache'  => array('adapter' => 'Solar_Cache_Adapter_None'),
    );
    
    /**
     * 
     * The cache object for model data.
     * 
     * @var Solar_Cache_Adapter
     * 
     */
    protected $_cache;
    
    /**
     * 
     * The model this cache is working with.
     * 
     * @var Solar_Sql_Model
     * 
     */
    protected $_model;
    
    /**
     * 
     * The SQL connection cache-key prefix; needed for deconfliction when
     * there are multiple SQL connections.
     * 
     * @var string
     * 
     */
    protected $_prefix;
    
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
        $this->_cache = Solar::dependency(
            'Solar_Cache',
            $this->_config['cache']
        );
    }
    
    /**
     * 
     * Sets the model this cache will work with; picks up the SQL cache key
     * prefix along with it.
     * 
     * @param Solar_Sql_Model $model The model this cache will work with.
     * 
     * @return void
     * 
     */
    public function setModel($model)
    {
        $this->_model = $model;
        $this->_prefix = $model->sql->getCacheKeyPrefix();
    }
    
    /**
     * 
     * Deletes the cache for this model.
     * 
     * Technically, this just increases the data version number.  This means
     * that older versions will no longer be valid, causing a cache miss.
     * 
     * The version entry is keyed under `$prefix/model/$model_name/data_version`.
     * 
     * @return void
     * 
     */
    public function delete()
    {
        $key = $this->_prefix
             . "/model"
             . "/{$this->_model->model_name}"
             . "/data_version";
        
        $this->_cache->increment($key);
    }
    
    /**
     * 
     * Deletes the cache for this model and all related models.
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
        $this->delete();
        foreach ($this->_model->related as $name => $info) {
            $model = $this->_model->getRelated($name)->getModel();
            $model->cache->delete();
        }
    }
    
    /**
     * 
     * Gets the key for a cache entry based on fetch parameters for a select.
     * 
     * The entry is keyed under `$prefix/model/$model_name/data/$version/$hash`,
     * where $hash is an MD5 hash of the serialized parameters.
     * 
     * If the params include a `cache_key` entry, that value is used instead
     * of $hash.
     * 
     * @param array $fetch The fetch parameters for a select.
     * 
     * @return string The versioned cache entry key.
     * 
     */
    public function entry(Solar_Sql_Model_Params_Fetch $fetch)
    {
        $version = (int) $this->_fetchVersion();
        
        if ($fetch['cache_key']) {
            $key = $fetch['cache_key'];
        } else {
            $array = $fetch->toArray();
            unset($array['cache']);
            unset($array['cache_key']);
            unset($array['count_pages']);
            $key = hash('md5', serialize($array));
        }
        
        $key = $this->_prefix
             . "/model"
             . "/{$this->_model->model_name}"
             . "/data"
             . "/$version"
             . "/$key";
        
        return $key;
    }
    
    /**
     * 
     * Fetches the data for a cache entry.
     * 
     * @param string $key The cache entry key.
     * 
     * @return mixed Boolean false if the fetch failed (cache miss), 
     * otherwise the result of the fetch (cache hit).
     * 
     * @see Solar_Cache_Adapter::fetch()
     * 
     */
    public function fetch($key)
    {
        return $this->_cache->fetch($key);
    }
    
    /**
     * 
     * Adds data to the cache under a specified key.  Note that this is a
     * race-condition safe add, not a save.
     * 
     * @param string $key The cache entry key.
     * 
     * @param mixed $data The data to add to the cache.
     * 
     * @return bool True on success, false on failure.
     * 
     * @see Solar_Cache_Adapter::add()
     * 
     */
    public function add($key, $data)
    {
        return $this->_cache->add($key, $data);
    }
    
    /**
     * 
     * Fetches the current model data version from the cache.
     * 
     * The entry is keyed under `$prefix/model/$model_name/data_version`.
     * 
     * @return int The model data version.
     * 
     */
    protected function _fetchVersion()
    {
        $key = $this->_prefix
             . "/model"
             . "/{$this->_model->model_name}"
             . "/data_version";
        
        $result = $this->_cache->fetch($key);
        return $result;
    }
}