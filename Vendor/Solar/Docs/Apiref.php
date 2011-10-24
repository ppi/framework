<?php
/**
 * 
 * Recursively parses a class directory for API reference documentation.
 * 
 * @category Solar
 * 
 * @package Solar_Docs Tools for building API documentation from source code.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Apiref.php 4534 2010-04-23 16:43:23Z pmjones $
 * 
 * @todo parse constants
 * 
 * @todo report when a method is missing documentation (at least a summary)
 * 
 * @todo report when a property is missing documentation (at least a summary)
 * 
 * @todo actually set up a log object
 * 
 */
class Solar_Docs_Apiref extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency phpdoc A Solar_Docs_Phpdoc dependency.
     * 
     * @config dependency log A Solar_Log dependency.
     * 
     * @config string unknown When a type is unknown or not specified,
     *   use this value instead.
     * 
     * @var array
     * 
     */
    protected $_Solar_Docs_Apiref = array(
        'phpdoc'  => null,
        'log'     => array(
            'adapter' => 'Solar_Log_Adapter_Echo',
            'format'  => '%m',
        ),
        'unknown' => 'void',
    );
    
    /** 
     * 
     * Solar_Log instance.
     * 
     * @var Solar_Log
     * 
     */
    protected $_log;
    
    /**
     * 
     * Class for parsing PHPDoc comment blocks.
     * 
     * @var Solar_Docs_Phpdoc
     * 
     */
    protected $_phpdoc;
    
    /** 
     * 
     * When generating log notices, ignore these class methods and
     * properties.
     * 
     * @var string
     * 
     * @todo replace with a check for "built-in" classes?
     * 
     */
    protected $_ignore = array(
        'Exception' => array(
            'methods' => array(
                '__clone',
                'getMessage',
                'getCode',
                'getFile',
                'getLine',
                'getPrevious',
                'getTrace',
                'getTraceAsString',
            ),
            'properties' => array(
                'message',
                'code',
                'file',
                'line',
            ),
        ),
    );
    
    /** 
     * 
     * The entire API as a structured array.
     * 
     * {{code: php
     *     $api = array(
     *         classname => array(
     *             summ => string, // phpdoc summary
     *             narr => string, // phpdoc narrative
     *             tech => array(...), // technical phpdoc @tags
     *             from => array(...), // parent classes
     *             constants => array(
     *                 name => array(
     *                     type => string,
     *                     value => string,
     *                 ), // constantname
     *             ), // constants
     *             config_keys => array(
     *                 name => array(
     *                     name => string,
     *                     type => string,
     *                     summ => string,
     *                     value => mixed,
     *                 ),
     *             ), // config_keys
     *             properties => array(
     *                 name => array(
     *                     name => string,
     *                     summ => string,
     *                     narr => string,
     *                     tech => array(...),
     *                     type => string,
     *                     access => string,
     *                     static => bool,
     *                     from => string,
     *                 ), // propertyname
     *             ), // properties
     *             methods => array(
     *                 name => array(
     *                     name => string,
     *                     summ => string,
     *                     narr => string,
     *                     tech => array(...),
     *                     access => string,
     *                     static => bool,
     *                     final => bool,
     *                     return => string,
     *                     from => string,
     *                     params => array(
     *                         name => array(
     *                             name => string,
     *                             type => string,
     *                             summ => string,
     *                             byref => bool,
     *                             optional => bool,
     *                             default => mixed,
     *                         ), // paramname
     *                     ), // params
     *                 ), // methodname
     *             ), // methods
     *         ), // classname
     *     ); // $this->api
     * }}
     * 
     * @var array
     * 
     */
    public $api = array();
    
    /**
     * 
     * An array of all packages discovered.
     * 
     * Key is the package name, value is an array of all classes in that
     * package.
     * 
     * @var array
     * 
     */
    public $packages = array();
    
    /**
     * 
     * An array of all subpackages discovered.
     * 
     * Key is the subpackage name, value is an array of all classes in that
     * subpackage.
     * 
     * @var array
     * 
     */
    public $subpackages = array();
    
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
        
        // PHPDoc parser
        $this->_phpdoc = Solar::dependency(
            'Solar_Docs_Phpdoc',
            $this->_config['phpdoc']
        );
        
        // Logger
        $this->_log = Solar::dependency(
            'Solar_Log',
            $this->_config['log']
        );
    }
    
    /**
     * 
     * Adds classes from a file hierarchy.
     * 
     * @param string $base The base of the class hierarchy, typically
     * the base PEAR library path.
     * 
     * @param string $class Start with this class in the hierarchy.
     * 
     * @return void
     * 
     */
    public function addFiles($base, $class = null)
    {
        $map = Solar::factory('Solar_Class_Map');
        $map->setBase($base);
        $source = $map->fetch($class);
        foreach ($source as $class => $file) {
            require_once($file);
            $this->addClass($class, $file);
        }
    }
    
    /**
     * 
     * Adds a class to the API docs.
     * 
     * @param string $class The class to add to the docs.
     * 
     * @param string $file The file name in which the class is defined.
     * 
     * @return bool True if the class was added, false if not.
     * 
     */
    public function addClass($class, $file)
    {
        if (! class_exists($class)) {
            return false;
        }
        
        $reflect = new ReflectionClass($class);
        
        // add top-level class docs
        $this->api[$class] = $this->_phpdoc->parse($reflect->getDocComment());
        
        // definition info
        $this->api[$class]['abstract'] = $reflect->isAbstract() ? 'abstract' : false;
        $this->api[$class]['final'] = $reflect->isFinal() ? 'final' : false;
        $this->api[$class]['interface'] = $reflect->isInterface() ? 'interface' : false;
        
        // needs a summary line
        if (empty($this->api[$class]['summ'])) {
            $this->_log($class, "class '$class' has no summary");
        }
        
        // add to the package list
        if (empty($this->api[$class]['tech']['package'])) {
            $this->_log($class, "class '$class' has no @package tag");
            $this->api[$class]['tech']['package'] = null;
        } else {
            // retain in the class list
            $name = $this->api[$class]['tech']['package']['name'];
            $this->packages[$name]['list'][] = $class;
            // if a summary is present in the class, and no summary
            // has been retained yet, then retain it.
            $summ    = $this->api[$class]['tech']['package']['summ'];
            $already = ! empty($this->packages[$name]['summ']);
            if ($summ && ! $already) {
                $this->packages[$name]['summ'] = $summ;
            }
        }
        
        // optionally add to the subpackage list
        if (! empty($this->api[$class]['tech']['subpackage'])) {
            $name = $this->api[$class]['tech']['subpackage']['name'];
            $this->subpackages[$name][] = $class;
        }
        
        // add the class parents, properties and methods
        $this->_addParents($class);
        $this->_addConstants($class, $file);
        $this->_addConfigKeys($class);
        $this->_addProperties($class);
        $this->_addMethods($class);
        
        // done!
        return true;
    }
        
    
    /**
     * 
     * Adds the inheritance hierarchy for a given class.
     * 
     * @param string $class The class name.
     * 
     * @return void
     * 
     */
    protected function _addParents($class)
    {
        $parent = $class;
        $parents = array();
        while ($parent = get_parent_class($parent)) {
            $parents[] = $parent;
        }
        $this->api[$class]['from'] = array_reverse($parents);
    }
    
    /**
     * 
     * Adds the constant reflections for a given class.
     * 
     * The Reflection API does not support doc comments for constants yet,
     * which means we have to do a lot of extra work here to extract the
     * comments and their related information.
     * 
     * @param string $class The class name.
     * 
     * @param string $file The file in which the class is defined.
     * 
     * @return void
     * 
     */
    protected function _addConstants($class, $file)
    {
        // get constants; bail out early if there are none
        $reflect = new ReflectionClass($class);
        $list = $reflect->getConstants();
        if (! $list) {
            $this->api[$class]['constants'] = array();
            return;
        }
        
        // retain basic constants information
        $const = array();
        foreach ($list as $key => $val) {
            $const[$key] = array(
                'name' => $key,
                'summ' => '',
                'narr' => '',
                'tech' => '',
                'type' => gettype($val),
                'value' => var_export($val, true),
                // @todo add 'from' with inheritance check
            );
        }
        
        // re-purpose $list to be a regular expression clause
        $list = implode('|', array_keys($list));
        
        // the contents of the class file
        $text     = file_get_contents($file);
        
        // the length of the file
        $len      = strlen($text);
        
        // the current docblock text
        $block    = null;
        
        // are we in a docblock?
        $in_block = false;
        
        // manually retrieve docblocks from the file
        for ($pos = 0; $pos < $len; $pos ++) {
    
            // retain the current character
            $char = $text[$pos];
    
            // are we at a "/**" opener?
            if (substr($text, $pos, 3) == '/**') {
                $in_block = true;
                $block = null;
            }
    
            // are we in a docblock?
            if ($in_block) {
                $block .= $char;
            }
    
            // are we leaving a docblock?
            if (substr($text, $pos - 1, 2) == '*/') {
                
                // yes, no longer in a docblock
                $in_block = false;
        
                // is the docblock followed by a constant declaration?
                $expr = '/^[\s\n]*const[\s\n]+(' . $list . ')[\s\n]*=/';
                $next = substr($text, $pos + 1);
                preg_match($expr, $next, $matches);
                
                // yes, parse and retain the docblock info
                if (! empty($matches[1])) {
                    $name = $matches[1];
                    $docs = $this->_phpdoc->parse($block);
                    $const[$name]['summ'] = $docs['summ'];
                    $const[$name]['narr'] = $docs['narr'];
                    $const[$name]['tech'] = $docs['tech'];
                }
            }
        }
        // retain the constants array
        $this->api[$class]['constants'] = $const;
    }
    
    /**
     * 
     * Adds the property reflections for a given class.
     * 
     * @param string $class The class name.
     * 
     * @return void
     * 
     */
    protected function _addProperties($class)
    {
        $this->api[$class]['properties'] = array();
        $reflect = new ReflectionClass($class);
        
        foreach ($reflect->getProperties() as $prop) {
        
            // the property name
            $name = $prop->getName();
            
            // comment docs
            $docs = $this->_phpdoc->parse($prop->getDocComment());
            
            // basic properties
            $info = array(
                'name'   => $name,
                'summ'   => $docs['summ'],
                'narr'   => $docs['narr'],
                'tech'   => $docs['tech'],
                'type'   => null,
                'access' => null,
                'static' => $prop->isStatic() ? 'static' : false,
                'from' => false,
            );
            
            // set the access type
            if ($prop->isPublic()) {
                $info['access'] = "public";
            } elseif ($prop->isProtected()) {
                $info['access'] = "protected";
            } elseif ($prop->isPrivate()) {
                $info['access'] = "private";
            }
            
            // is this a class we ignore?
            // use the declaring class, not the current class, because
            // the property may be inherited.
            $decl = $prop->getDeclaringClass()->getName();
            $ignore = (array) @$this->_ignore[$decl]['properties'];
            
            // is there a summary line?
            if (empty($docs['summ'])) {
                // no summary line.  
                if (! in_array($name, $ignore)) {
                    // not in the list of ignored properties
                    $this->_log($class, "property '$name' has no summary");
                }
            }
            
            // does @var exist?
            if (empty($docs['tech']['var']['type'])) {
                // no @var type.  
                if (! in_array($name, $ignore)) {
                    // not in the list of ignored properties
                    $this->_log($class, "property '$name' has no @var type");
                }
            } else {
                $info['type'] = $docs['tech']['var']['type'];
            }
            
            // save in the API
            $this->api[$class]['properties'][$name] = $info;
            
            // was it inherited after all?
            $inherited = $this->_isInheritedProperty($class, $prop);
            $this->api[$class]['properties'][$name]['from'] = $inherited;
            
        }
        
        // sort them
        ksort($this->api[$class]['properties']);
    }
    
    /**
     * 
     * Adds the Solar configuration keys for a given class.
     * 
     * @param string $class The class name.
     * 
     * @return void
     * 
     */
    protected function _addConfigKeys($class)
    {
        $this->api[$class]['config_keys'] = array();
        
        // holding place for config key names and values
        $name_value = array();
        
        // holding place for tech info about @key phpdoc tags
        $tech = array();
        
        // get the parent classes and add the class itself
        $list = $this->api[$class]['from'];
        array_push($list, $class);
        foreach ($list as $item) {
            $reflect = new ReflectionClass($item);
            // all properties
            $vars = $reflect->getDefaultProperties();
            // the name of the config property
            $cvar = "_$item";
            // is there a config property?
            if (! empty($vars[$cvar])) {
                
                // merge name-value pairs with pre-existing
                $name_value = array_merge($name_value, $vars[$cvar]);
                
                // parse the docblock on the config var
                $prop = $reflect->getProperty($cvar);
                $docs = $this->_phpdoc->parse($prop->getDocComment());
                if (! empty($docs['tech']['key'])) {
                    foreach ($docs['tech']['key'] as $name => $info) {
                        $tech[$name] = $info;
                    }
                }
            }
        }
        
        foreach ($name_value as $name => $value) {
            if ($value === null) {
                $value = 'null'; // so that we get lower-case
            } else {
                $value = var_export($value, true);
            }
            
            if (empty($tech[$name]['type'])) {
                $tech[$name]['type'] = "unknown";
                $this->_log($class, "config key '$name' has no type");
            }
            
            if (empty($tech[$name]['summ'])) {
                $tech[$name]['summ'] = "No summary.";
                $this->_log($class, "config key '$name' has no summary");
            }
            
            $this->api[$class]['config_keys'][$name] = array(
                'name'  => $name,
                'type'  => $tech[$name]['type'],
                'summ'  => $tech[$name]['summ'],
                'value' => $value,
            );
        }
    }
    
    /**
     * 
     * Adds the method reflections for a given class.
     * 
     * @param string $class The class name.
     * 
     * @return void
     * 
     */
    protected function _addMethods($class)
    {
        $this->api[$class]['methods'] = array();
        
        $reflect = new ReflectionClass($class);
        
        foreach ($reflect->getMethods() as $method) {
            
            // get the method name
            $name = $method->getName();
            
            // parse the doc comments
            $docs = $this->_phpdoc->parse($method->getDocComment());
            
            // the basic method information
            $info = array(
                'from'     => false,
                'name'     => $name,
                'summ'     => $docs['summ'],
                'narr'     => $docs['narr'],
                'tech'     => $docs['tech'],
                'abstract' => $method->isAbstract() ? 'abstract' : false,
                'access'   => null,
                'static'   => $method->isStatic() ? 'static' : false,
                'final'    => $method->isFinal() ? 'final' : false,
                'return'   => null,
                'byref'    => $method->returnsReference() ? '&' : false,
                'params'   => array(),
            );
            
            // add the access visibility
            if ($method->isPublic()) {
                $info['access'] = 'public';
            } elseif ($method->isProtected()) {
                $info['access'] = 'protected';
            } elseif ($method->isPrivate()) {
                $info['access'] = 'private';
            }
            
            // is this a class we ignore?
            // use the declaring class, not the current class, because
            // the property may be inherited.
            $decl = $method->getDeclaringClass()->getName();
            $ignore = (array) @$this->_ignore[$decl]['methods'];
            
            // is there a summary line?
            if (empty($docs['summ'])) {
                // no summary line.  
                if (! in_array($name, $ignore)) {
                    // not in the list of ignored methods
                    $this->_log($class, "method '$name' has no summary");
                }
            }
            
            // find the return type in the technical docs
            if ($method->isConstructor()) {
                // it's a constructor, so it returns its own class
                $info['return'] = $class;
            } elseif (! empty($docs['tech']['return']['type'])) {
                // return type comes from tech docs
                $info['return'] = $docs['tech']['return']['type'];
            } else {
                // no return type noted in the class docs
                $info['return'] = $this->_config['unknown'];
                
                // can we ignore this lack of type?
                if (! in_array($name, $ignore)) {
                    // not to be ignored
                    $unknown = $this->_config['unknown'];
                    $this->_log($class, "method '$name' has unknown @return type, used '$unknown'");
                }
            }
            
            // add the parameters
            $info['params'] = $this->_getParameters($class, $method, $docs['tech']);
            
            // save in the API
            $this->api[$class]['methods'][$name] = $info;
            
            // was it inherited after all?
            $inherited = $this->_isInheritedMethod($class, $method);
            $this->api[$class]['methods'][$name]['from'] = $inherited;
        }
        
        // sort them
        ksort($this->api[$class]['methods']);
    }
    
    /**
     * 
     * Reports the class, if any, a method is inherited from and identical to.
     * 
     * @param string $class The class to check.
     * 
     * @param ReflectionMethod $method The method to check.
     * 
     * @return string The class from which the method was inherited, but only
     * if the modifiers, parameters, and comments are identical.
     * 
     */
    protected function _isInheritedMethod($class, ReflectionMethod $method)
    {
        // if declared in the same class, then it's not inherited.
        $decl = $method->getDeclaringClass()->getName();
        if ($class != $decl) {
            return $decl;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Reports the class, if any, a property is inherited from and identical to.
     * 
     * @param string $class The class to check.
     * 
     * @param ReflectionProperty $property The property to check.
     * 
     * @return string The class from which the property was inherited, but only
     * if the modifiers and comments are identical.
     * 
     */
    protected function _isInheritedProperty($class, ReflectionProperty $property)
    {
        // if declared in the same class, then it's not inherited.
        $decl = $property->getDeclaringClass()->getName();
        if ($class != $decl) {
            return $decl;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Returns the parameters for a ReflectionMethod.
     * 
     * @param string $class The class name.
     * 
     * @param ReflectionMethod $method A ReflectionMethod object to get parameters for.
     * 
     * @param array $tech A technical information array derived from Solar_Docs_Phpdoc.
     * 
     * @return array An array of parameter specifications.
     * 
     */
    protected function _getParameters($class, ReflectionMethod $method, $tech)
    {
        $params = array();
        $methodname = $method->getName();
        
        // find each of the parameters
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            $params[$name] = array(
                'name'     => $name,
                'type'     => 'unknown',
                'summ'     => null,
                'byref'    => $param->isPassedByReference() ? '&' : false,
                'optional' => $param->isOptional(),
                'default'  => $param->isOptional() ? $param->getDefaultValue() : null,
            );
            
            // add the type
            if ($param->getClass()) {
                
                // the type comes from a typehint.
                $params[$name]['type'] = $param->getClass();
                
                // hack, because of return differences between PHP5.1.4
                // and earlier PHP5.1.x versions.  otherwise you get
                // things like "Object id #31" as the type.
                if (is_object($params[$name]['type'])) {
                    $params[$name]['type'] = $params[$name]['type']->name;
                }
                
            } elseif (! empty($tech['param'][$name]['type'])) {
                // the type comes from the tech docs
                $params[$name]['type'] = $tech['param'][$name]['type'];
            } else {
                // no typehint, and not in the class docs
                $this->_log($class, "method '$methodname' param '$name' has no type");
            }
            
            // add the summary
            if (! empty($tech['param'][$name]['summ'])) {
                // summary comes from the tech docs
                $params[$name]['summ'] = $tech['param'][$name]['summ'];
            } else {
                // no summary
                $this->_log($class, "method '$methodname' param '$name' has no summary");
            }
        }
        return $params;
    }
    
    /**
     * 
     * Saves a message to the log.
     * 
     * @param string $class The class that the message refers to.
     * 
     * @param string $message The event message.
     * 
     * @return void
     * 
     */
    protected function _log($class, $message)
    {
        $message = "$class: $message";
        $this->_log->save(get_class($this), 'docs', $message);
    }
}
