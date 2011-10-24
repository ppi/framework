<?php
/**
 * 
 * The Solar arch-class provides static methods needed throughout the
 * framework environment.
 * 
 * @category Solar
 * 
 * @package Solar Foundation classes for all of Solar.
 * 
 * @author Paul M. Jones <pmjones@solarphp.net>
 * 
 * @version $Id: Solar.php 4551 2010-05-04 21:38:04Z pmjones $
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * Copyright (c) 2005-2007, Paul M. Jones.  All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 
 * * Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * 
 * * Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following
 *   disclaimer in the documentation and/or other materials provided
 *   with the distribution.
 * 
 * * Neither the name of the Solar project nor the names of its
 *   contributors may be used to endorse or promote products derived
 *   from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 */
class Solar
{
    /**
     * 
     * Default config values for the Solar arch-class.
     * 
     * @config array ini_set An array of key-value pairs where the key is an
     *   [[php::ini_set | ]] key, and the value is the value for that setting.
     * 
     * @config array registry_set An array of key-value pairs to use in pre-setting registry
     *   objects.  The key is a registry name to use.  The value is either
     *   a string class name, or is a sequential array where element 0 is
     *   a string class name and element 1 is a configuration array for that
     *   class.  Cf. [[Solar_Registry::set()]].
     * 
     * @config array start Run these scripts at the end of Solar::start().
     * 
     * @config array stop Run these scripts in Solar::stop().
     * 
     * @config string system The system directory path.
     * 
     * @var array
     * 
     */
    protected static $_Solar = array(
        'ini_set'      => array(),
        'registry_set' => array(),
        'start'        => array(),
        'stop'         => array(),
        'system'       => null,
    );
    
    /**
     * 
     * The Solar system root directory.
     * 
     * @var string
     * 
     */
    public static $system = null;
    
    /**
     * 
     * Status flag (whether Solar has started or not).
     * 
     * @var bool
     * 
     */
    protected static $_status = false;
    
    /**
     * 
     * Constructor is disabled to enforce a singleton pattern.
     * 
     */
    final private function __construct() {}
    
