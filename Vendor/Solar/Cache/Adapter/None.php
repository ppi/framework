<?php
/**
 * 
 * The cache of no-cache.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: None.php 4442 2010-02-26 16:33:06Z pmjones $
 * 
 */
class Solar_Cache_Adapter_None extends Solar_Cache_Adapter
{
    /**
     * 
     * Sets cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @param mixed $data The data to write into the entry.
     * 
     * @param int $life A custom lifespan, in seconds, for the entry; if null,
     * uses the default lifespan for the adapter instance.
     * 
     * @return true Always reports a successsful save.
     * 
     */
    public function save($key, $data, $life = null)
    {
        if (! $this->_active) {
            return;
        }
        
        return true;
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
     * @return true Always reports a successsful add.
     * 
     */
    public function add($key, $data, $life = null)
    {
        if (! $this->_active) {
            return;
        }
        
        return true;
    }
    
    /**
     * 
     * Gets cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @return true Always reports a failed fetch.
     * 
     */
    public function fetch($key)
    {
        if (! $this->_active) {
            return;
        }
        
        return false;
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
     * @return void Never increments.
     * 
     */
    public function increment($key, $amt = 1)
    {
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
    }
    
    /**
     * 
     * Removes all cache entries.
     * 
     * Note that APC makes a distinction between "user" entries and
     * "system" entries; this only deletes the "user" entries.
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
    }
    
    /**
     * 
     * Returns the name for the entry key.
     * 
     * @param string $key The entry ID.
     * 
     * @return void Never caches, so never has a key.
     * 
     */
    public function entry($key)
    {
    }
}
