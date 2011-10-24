<?php
/**
 * 
 * Registry for storing objects, with built-in lazy loading.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Registry.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_Registry
{
    /**
     * 
     * Map of registry names to object instances (or their specs for on-demand 
     * creation).
     * 
     * @var array
     * 
     */
    protected static $_obj = array();
    
    /**
     * 
     * Constructor is disabled to enforce a singleton pattern.
     * 
     */
    final private function __construct() {}
    
    /**
     * 
     * Accesses an object in the registry.
     * 
     * @param string $name The registered name.
     * 
     * @return object The object registered under $name.
     * 
     * @todo Localize these errors.
     * 
     */
    public static function get($name)
    {
        // has the shared object already been loaded?
        if (! Solar_Registry::exists($name)) {
            throw Solar::exception(
                'Solar_Registry',
                'ERR_NOT_IN_REGISTRY',
                "Object with name '$name' not in registry.",
                array('name' => $name)
            );
        }
        
        // was the registration for a lazy-load?
        if (is_array(Solar_Registry::$_obj[$name])) {
            $val = Solar_Registry::$_obj[$name];
            $obj = Solar::factory($val[0], $val[1]);
            Solar_Registry::$_obj[$name] = $obj;
        }
        
        // done
        return Solar_Registry::$_obj[$name];
    }
    
    /**
     * 
     * Registers an object under a unique name.
     * 
     * @param string $name The name under which to register the object.
     * 
     * @param object|string $spec The registry specification.
     * 
     * @param mixed $config If lazy-loading, use this as the config.
     * 
     * @return void
     * 
     * @todo Localize these errors.
     * 
     */
    public static function set($name, $spec, $config = null)
    {
        if (Solar_Registry::exists($name)) {
            // name already exists in registry
            $class = get_class(Solar_Registry::$_obj[$name]);
            throw Solar::exception(
                'Solar_Registry',
                'ERR_REGISTRY_NAME_EXISTS',
                "Object with '$name' of class '$class' already in registry", 
                array('name' => $name, 'class' => $class)
            );
        }
        
        // register as an object, or as a class and config?
        if (is_object($spec)) {
            // directly register the object
            Solar_Registry::$_obj[$name] = $spec;
        } elseif (is_string($spec)) {
            // register a class and config for lazy loading
            Solar_Registry::$_obj[$name] = array($spec, $config);
        } else {
            throw Solar::exception(
                'Solar_Registry',
                'ERR_REGISTRY_FAILURE',
                'Please pass an object, or a class name and a config array',
                array()
            );
        }
    }
    
    /**
     * 
     * Check to see if an object name already exists in the registry.
     * 
     * @param string $name The name to check.
     * 
     * @return bool
     * 
     */
    public static function exists($name)
    {
        return ! empty(Solar_Registry::$_obj[$name]);
    }
    
}