    /**
     * 
     * Starts Solar: loads configuration values and and sets up the environment.
     * 
     * Note that this method is overloaded; you can pass in different
     * value types for the $config parameter.
     * 
     * * `null|false` -- This will not load any new configuration values;
     *   you will get only the default values defined in the Solar class.
     * 
     * * `string` -- The string is treated as a path to a Solar.config.php
     *   file; the return value from that file will be used for [[Solar_Config::load()]].
     * 
     * * `array` -- This will use the passed array for the [[Solar_Config::load()]]
     *   values.
     * 
     * * `object` -- The passed object will be cast as an array, and those
     *   values will be used for [[Solar_Config::load()]].
     * 
     * Here are some examples of starting with alternative configuration parameters:
     * 
     * {{code: php
     *     require_once 'Solar.php';
     * 
     *     // don't load any config values at all
     *     Solar::start();
     * 
     *     // point to a config file (which returns an array)
     *     Solar::start('/path/to/another/config.php');
     * 
     *     // use an array as the config source
     *     $config = array(
     *         'Solar' => array(
     *             'ini_set' => array(
     *                 'error_reporting' => E_ALL,
     *             ),
     *         ),
     *     );
     *     Solar::start($config);
     * 
     *     // use an object as the config source
     *     $config = new StdClass;
     *     $config->Solar = array(
     *         'ini_set' => array(
     *             'error_reporting' => E_ALL,
     *         ),
     *     );
     *     Solar::start($config);
     * }}
     *  
     * @param mixed $config The configuration source value.
     * 
     * @return void
     * 
     * @see Solar::cleanGlobals()
     * 
     */
    public static function start($config = null)
    {
        // don't re-start if we're already running.
        if (Solar::$_status) {
            return;
        }
        
        // make sure these classes are loaded
        $list = array(
            'Base',
            'Class',
            'Config',
            'File',
        );
        
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Solar';
        foreach ($list as $name) {
            // don't use the autoloader when checking for existence
            if (! class_exists('Solar_' . $name, false)) {
                require $dir . DIRECTORY_SEPARATOR . "$name.php";
            }
        }
        
        // register autoloader
        spl_autoload_register(array('Solar_Class', 'autoload'));
        
        // clear out registered globals
        if (ini_get('register_globals')) {
            Solar::cleanGlobals();
        }
        
        // load config values
        Solar_Config::load($config);
        
        // make sure we have the Solar arch-class configs
        $arch_config = Solar_Config::get('Solar');
        if (! $arch_config) {
            Solar_Config::set('Solar', null, Solar::$_Solar);
        } else {
            Solar_Config::set('Solar', null, array_merge(
                Solar::$_Solar,
                (array) $arch_config
            ));
        }
        
        // set the system directory
        Solar::$system = Solar_Config::get('Solar', 'system');
        
        // process ini settings from config file
        $settings = Solar_Config::get('Solar', 'ini_set', array());
        foreach ($settings as $key => $val) {
            ini_set($key, $val);
        }
        
        // user-defined registry entries
        $register = Solar_Config::get('Solar', 'registry_set', array());
        foreach ($register as $name => $list) {
            // make sure we have the class-name and a config
            $list = array_pad((array) $list, 2, null);
            list($spec, $config) = $list;
            // register the item
            Solar_Registry::set($name, $spec, $config);
        }
        
        // Solar itself needs these default objects registered ...
        $name_class = array(
            'inflect'  => 'Solar_Inflect',
            'locale'   => 'Solar_Locale',
            'rewrite'  => 'Solar_Uri_Rewrite',
            'request'  => 'Solar_Request',
            'response' => 'Solar_Http_Response',
        );
        
        // ... but only if not already registered by the user.
        foreach ($name_class as $name => $class) {
            if (! Solar_Registry::exists($name)) {
                Solar_Registry::set($name, $class);
            }
        }
        
        // run any 'start' hooks
        $hooks = Solar_Config::get('Solar', 'start', array());
        Solar::callbacks($hooks);
        
        // and we're done!
        Solar::$_status = true;
    }
    
    /**
     * 
     * Stops Solar: runs stop scripts and cleans up the Solar environment.
     * 
     * @return void
     * 
     */
    public static function stop()
    {
        // run any 'stop' hook methods
        $hooks = Solar_Config::get('Solar', 'stop', array());
        Solar::callbacks($hooks);
        
        // unregister autoloader
        spl_autoload_unregister(array('Solar_Class', 'autoload'));
        
        // reset the status flag, and we're done.
        Solar::$_status = false;
    }
    
    /**
     * 
     * Runs a series of callbacks using call_user_func_array().
     * 
     * The callback array looks like this:
     * 
     * {{code: php
     *     $callbacks = array(
     *         // static method call
     *         array('Class_Name', 'method', $param1, $param2, ...),
     *         
     *         // instance method call on a registry object
     *         array('registry-key', 'method', $param1, $param2, ...),
     *         
     *         // instance method call
     *         array($object, 'method', $param1, $param2, ...),
     *         
     *         // function call
     *         array(null, 'function', $param1, $param2, ...),
     *         
     *         // file include, as in previous versions of Solar
     *         'path/to/file.php',
     *     );
     * }}
     * 
     * @param array $callbacks The array of callbacks.
     * 
     * @return void
     * 
     * @see start()
     * 
     * @see stop()
     * 
     */
    public static function callbacks($callbacks)
    {
        foreach ((array) $callbacks as $params) {
            
            // include a file as in previous versions of Solar
            if (is_string($params)) {
                Solar_File::load($params);
                continue;
            }
            
            // $spec is an object instance, class name, or registry key
            settype($params, 'array');
            $spec = array_shift($params);
            if (! is_object($spec)) {
                // not an object, so treat as a class name ...
                $spec = (string) $spec;
                // ... unless it's a registry key.
                if (Solar_Registry::exists($spec)) {
                    $spec = Solar_Registry::get($spec);
                }
            }
            
            // the method to call on $spec
            $func = array_shift($params);
            
            // make the call
            if ($spec) {
                call_user_func_array(array($spec, $func), $params);
            } else {
                call_user_func_array($func, $params);
            }
        }
    }
    
