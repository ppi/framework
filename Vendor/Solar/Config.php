<?php
/**
 * 
 * Static support methods for config information.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Config.php 4498 2010-03-05 17:28:00Z pmjones $
 * 
 */
class Solar_Config
{
    /**
     * 
     * The loaded values from the config file.
     * 
     * @var array
     * 
     * @see load()
     * 
     */
    static protected $_store = array();
    
    /**
     * 
     * The config values built for a class, including inheritance from its
     * parent class configs.
     * 
     * @var array
     * 
     * @see setBuild()
     * 
     * @see getBuild()
     * 
     */
    static protected $_build = array();
    
    /**
     * 
     * Safely gets a configuration class array or key value.
     * 
     * @param string $class The name of the class.  If not set, returns the
     * entire configuration array.
     * 
     * @param string $key The name of the key in the class.  If not set, 
     * returns the whole array for that class.
     * 
     * @param mixed $val If the class or key is not set, return
     * this value instead.  If this is not set and class was requested,
     * returns an empty array; if not set and a key was requested,
     * returns null.
     * 
     * @return mixed The value of the configuration class or key.
     * 
     */
    static public function get($class = null, $key = null, $val = null)
    {
        // are we looking for a class?
        if ($class === null) {
            // return the whole config array
            return Solar_Config::$_store;
        }
        
        // are we looking for a key in the class?
        if ($key === null) {
            
            // looking for a class. if no default passed, set up an
            // empty array.
            if ($val === null) {
                $val = array();
            }
            
            // find the requested class.
            if (! array_key_exists($class, Solar_Config::$_store)) {
                return $val;
            } else {
                return Solar_Config::$_store[$class];
            }
            
        } else {
            
            // find the requested class and key.
            $exists = array_key_exists($class, Solar_Config::$_store)
                   && array_key_exists($key, Solar_Config::$_store[$class]);
            
            if (! $exists) {
                return $val;
            } else {
                return Solar_Config::$_store[$class][$key];
            }
        }
    }
    
    /**
     * 
     * Loads the config values from the specified location.
     * 
     * @param mixed $spec A config specification.
     * 
     * @see fetch()
     * 
     * @return void
     * 
     */
    static public function load($spec)
    {
        Solar_Config::$_store = Solar_Config::fetch($spec);
        Solar_Config::$_build = array();
        $callback = Solar_Config::get('Solar_Config', 'load_callback');
        if ($callback) {
            $merge = (array) call_user_func($callback);
            Solar_Config::$_store = array_merge(Solar_Config::$_store, $merge);
        }
    }
    
    /**
     * 
     * Sets the config values for a class and key.
     * 
     * @param string $class The name of the class.
     * 
     * @param string $key The name of the key for the class; if empty, will
     * apply the changes to the entire class array.
     * 
     * @param mixed $val The value to set for the class and key.
     * 
     * @return void
     * 
     */
    static public function set($class, $key, $val)
    {
        if (! $key) {
            Solar_Config::$_store[$class] = $val;
        } else {
            Solar_Config::$_store[$class][$key] = $val;
        }
        
        // Invalidate the build cache; this can make set a rather expensive method to call
        Solar_Config::$_build = array();
    }
    
    /**
     * 
     * Fetches config file values.
     * 
     * Note that this method is overloaded by the variable type of $spec ...
     * 
     * * `null|false` (or empty) -- This will not load any new configuration
     *   values; you will get only the default [[Solar_Config::$_store]] array values
     *   defined in the Solar class.
     * 
     * * `string` -- The string is treated as a path to a Solar.config.php
     *   file; the return value from that file will be used for [[Solar_Config::$_store]].
     * 
     * * `array` -- This will use the passed array for the [[Solar_Config::$_store]]
     *   values.
     * 
     * * `object` -- The passed object will be cast as an array, and those
     *   values will be used for [[Solar_Config::$_store]].
     * 
     * @param mixed $spec A config specification.
     * 
     * @return array A config array.
     * 
     */
    static public function fetch($spec = null)
    {
        // load the config file values.
        // use alternate config source if one is given.
        if (is_array($spec) || is_object($spec)) {
            $config = (array) $spec;
        } elseif (is_string($spec)) {
            // merge from array file return
            $config = (array) Solar_File::load($spec);
        } else {
            // no added config
            $config = array();
        }
        
        return $config;
    }
    
    /**
     * 
     * Sets the build config to retain for a class.
     * 
     * **DO NOT** use this unless you know what you're doing.  The only reason
     * this is here is for Solar_Base::_buildConfig() to use it.
     * 
     * @param string $class The class name.
     * 
     * @param array $config Configuration value overrides, if any.
     * 
     * @return void
     * 
     */
    static public function setBuild($class, $config)
    {
        Solar_Config::$_build[$class] = (array) $config;
    }
    
    /**
     * 
     * Gets the retained build config for a class.
     * 
     * **DO NOT** use this unless you know what you're doing.  The only reason
     * this is here is for Solar_Base::_buildConfig() to use it.
     * 
     * @param string $class The class name to get the config build for.
     * 
     * @return mixed An array of retained config built for the class, or null
     * if there's no build for it.
     * 
     */
    static public function getBuild($class)
    {
        if (array_key_exists($class, Solar_Config::$_build)) {
            return Solar_Config::$_build[$class];
        }
    }
}