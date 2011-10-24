<?php
/**
 * 
 * Command to make a test class (or set of classes) from a given class.
 * 
 * Examples
 * ========
 * 
 * Make test files for a class and its subdirectories.
 * 
 *     $ ./script/solar make-tests Vendor_Example
 * 
 * Make only the Vendor_Example test (no subdirectories):
 * 
 *     $ solar make-tests --only Vendor_Example
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: MakeTests.php 4624 2010-06-30 21:56:24Z pmjones $
 * 
 */
class Solar_Cli_MakeTests extends Solar_Controller_Command
{
    /**
     * 
     * Skeleton templates for classes and methods.
     * 
     * @var array
     * 
     */
    protected $_tpl;
    
    /**
     * 
     * The source class to work with.
     * 
     * @var string
     * 
     */
    protected $_class;
    
    /**
     * 
     * The target directory for writing tests.
     * 
     * @var string
     * 
     */
    protected $_target;
    
    /**
     * 
     * Name of the test file to work with.
     * 
     * @var string
     * 
     */
    protected $_file;
    
    /**
     * 
     * The code in the test file.
     * 
     * @var string
     * 
     */
    protected $_code;
    
    /**
     * 
     * Builds one or more test files starting at the requested class, usually
     * descending recursively into subdirectories of that class.
     * 
     * @param string $class The class name to build tests for.
     * 
     * @return void
     * 
     */
    protected function _exec($class = null)
    {
        $this->_outln("Making tests.");
        
        // make sure we have a class to work with
        if (! $class) {
            throw $this->_exception('ERR_NO_CLASS');
        }
        
        // make sure we have a target directory
        $this->_setTarget();
        
        // get all the class and method templates
        $this->_loadTemplates();
        
        // build a class-to-file map object for later use
        $map = Solar::factory('Solar_Class_Map');
        
        // tell the user where the source and targets are
        $this->_outln("Source: " . $map->getBase());
        $this->_outln("Target: $this->_target");
        
        // get the class and file locations
        $class_file = $map->fetch($class);
        foreach ($class_file as $class => $file) {
            
            // if this is an exception class, skip it
            if (strpos($class, '_Exception')) {
                $this->_outln("$class: skip (exception class)");
                continue;
            } else {
                // tell the user what class we're on
                $this->_outln("$class"); 
            }
            
            // load the class and get its API reference
            $apiref = Solar::factory('Solar_Docs_Apiref');
            $apiref->addClass($class, $file);
            $api = $apiref->api[$class];
            
            // set the file name, creating if needed
            $this->_setFile($class, $api);
            
            // skip adding methods on adapter classes; they should get their
            // methods from the parent class
            $pos = strrpos($class, '_Adapter_');
            if ($pos === false) {
            
                // get the code currently in the file
                $this->_code = file_get_contents($this->_file);
            
                // add new test methods
                $this->_addTestMethods($api);
            
                // write the file back out again
                file_put_contents($this->_file, $this->_code);
            }
        }
        
        // done with all classes.
        $this->_outln('Done.');
    }
    
    /**
     * 
     * Loads the template array from skeleton files.
     * 
     * @return void
     * 
     */
    protected function _loadTemplates()
    {
        $this->_tpl = array();
        $dir = Solar_Dir::fix(dirname(__FILE__) . '/MakeTests/Data');
        $list = glob($dir . '*.php');
        foreach ($list as $file) {
            $key = substr(basename($file), 0, -4);
            $text = file_get_contents($file);
            if (substr($key, 0, 5) == 'class') {
                // we need to add the php-open tag ourselves, instead of
                // having it in the template file, becuase the PEAR packager
                // complains about parsing the skeleton code.
                $text = "<?php\n$text";
            }
            $this->_tpl[$key] = $text;
        }
    }
    