    /**
     * 
     * Returns the API version for Solar.
     * 
     * @return string A PHP-standard version number.
     * 
     */
    public static function apiVersion()
    {
        return '1.1.2';
    }
    
    /**
     * 
     * Convenience method to instantiate and configure an object.
     * 
     * @param string $class The class name.
     * 
     * @param array $config Configuration value overrides, if any.
     * 
     * @return object A new instance of the requested class.
     * 
     */
    public static function factory($class, $config = null)
    {
        Solar_Class::autoload($class);
        $obj = new $class($config);
        
        // is it an object factory?
        if ($obj instanceof Solar_Factory) {
            // return an instance from the object factory
            return $obj->factory();
        }
        
        // return the object itself
        return $obj;
    }
    
    /**
     * 
     * Combination dependency-injection and service-locator method; returns
     * a dependency object as passed, or an object from the registry, or a 
     * new factory instance.
     * 
     * @param string $class The dependency object should be an instance of
     * this class. Technically, this is more a hint than a requirement, 
     * although it will be used as the class name if [[Solar::factory()]] 
     * gets called.
     * 
     * @param mixed $spec If an object, check to make sure it's an instance 
     * of $class. If a string, treat as a [[Solar_Registry::get()]] key. 
     * Otherwise, use this as a config param to [[Solar::factory()]] to 
     * create a $class object.
     * 
     * @return object The dependency object.
     * 
     */
    public static function dependency($class, $spec)
    {
        // is it an object already?
        if (is_object($spec)) {
            return $spec;
        }
        
        // check for registry objects
        if (is_string($spec)) {
            return Solar_Registry::get($spec);
        }
        
        // not an object, not in registry.
        // try to create an object with $spec as the config
        return Solar::factory($class, $spec);
    }
    
