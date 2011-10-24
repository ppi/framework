<?php
/**
 * 
 * Xcache cache controller.
 * 
 * Xcache is a fast, stable PHP opcode cacher tested and supported on
 * all of the latest PHP cvs branches.
 * 
 * The Xcache extension is not bundled with PHP; you will need to
 * install it on your server before you can use it. More info on the
 * [Xcache homepage](http://trac.lighttpd.net/xcache/wiki/).
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Rodrigo Moraes <rodrigo.moraes@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Xcache.php 4619 2010-06-21 15:56:46Z pmjones $
 * 
 * @todo Does not work with objects.  Need to add custom support for them.
 * <http://trac.lighttpd.net/xcache/wiki/XcacheApi>
 * 
 */
class Solar_Cache_Adapter_Xcache extends Solar_Cache_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string user Admin user name for Xcache, as set in php.ini. This login
     *   and the corresponding password are required _only_ for the deleteAll()
     *   method. Defaults to `null`.
     * 
     * @config string pass Plaintext password that matches the MD5-encrypted password
     *   in php.ini. This password and the corresponding login are required
     *   _only_ for the deleteAll() method. Defaults to `null`.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache_Adapter_Xcache = array(
        'user' => null,
        'pass' => null
    );
    
    /**
     * 
     * Checks to make sure the XCache extension is available.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        if (! (extension_loaded('xcache') && ini_get('xcache.cacher'))) {
            throw $this->_exception('ERR_EXTENSION_NOT_LOADED', array(
                'extension' => 'xcache'
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
        
        // save in xcache
        return xcache_set($key, $data, $life);
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
        
        // don't save if already there
        if (xcache_isset($key)) {
            return false;
        }
        
        // add to xcache
        return xcache_set($key, $data, $life);
    }
    
    /**
     * 
     * Gets cache entry data.
     * 
     * @param string $key The entry ID.
     * 
     * @return mixed NULL on failure, cache data on success.
     * 
     */
    public function fetch($key)
    {
        if (! $this->_active) {
            return;
        }
        
        // modify the key to add the prefix
        $key = $this->entry($key);
        
        // get from xcache
        return xcache_get($key);
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
        
        // let xcache do the increment and retain its value
        $val = xcache_inc($key, $amt, $this->_life);
        
        // done
        return $val;
    }
    
    /**
     * 
     * Deletes a cache entry.
     * 
     * @param string $key The entry ID.
     * 
     * @return bool true on successful deletion, false on failure
     * 
     */
    public function delete($key)
    {
        if (! $this->_active) {
            return;
        }
        
        // modify the key to add the prefix
        $key = $this->entry($key);
        
        // remove from xcache
        return xcache_unset($key);
    }
    
    /**
     * 
     * Removes all cache entries.
     * 
     * Note that Xcache makes a distinction between "user" entries and
     * "system" or "script" entries; this deletes only "user" entries.
     * 
     * @return bool true on success, false on failure
     * 
     */
    public function deleteAll()
    {
        if (! $this->_active) {
            return;
        }
        
        // store creds current state
        $olduser = null;
        $oldpass = null;
        
        // we need to work with actual PHP superglobal $_SERVER here,
        // instead of a Solar_Request::$server value, because the Xcache
        // extension doesn't know about Solar_Request.
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $olduser = $_SERVER['PHP_AUTH_USER'];
        }
        
        if (isset($_SERVER['PHP_AUTH_PW'])) {
            $oldpass = $_SERVER['PHP_AUTH_PW'];
        }
        
        // force credentials to the configured values
        $_SERVER['PHP_AUTH_USER'] = $this->_config['user'];
        $_SERVER['PHP_AUTH_PW'] = $this->_config['pass'];
        
        // clear user cache
        $vcnt = xcache_count(XC_TYPE_VAR);
        for ($i = 0; $i < $vcnt; $i++) {
            if (! xcache_clear_cache(XC_TYPE_VAR, $i)) {
                return false;
            }
        }
        
        // Restore creds to prior state
        if ($olduser !== null) {
            $_SERVER['PHP_AUTH_USER'] = $olduser;
        } else {
            $_SERVER['PHP_AUTH_USER'] = null;
        }
        
        if ($oldpass !== null) {
            $_SERVER['PHP_AUTH_PW'] = $oldpass;
        } else {
            $_SERVER['PHP_AUTH_PW'] = null;
        }
        
        return true;
    }
}