    /**
     * 
     * Sets the base directory target.
     * 
     * @return void
     * 
     */
    protected function _setTarget()
    {
        $target = Solar::$system . "/include";
        $this->_target = Solar_Dir::fix($target);
    }
    
    /**
     * 
     * Sets the file name for the test file, creating it if needed.
     * 
     * Uses a different class template for abstract, factory, and normal
     * (concrete) classes.  Also looks to see if this is an Adapter-based
     * class and takes that into account.
     * 
     * @param string $class The class name to work with.
     * 
     * @param array $api The list of methods in the class API to write test
     * stubs for.
     * 
     * @return void
     * 
     */
    protected function _setFile($class, $api)
    {
        $this->_file = $this->_target 
                     . str_replace('_', DIRECTORY_SEPARATOR, "Test_$class")
                     . '.php';
        
        // create the file if needed
        if (file_exists($this->_file)) {
            return;
        }
        
        // create the directory if needed
        $dir = dirname($this->_file);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        // use the right code template
        if ($api['abstract']) {
            $code = $this->_tpl['classAbstract'];
        } elseif (in_array('Solar_Factory', $api['from'])) {
            $code = $this->_tpl['classFactory'];
        } else {
            $code = $this->_tpl['classConcrete'];
        }
        
        // use the right template for adapter abstract classes
        if (substr($class, -8) == '_Adapter') {
            $code = $this->_tpl['classAdapterAbstract'];
        }
        
        // use the right "extends" for adapter concrete classes
        $pos = strrpos($class, '_Adapter_');
        if ($pos === false) {
            // normal test extends
            $extends = 'Solar_Test';
        } else {
            // adapter extends: Test_Foo_Adapter_Bar extends Test_Foo_Adapter
            $extends = 'Test_' . substr($class, 0, $pos + 8);
            $code = $this->_tpl['classAdapterConcrete'];
        }
        
        // do replacements
        $code = str_replace(
            array('{:class}', '{:extends}'),
            array($class, $extends),
            $code
        );
        
        // write the file
        file_put_contents($this->_file, $code);
    }
    
    /**
     * 
     * Adds test methods to the code for a test file.
     * 
     * @param array $api The list of methods in the class API to write test
     * stubs for.
     * 
     * @return void
     * 
     */
    protected function _addTestMethods($api)
    {
        // prepare the testing code for appending new methods.
        $this->_code = trim($this->_code);
        
        // the last char should be a brace.
        $last = substr($this->_code, -1);
        if ($last != '}') {
            throw $this->_exception('ERR_LAST_BRACE', array(
                'file' => $this->_file
            ));
        }
        
        // strip the last brace
        $this->_code = substr($this->_code, 0, -1);
        
        // ignore these methods
        $ignore = array('__construct', '__destruct', 'dump', 'locale');
        
        // look for methods and add them if needed
        foreach ($api['methods'] as $name => $info) {
            
            // is this an ignored method?
            if (in_array($name, $ignore)) {
                $this->_outln("    . $name");
                continue;
            }
            
            // is this a public method?
            if ($info['access'] != 'public') {
                $this->_outln("    . $name");
                continue;
            };
            
            // the test-method name
            $test_name = 'test' . ucfirst($name);
            
            // does the test-method definition already exist?
            $def = "function {$test_name}()";
            $pos = strpos($this->_code, $def);
            if ($pos) {
                $this->_outln("    . $name");
                continue;
            }
            
            // use the right code template
            if ($info['abstract']) {
                $test_code = $this->_tpl['methodAbstract'];
            } else {
                $test_code = $this->_tpl['methodConcrete'];
            }
            
            // do replacements
            $test_code = str_replace(
                array('{:name}', '{:summ}'),
                array($test_name, $info['summ']),
                $test_code
            );
            
            // append to the test code
            $this->_code .= $test_code;
            $this->_outln("    + $name");
        }
        
        // append the last brace
        $this->_code = trim($this->_code) . "\n}\n";
    }
}
