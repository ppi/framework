<?php
/**
 * 
 * File-based cache controller.
 * 
 * This is the file-based adapter for [Solar_Cache:HomePage Solar_Cache].
 * In general, you never need to instantiate it yourself; instead,
 * use Solar_Cache as the frontend for it and specify
 * 'Solar_Cache_File' as the 'adapter' config key value.
 * 
 * If you specify a path (for storing cache entry files) that does
 * not exist, this adapter attempts to create it for you.
 * 
 * This adapter always uses [[php::flock() | ]] when reading and writing
 * cache entries; it uses a shared lock for reading, and an exclusive
 * lock for writing.  This is to help cut down on cache corruption
 * when two processes are trying to access the same cache file entry,
 * one for reading and one for writing.
 * 
 * In addition, this adapter automatically serializes and unserializes
 * arrays and objects that are stored in the cache.  This means you
 * can store not only string output, but also array data and entire
 * objects in the cache ... just like Solar_Cache_Memcache.
 * 
 * @category Solar
 * 
 * @package Solar_Cache
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Clay Loveless <clay@killersoft.com> Streams awareness.
 * 
 * @author Jeff Moore <jeff@procata.com> Cache-corruption avoidance and speed
 * enhancements.
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: File.php 4573 2010-05-15 21:15:37Z pmjones $
 * 
 * @todo Add CRC32 to check for cache corruption?
 * 
 */
