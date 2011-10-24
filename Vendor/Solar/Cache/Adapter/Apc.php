<?php
/**
 * 
 * APC cache controller.
 * 
 * Requires APC 3.0.13 or later.
 * 
 * The Alternative PHP Cache (APC) is a free and open opcode cache for PHP.
 * It was conceived of to provide a free, open, and robust framework for
 * caching and optimizing PHP intermediate code.
 * 
 * The APC extension is not bundled with PHP; you will need to install it
 * on your server before you can use it. You can read more about it at the
 * [APC homepage](http://pecl.php.net/package/apc).
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Apc.php 4619 2010-06-21 15:56:46Z pmjones $
 * 
 */
class Solar_Cache_Adapter_Apc extends Solar_Cache_Adapter
{
    /**
     * 
     * Checks to make sure the APC extension is available.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        if (! ( extension_loaded('apc') && ini_get('apc.enabled') ) ) {
            throw $this->_exception('ERR_EXTENSION_NOT_LOADED', array(
                'extension' => 'apc',
            ));
        }
    }
    
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
        
        Solar::dump($life, 'life');
        
        // save to apc
        return apc_store($key, $data, $life);
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
        
        // add to apc
        return apc_add($key, $data, $life);
    }
    
    /**
     * 
     * Gets cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed Boolean false on failure, cache data on success.
     * 
     */
    public function fetch($key)
    {
        if (! $this->_active) {
            return;
        }
        
        // modify the key to add the prefix
        $key = $this->entry($key);
        
        // get from apc
        return apc_fetch($key);
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
        
        // fetch the current value
        $val = $this->fetch($key);
        
        // increment and save
        $val += $amt;
        $this->save($key, $val);
        
        // re-fetch in case someone else incremented in the interim
        $val = $this->fetch($key);
        
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
        
        // remove from apc
        apc_delete($key);
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
        if (! $this->_active) {
            return;
        }
        
        apc_clear_cache('user');
    }
}
