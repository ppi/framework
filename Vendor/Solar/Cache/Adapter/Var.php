<?php
/**
 * 
 * Variable (in-memory) cache controller.
 * 
 * Stores cache entries to an object variable.  This means that entries are
 * available for the duration of the script, but are cleared out at the end
 * of the script.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Var.php 4619 2010-06-21 15:56:46Z pmjones $
 * 
 */
class Solar_Cache_Adapter_Var extends Solar_Cache_Adapter
{
    /**
     * 
     * Cache entries.
     * 
     * @var array
     * 
     */
    protected $_entries = array();
    
    /**
     * 
     * Expiration timestamps for each cache entry.
     * 
     * @var array
     * 
     */
    protected $_expires = array();
    
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
        
        // save entry and expiry
        $this->_entries[$key] = $data;
        $this->_expires[$key] = time() + $life;
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
     * @return bool True on success, false on failure.
     * 
     */
    public function add($key, $data, $life = null)
    {
        if (! $this->_active) {
            return;
        }
        
        // modify the key to add the prefix
        $modkey = $this->entry($key);
        
        // save entry, but only if it doesn't already exist
        if (empty($this->_entries[$modkey])) {
            // use the original key here, because save() will add the prefix
            return $this->save($key, $data, $life);
        } else {
            return false;
        }
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
        
        // does it exist?
        $exists = array_key_exists($key, $this->_entries);
        if (! $exists) {
            return false;
        }
        
        // has it expired?
        if ($this->_isExpired($key)) {
            // clear the entry
            unset($this->_entries[$key]);
            unset($this->_expires[$key]);
            return false;
        }
        
        // return the value
        return $this->_entries[$key];
    }
    
    /**
     * 
     * Checks if a file has expired (is past its lifetime) or not.
     * 
     * If lifetime is empty (zero), then the file never expires.
     * 
     * @param string $key The entry key with prefix already added.
     * 
     * @return bool
     * 
     */
    protected function _isExpired($key)
    {
        // is life set as "forever?"
        if (! $this->_life) {
            return false;
        }
        
        // is it past its expiration date?
        if (time() >= $this->_expires[$key]) {
            return true;
        }
        
        // not expired yet
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
        
        // increment it
        $this->_entries[$key] += $amt;
        
        // done!
        return $this->_entries[$key];
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
        
        // remove entry and expiry
        unset($this->_entries[$key]);
        unset($this->_expires[$key]);
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
        
        $this->_entries = array();
        $this->_expires = array();
    }
}
