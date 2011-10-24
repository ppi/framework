<?php
/**
 * 
 * Abstract base class for all Solar objects.
 * 
 * This is the class from which almost all other Solar classes are
 * extended.  Solar_Base is relatively light, and provides ...
 * 
 * * Construction-time reading of config file options 
 *   for itself, and merging of those options with any options passed   
 *   for instantation, along with the class-defined config defaults,
 *   into the Solar_Base::$_config property.
 * 
 * * A [[Solar_Base::locale()]] convenience method to return locale strings.
 * 
 * * A [[Solar_Base::_exception()]] convenience method to generate
 *   exception objects with translated strings from the locale file
 * 
 * Note that you do not define config defaults in $_config directly; 
 * instead, you use a protected property named for the class, with an
 * underscore prefix.  For exmple, a "Vendor_Class_Name" class would 
 * define the default config array in "$_Vendor_Class_Name".  This 
 * convention lets child classes inherit parent config keys and values.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Base.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
abstract class Solar_Base
{
    
    /**
     * 
     * Collection point for configuration values.
     * 
     * Note that you do not define config defaults in $_config directly.
     * 
     * {{code: php
     *     // DO NOT DO THIS
     *     protected $_config = array(
     *         'foo' => 'bar',
     *         'baz' => 'dib',
     *     );
     * }}
     * 
     * Instead, define config defaults in a protected property named for the
     * class, withan underscore prefix.
     * 
     * For exmple, a "Vendor_Class_Name" class would define the default 
     * config array in "$_Vendor_Class_Name".  This convention lets 
     * child classes inherit parent config keys and values.
     * 
     * {{code: php
     *     // DO THIS INSTEAD
     *     protected $_Vendor_Class_Name = array(
     *         'foo' => 'bar',
     *         'baz' => 'dib',
     *     );
     * }}
     * 
     * @var array
     * 
     */
    protected $_config = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config Configuration value overrides, if any.
     * 
     */
    public function __construct($config = null)
    {
        // pre-configuration tasks
        $this->_preConfig();
        
        // build configuration
        $this->_config = array_merge(
            $this->_buildConfig(get_class($this)),
            (array) $config
        );
        
        // post-configuration tasks
        $this->_postConfig();
        
        // post-construction tasks
        $this->_postConstruct();
    }
    
    /**
     * 
     * Default destructor; does nothing other than provide a safe fallback
     * for calls to parent::__destruct().
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
    }
    
    /**
     * 
     * Convenience method for getting a dump the whole object, or one of its
     * properties, or an external variable.
     * 
     * @param mixed $var If null, dump $this; if a string, dump $this->$var;
     * otherwise, dump $var.
     * 
     * @param string $label Label the dump output with this string.
     * 
     * @return void
     * 
     */
    public function dump($var = null, $label = null)
    {
        $obj = Solar::factory('Solar_Debug_Var');
        if (is_null($var)) {
            // clone $this and remove the parent config arrays
            $clone = clone($this);
            foreach (Solar_Class::parents($this) as $class) {
                $key = "_$class";
                unset($clone->$key);
            }
            $obj->display($clone, $label);
        } elseif (is_string($var)) {
            // display a property
            $obj->display($this->$var, $label);
        } else {
            // display the passed variable
            $obj->display($var, $label);
        }
    }
    
    /**
     * 
     * Looks up class-specific locale strings based on a key.
     * 
     * This is a convenient shortcut for calling
     * [[Solar_Registry]]::get('locale')->fetch()
     * that automatically uses the current class name.
     * 
     * You can also pass an array of replacement values.  If the `$replace`
     * array is sequential, this method will use it with vsprintf(); if the
     * array is associative, this method will replace "{:key}" with the array
     * value.
     * 
     * For example:
     * 
     * {{code: php
     *     $page  = 2;
     *     $pages = 10;
     *     
     *     // given a locale string TEXT_PAGES => 'Page %d of %d'
     *     $replace = array($page, $pages);
     *     return $this->locale('Solar_Example', 'TEXT_PAGES',
     *         $pages, $replace);
     *     // returns "Page 2 of 10"
     *     
     *     // given a locale string TEXT_PAGES => 'Page {:page} of {:pages}'
     *     $replace = array('page' => $page, 'pages' => $pages);
     *     return $this->locale('Solar_Example', 'TEXT_PAGES',
     *         $pages, $replace);
     *     // returns "Page 2 of 10"
     * }}
     * 
     * @param string $key The key to get a locale string for.
     * 
     * @param string $num If 1, returns a singular string; otherwise, returns
     * a plural string (if one exists).
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return string The locale string, or the original $key if no
     * string found.
     * 
     */
    public function locale($key, $num = 1, $replace = null)
    {
        static $class;
        if (! $class) {
            $class = get_class($this);
        }
        
        static $locale;
        if (! $locale) {
            $locale = Solar_Registry::get('locale');
        }
        
        return $locale->fetch($class, $key, $num, $replace);
    }
    
    /**
     * 
     * Builds and returns the default config for a class, including all
     * configs inherited from its parents.
     * 
     * @param string $class The class to get the config build for.
     * 
     * @return array The config build for the class.
     * 
     */
    protected function _buildConfig($class)
    {
        if (! $class) {
            return array();
        }
        
        $config = Solar_Config::getBuild($class);
        
        if ($config === null) {
        
            $var    = "_$class";
            $prop   = empty($this->$var)
                    ? array()
                    : (array) $this->$var;
                    
            $parent = get_parent_class($class);
            
            $config = array_merge(
                // parent values
                $this->_buildConfig($parent),
                // override with class property config
                $prop,
                // override with solar config for the class
                Solar_Config::get($class, null, array())
            );
            
            // cache for future reference
            Solar_Config::setBuild($class, $config);
        }
        
        return $config;
    }
    
    /**
     * 
     * A hook that activates before _buildConfig() in the constructor.
     * 
     * Allows you to modify the object before configuration is built; for
     * example, to set properties or to check for extensions.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
    }
    
    /**
     * 
     * A hook that activates after _buildConfig() in the constructor.
     * 
     * Allows you to modify $this->_config after it has been built.
     * 
     * @return void
     * 
     */
    protected function _postConfig()
    {
    }
    
    /**
     * 
     * A hook that activates at the end of the constructor.
     * 
     * Allows you to modify the object properties after config has been built,
     * and to call follow-on methods.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
    }
    
    /**
     * 
     * Convenience method for returning exceptions with localized text.
     * 
     * @param string $code The error code; does additional duty as the
     * locale string key and the exception class name suffix.
     * 
     * @param array $info An array of error-specific data.
     * 
     * @return Solar_Exception An instanceof Solar_Exception.
     * 
     */
    protected function _exception($code, $info = array())
    {
        static $class;
        if (! $class) {
            $class = get_class($this);
        }
        
        return Solar::exception(
            $class,
            $code,
            $this->locale($code, 1, $info),
            (array) $info
        );
    }
}