class Solar_Cache_Adapter_File extends Solar_Cache_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string path The directory where cache files are located; should be
     *   readable and writable by the script process, usually the web server
     *   process. Default is '/Solar_Cache_File' in the system temporary
     *   directory.  Will be created if it does not already exist.  Supports
     *   streams, so you may specify (e.g.) 'http://cache-server/' as the 
     *   path.
     * 
     * @config int mode If the cache path does not exist, when it is created, use
     *   this octal permission mode.  Default is `0740` (user read/write/exec,
     *   group read, others excluded).
     * 
     * @config array|resource context A stream context resource, or an array to pass to
     *   stream_create_context(). When empty, no context is used.  Default
     *   null.
     * 
     * @config bool hash Whether or not to hash the cache entry filenames.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cache_Adapter_File = array(
        'path'    => null, // default set in constructor
        'mode'    => 0740,
        'context' => null,
        'hash'    => true,
    );
    
    /**
     * 
     * Path to the cache directory.
     * 
     * @var string
     * 
     */
    protected $_path;
    
    /**
     * 
     * Whether or not to hash key names.
     * 
     * @var bool
     * 
     */
    protected $_hash;
    
    /**
     * 
     * A stream context resource to define how the input/output for the cache
     * is handled.
     * 
     * @var resource
     * 
     */
    protected $_context;
    
    /**
     * 
     * Sets the default cache directory location.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        $tmp = Solar_Dir::tmp('/Solar_Cache_File/');
        $this->_Solar_Cache_Adapter_File['path'] = $tmp;
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
        
        // path to storage; include the prefix as part of the path
        $this->_path = Solar_Dir::fix($this->_config['path'] . '/' . $this->_prefix);
        
        // whether or not to hash
        $this->_hash = $this->_config['hash'];
        
        // build the context property
        if (is_resource($this->_config['context'])) {
            // assume it's a context resource
            $this->_context = $this->_config['context'];
        } elseif (is_array($this->_config['context'])) {
            // create from scratch
            $this->_context = stream_context_create($this->_config['context']);
        } else {
            // not a resource, not an array, so ignore.
            // have to use a resource of some sort, so create
            // a blank context resource.
            $this->_context = stream_context_create(array());
        }
        
        // make sure the cache directory is there; create it if
        // necessary.
        if (! is_dir($this->_path)) {
            mkdir($this->_path, $this->_config['mode'], true, $this->_context);
        }
    }
    
    /**
     * 
     * Inserts/updates cache entry data.
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
        
        // life value
        if ($life === null) {
            $life = $this->_life;
        }
        
        // keep some meta info
        $meta = array(
            'serial' => ! is_scalar($data),
            'expire' => ($life ? time() + $life : 0),
        );
        
        // serialize the data?
        if ($meta['serial']) {
            $data = serialize($data);
        }
        
        // what file should we write to?
        $file = $this->entry($key);
        
        // does the directory exist?
        $dir = dirname($file);
        if (! is_dir($dir)) {
            mkdir($dir, $this->_config['mode'], true, $this->_context);
        }
        
        // open data file for over-writing. not using file_put_contents 
        // becuase we need to write a meta file too (and avoid race
        // conditions while doing so). don't use include path. using ab+ is
        // much, much faster than wb.
        $fp = fopen($file, 'ab+', false, $this->_context);
        
        // was it opened?
        if (! $fp) {
            // could not open data file for writing.
            return false;
        }
        
        // set exclusive lock for writing.
        flock($fp, LOCK_EX);
        
        // empty whatever might be there and then write the data
        fseek($fp, 0);
        ftruncate($fp, 0);
        fwrite($fp, $data);
        
        // write meta while the data file is locked to avoid race conditions.
        $meta = serialize($meta);
        file_put_contents($file . '.meta', $meta, LOCK_EX, $this->_context);
        
        // release the lock
        fclose($fp);
        
        // done!
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
        
        // what file should we look for?
        $file = $this->entry($key);
        
        // is the key available for adding?
        $available = ! file_exists($file) ||
                     ! is_readable($file) ||
                     $this->_isExpired($file);
        
        if ($available) {
            return $this->save($key, $data, $life);
        }
        
        // key already exists
        return false;
    }
    
    /**
     * 
     * Fetches cache entry data.
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
        
        // get the entry filename *before* validating;
        // this avoids race conditions.
        $file = $this->entry($key);
        
        // make sure the file exists and is readable
        if (! file_exists($file) || ! is_readable($file)) {
            return false;
        }
        
        // make sure file is still within its lifetime
        if ($this->_isExpired($file)) {
            // expired, remove it
            $this->delete($key);
            return false;
        }
        
        // the file data, if we can open the file.
        $data = false;
        
        // file exists and is not expired; open it for reading
        $fp = @fopen($file, 'rb', false, $this->_context);
        
        // could it be opened?
        if ($fp) {
        
            // lock the file right away
            flock($fp, LOCK_SH);
            
            // get the cache entry data
            $data = stream_get_contents($fp);
            
            // get the meta info
            $meta = unserialize(file_get_contents("$file.meta"));
            if ($meta['serial']) {
                $data = unserialize($data);
            }
            
            // release the lock
            fclose($fp); 
        }
        
        // will be false if fopen() failed, otherwise is the file contents.
        return $data;
    }
    
    /**
     * 
     * Checks if a file has expired (is past its lifetime) or not.
     * 
     * If lifetime is empty (zero), then the file never expires.
     * 
     * @param string $file The file name.
     * 
     * @return bool
     * 
     */
    protected function _isExpired($file)
    {
        $meta = unserialize(file_get_contents("$file.meta"));
        return $meta['expire'] && $meta['expire'] <= time();
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
        
        // make sure we have a key to increment
        $this->add($key, '0', null, $this->_life);
        
        // what file should we write to?
        $file = $this->entry($key);
        
        // open the file for read/write.
        $fp = fopen($file, 'r+b', false, $this->_context);
        
        // was it opened?
        if (! $fp) {
            return false;
        }
        
        // set exclusive lock for read/write.
        flock($fp, LOCK_EX);
        
        // PHP caches file lengths; clear that out so we get
        // an accurate file length.
        clearstatcache();
        $len = filesize($file);
        
        // get the current value and increment it
        $val = fread($fp, $len);
        $val += $amt;
        
        // clear the file, rewind the pointer, and write the new value
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $val);
        
        // unlock and close, then done.
        flock($fp, LOCK_UN);
        fclose($fp);
        return $val;
    }
    
    /**
     * 
     * Deletes an entry from the cache.
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
        
        $file = $this->entry($key);
        @unlink($file, $this->_context);
        @unlink($file . '.meta', $this->_context);
    }
    
    /**
     * 
     * Removes all entries from the cache.
     * 
     * @return void
     * 
     */
    public function deleteAll()
    {
        if (! $this->_active) {
            return;
        }
        
        if (! $this->_hash) {
            // not hashing, so delete recursively
            $this->_deleteAll($this->_path);
            return;
        }
        
        // because we are hashing, we have a flat directory space, so we
        // don't need to recurse into subdirectories.
        // 
        // get the list of files in the directory, suppress warnings.
        $list = (array) @scandir($this->_path, null, $this->_context);
        
        // delete each file 
        foreach ($list as $file) {
            @unlink($this->_path . $file, $this->_context);
        }
    }
    
    /**
     * 
     * Support method to recursively descend into cache subdirectories and
     * remove their contents.
     * 
     * @param string $dir The directory to remove.
     * 
     * @return void
     * 
     */
    protected function _deleteAll($dir)
    {
        // get the list of files in the directory, suppress warnings.
        $list = (array) @scandir($dir, null, $this->_context);
        
        // delete each file, and recurse into subdirectories
        foreach ($list as $item) {
            
            // ignore dot-dirs
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            // set up the absolute path to the item
            $item = $dir . DIRECTORY_SEPARATOR . $item;
            
            // how to handle the item?
            if (is_dir($item)) {
                // recurse into each subdirectory ...
                $this->_deleteAll($item);
                // ... then remove it
                Solar_Dir::rmdir($item);
            } else {
                // remove the cache file
                @unlink($item, $this->_context);
            }
        }
    }
    
    /**
     * 
     * Returns the path and filename for the entry key.
     * 
     * @param string $key The entry ID.
     * 
     * @return string The cache entry path and filename.
     * 
     */
    public function entry($key)
    {
        if ($this->_config['hash']) {
            return $this->_path . hash('md5', $key);
        } else {
            // try to avoid file traversal exploits
            $key = str_replace('..', '_', $key);
            // colons mess up Mac OS X
            $key = str_replace(':', '_', $key);
            // done
            return $this->_path . $key;
        }
    }
} 