    /**
     * 
     * Generates a simple exception, but does not throw it.
     * 
     * This method attempts to automatically load an exception class
     * based on the error code, falling back to parent exceptions
     * when no specific exception classes exist.  For example, if a
     * class named 'Vendor_Example' extended from 'Vendor_Base' throws an
     * exception or error coded as 'ERR_FILE_NOT_FOUND', the method will
     * attempt to return these exception classes in this order ...
     * 
     * 1. Vendor_Example_Exception_FileNotFound (class specific)
     * 
     * 2. Vendor_Base_Exception_FileNotFound (parent specific)
     * 
     * 3. Vendor_Example_Exception (class generic)
     * 
     * 4. Vendor_Base_Exception (parent generic)
     * 
     * 5. Vendor_Exception (generic for all of vendor)
     * 
     * The final fallback is always the generic Solar_Exception class.
     * 
     * Note that this method only generates the object; it does not
     * throw the exception.
     * 
     * {{code: php
     *     $class = 'My_Example_Class';
     *     $code = 'ERR_SOMETHING_WRONG';
     *     $text = 'Something is wrong.';
     *     $info = array('foo' => 'bar');
     *     $exception = Solar::exception($class, $code, $text, $info);
     *     throw $exception;
     * }}
     * 
     * In general, you shouldn't need to use this directly in classes
     * extended from [[Solar_Base]].  Instead, use
     * [[Solar_Base::_exception() | $this->_exception()]] for automated
     * picking of the right exception class from the $code, and
     * automated translation of the error message.
     * 
     * @param string|object $spec The class name (or object) that generated 
     * the exception.
     * 
     * @param mixed $code A scalar error code, generally a string.
     * 
     * @param string $text Any error message text.
     * 
     * @param array $info Additional error information in an associative
     * array.
     * 
     * @return Solar_Exception
     * 
     */
    public static function exception($spec, $code, $text = '',
        $info = array())
    {
        // is the spec an object?
        if (is_object($spec)) {
            // yes, find its class
            $class = get_class($spec);
        } else {
            // no, assume the spec is a class name
            $class = (string) $spec;
        }
        
        // drop 'ERR_' and 'EXCEPTION_' prefixes from the code
        // to get a suffix for the exception class
        $suffix = $code;
        if (strpos($suffix, 'ERR_') === 0) {
            $suffix = substr($suffix, 4);
        } elseif (strpos($suffix, 'EXCEPTION_') === 0) {
            $suffix = substr($suffix, 10);
        }
        
        // convert "STUDLY_CAP_SUFFIX" to "Studly Cap Suffix" ...
        $suffix = ucwords(strtolower(str_replace('_', ' ', $suffix)));
        
        // ... then convert to "StudlyCapSuffix"
        $suffix = str_replace(' ', '', $suffix);
        
        // build config array from params
        $config = array(
            'class' => $class,
            'code'  => $code,
            'text'  => $text,
            'info'  => (array) $info,
        );
        
        // get all parent classes, including the class itself
        $stack = array_reverse(Solar_Class::parents($class, true));
        
        // add the vendor namespace to the stack as a fallback, even though
        // it's not strictly part of the hierarchy, for generic vendor-wide
        // exceptions.
        $vendor = Solar_Class::vendor($class);
        if ($vendor != 'Solar') {
            $stack[] = $vendor;
        }
        
        // add Solar as the final fallback
        $stack[] = 'Solar';
        
        // track through class stack and look for specific exceptions
        foreach ($stack as $class) {
            try {
                $obj = Solar::factory("{$class}_Exception_$suffix", $config);
                return $obj;
            } catch (Exception $e) {
                // do nothing
            }
        }
        
        // track through class stack and look for generic exceptions
        foreach ($stack as $class) {
            try {
                $obj = Solar::factory("{$class}_Exception", $config);
                return $obj;
            } catch (Exception $e) {
                // do nothing
            }
        }
        
        // last resort: a generic Solar exception
        return Solar::factory('Solar_Exception', $config);
    }
    
    /**
     * 
     * Dumps a variable to output.
     * 
     * Essentially, this is an alias to the Solar_Debug_Var::dump()
     * method, which buffers the [[php::var_dump | ]] for a variable,
     * applies some simple formatting for readability, [[php::echo | ]]s
     * it, and prints with an optional label.  Use this for
     * debugging variables to see exactly what they contain.
     * 
     * @param mixed $var The variable to dump.
     * 
     * @param string $label A label for the dumped output.
     * 
     * @return void
     * 
     */
    public static function dump($var, $label = null)
    {
        $obj = Solar::factory('Solar_Debug_Var');
        $obj->display($var, $label);
    }
    
    /**
     * 
     * Cleans the global scope of all variables that are found in other
     * super-globals.
     * 
     * This code originally from Richard Heyes and Stefan Esser.
     * 
     * @return void
     * 
     */
    public static function cleanGlobals()
    {
        $list = array(
            'GLOBALS',
            '_POST',
            '_GET',
            '_COOKIE',
            '_REQUEST',
            '_SERVER',
            '_ENV',
            '_FILES',
        );
        
        // Create a list of all of the keys from the super-global values.
        // Use array_keys() here to preserve key integrity.
        $keys = array_merge(
            array_keys($_ENV),
            array_keys($_GET),
            array_keys($_POST),
            array_keys($_COOKIE),
            array_keys($_SERVER),
            array_keys($_FILES),
            // $_SESSION is null if you have not started the session yet.
            // This insures that a check is performed regardless.
            isset($_SESSION) && is_array($_SESSION) ? array_keys($_SESSION) : array()
        );
        
        // Unset the globals.
        foreach ($keys as $key) {
            if (isset($GLOBALS[$key]) && ! in_array($key, $list)) {
                unset($GLOBALS[$key]);
            }
        }
    }
}
