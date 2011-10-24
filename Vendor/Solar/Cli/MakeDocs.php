<?php
/**
 * 
 * Generates package and API documentation files.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @subpackage Solar_Cli_MakeDocs
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: MakeDocs.php 4605 2010-06-17 16:17:26Z pmjones $
 * 
 */
class Solar_Cli_MakeDocs extends Solar_Controller_Command
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string class_dir The directory in which to write class docs.
     * 
     * @config string package_dir The directory in which to write package docs.
     * 
     * @config string docbook_dir The directory in which to write converted
     * DocBook files.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cli_MakeDocs = array(
        'class_dir'   => null,
        'package_dir' => null,
        'docbook_dir' => null,
    );
    
    /**
     * 
     * The source code directory, typically the 'include' directory.
     * 
     * @var string
     * 
     */
    protected $_source;
    
    /**
     * 
     * Write class API files to this directory.
     * 
     * @var string
     * 
     */
    protected $_class_dir;
    
    /**
     * 
     * Write package files to this directory.
     * 
     * @var string
     * 
     */
    protected $_package_dir;
    
    /**
     * 
     * Write DocBook files to this directory.
     * 
     * @var string
     * 
     */
    protected $_docbook_dir;
    
    /**
     * 
     * Summary list of all classes.
     * 
     * @var array
     * 
     */
    protected $_classes_list = array();
    
    /**
     * 
     * The entire API as a data set.
     * 
     * @var array
     * 
     */
    public $api = array();
    
    /**
     * 
     * All package groupings as a data set.
     * 
     * @var array
     * 
     */
    public $packages = array();
    
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
        
        if ($this->_config['class_dir']) {
            $this->_class_dir = Solar_Dir::fix($this->_config['class_dir']);
        }
        
        if ($this->_config['package_dir']) {
            $this->_package_dir = Solar_Dir::fix($this->_config['package_dir']);
        }
        
        if ($this->_config['docbook_dir']) {
            $this->_docbook_dir = Solar_Dir::fix($this->_config['docbook_dir']);
        }
    }
    
    /**
     * 
     * Main action: parse the classes and write documentation.
     * 
     * @param string $class Start parsing with this class and recursively
     * descend.
     * 
     * @return void
     * 
     */
    protected function _exec($class = null)
    {
        $begin = time();
        
        if (! $class) {
            $class = 'Solar';
        }
        
        // get the source dir
        $this->_source = $this->_options['source'];
        if (! $this->_source) {
            // get the directory where this class is stored
            $this->_source = Solar_Dir::name(__FILE__, 2);
        }
        
        // start parsing
        $this->_outln("Parsing source files from '{$this->_source}' ... ");
        $ref = Solar::factory('Solar_Docs_Apiref');
        $ref->addFiles($this->_source, $class);
        
        // are we only linting the sources?
        if ($this->_options['lint']) {
            $this->_outln('Done.');
            return;
        }
        
        // get the target API dir
        $class_dir = $this->_options['class_dir'];
        if ($class_dir) {
            $this->_class_dir = Solar_Dir::fix($class_dir);
        }
        
        // do we have a class dir?
        if (! $this->_class_dir) {
            throw $this->_exception('ERR_NO_CLASS_DIR');
        }
        
        // get the target package dir (if any)
        $package_dir = $this->_options['package_dir'];
        if ($package_dir) {
            $this->_package_dir = Solar_Dir::fix($package_dir);
        }
        
        // do we have a package dir?
        if (! $this->_package_dir) {
            throw $this->_exception('ERR_NO_PACKAGE_DIR');
        }
        
        // get the docbook dir
        $docbook_dir = $this->_options['docbook_dir'];
        if ($docbook_dir) {
            $this->_docbook_dir = Solar_Dir::fix($docbook_dir);
        }
        
        // import the class data
        $this->api = $ref->api;
        ksort($this->api);
        
        // import the package data
        $this->packages = $ref->packages;
        ksort($this->packages);
        
        // write out the package pages
        $this->_outln();
        $this->writePackages();
        
        // write out the class pages
        $this->_outln();
        $this->writeClasses();
        
        // time note
        $time = time() - $begin;
        $this->_outln("Wiki docs written in $time seconds.");
        
        // convert to docbook?
        if ($this->_docbook_dir) {
            $this->_outln();
            $cli  = Solar::factory('Solar_Cli_MakeDocbook');
            $argv = array(
                "--class-dir={$this->_class_dir}",
                "--package-dir={$this->_package_dir}",
                "--docbook-dir={$this->_docbook_dir}",
                $class,
            );
            $cli->exec($argv);
            
            $this->_outln();
            $time = time() - $begin;
            $this->_outln("All docs written and converted in $time seconds.");
        }
    }
    
    /**
     * 
     * Writes the entire "packages" directory.
     * 
     * @return void
     * 
     */
    public function writePackages()
    {
        $this->_outln("Writing package pages to '{$this->_package_dir}':");
        
        $this->_out("Writing package index ... ");
        $this->writePackageIndex();
        $this->_outln("done.");
        
        $this->_outln("Writing package class lists:");
        $list = array_keys($this->packages);
        foreach ($list as $package) {
            $this->_out("$package ... ");
            $this->writePackageClassList($package);
            $this->_outln("done.");
        }
    }
    
    /**
     * 
     * Writes the package index file.
     * 
     * @return void
     * 
     */
    public function writePackageIndex()
    {
        $text = array();
        
        foreach ($this->packages as $name => $info) {
            $summ = empty($info['summ']) ? '-?-' : $info['summ'];
            $text[] = "[[Package::$name | ]]";
            $text[] = ": $summ";
            $text[] = "";
        }
        
        $this->_write('package', 'index', $text);
    }
    
    /**
     * 
     * Writes one package description file.
     * 
     * @param string $package The package name.
     * 
     * @return void
     * 
     */
    public function writePackageClassList($package)
    {
        $text = array();
        ksort($this->packages[$package]['list']);
        foreach ($this->packages[$package]['list'] as $class) {
            
            // ignore classes descended from Solar_Exception
            $parents = Solar_Class::parents($class);
            if (in_array('Solar_Exception', $parents)) {
                continue;
            }
            
            // everything else
            $text[] = "[[$class]]";
            if ($this->api[$class]['summ']) {
                $text[] = ": " . $this->api[$class]['summ'];
            } else {
                $text[] = ": -?-";
            }
            $text[] = '';
        }    
        $this->_write('package', $package, $text);
    }
    
    /**
     * 
     * Writes the "class" directory.
     * 
     * @return void
     * 
     */
    public function writeClasses()
    {
        $this->_outln("Writing class pages to '{$this->_class_dir}':");
        
        foreach ($this->api as $class => $api) {
            
            // ignore classes descended from Solar_Exception
            $parents = Solar_Class::parents($class);
            if (in_array('Solar_Exception', $parents)) {
                continue;
            }
            
            // write the class overview
            $this->_out("$class: ");
            $this->writeClassOverview($class);
            $this->_out('.');
            
            // write the list of all config options and default values
            $this->writeClassConfig($class);
            $this->_out('.');
            
            // write the list of all constants
            $this->writeClassConstants($class);
            $this->_out('.');
            
            // write the list of all class properties
            $this->writeClassProperties($class);
            $this->_out(".");
            
            // write the list of all class methods
            $this->writeClassMethods($class);
            $this->_out('.');
            
            // write each class method
            foreach ($api['methods'] as $name => $info) {
                $this->writeClassMethod($class, $name, $info);
                $this->_out('.');
            }
            
            // write the class index
            $this->writeClassIndex($class);
            $this->_outln(". ;");
            
            // retain the class name and info
            if ($api['summ']) {
                $this->_classes_list[$class] = $api['summ'];
            } else {
                $this->_classes_list[$class] = '-?-';
            }
        }
        
        $this->_outln("Done.");
        
        // write the overall list of classes and summaries.
        $this->_out("Writing summary list of all classes ... ");
        $this->writeClassesIndex();
        $this->_outln("done.");
    }
    
    /**
     * 
     * Writes the index file for the list of classes.
     * 
     * @return void
     * 
     */
    public function writeClassesIndex()
    {
        $text = array();
        $text[] = 'Class | Summary';
        $text[] = '----- | -------';
        foreach ($this->_classes_list as $name => $summ) {
            $text[] = "[[$name::Overview | $name]] | $summ";
        }
        $this->_write('class', 'index', $text);
    }
    
    /**
     * 
     * Writes the Overview file.
     * 
     * @param string $class The class to write Overview for.
     * 
     * @return void
     * 
     */
    public function writeClassOverview($class)
    {
        $text = array();
        
        // summary
        if ($this->api[$class]['summ']) {
            $text[] = $this->api[$class]['summ'];
            $text[] = '';
        }
        
        // narrative
        if ($this->api[$class]['narr']) {
            $text[] = $this->api[$class]['narr'];
            $text[] = '';
        }
        
        // package data
        $text[] = $this->_title2('Package');
        if ($this->api[$class]['tech']['package']['name']) {
            $package = $this->api[$class]['tech']['package']['name'];
            $text[] = "This class is part of the [[Package::$package | ]] package.";
            $text[] = '';
        }
        
        // inheritance hierarchy
        $parents = $this->api[$class]['from'];
        if ($parents) {
            
            $text[] = 'Inheritance:';
            $text[] = '';
            
            $i = 0;
            foreach ($this->api[$class]['from'] as $parent) {
                $pad = str_pad('', $i++, "\t");
                if ($parent == 'Exception') {
                    // special case for Exception classes
                    $text[] = "$pad* [Exception](http://php.net/Exceptions)";
                }elseif (empty($this->api[$parent])) {
                    // parent is a class not in the API
                    $text[] = "$pad* $parent";
                } else {
                    // parent is in the API, link to its overview page
                    $text[] = "$pad* [[$parent]]";
                }
            }
            $pad = str_pad('', $i++, "\t");
            $text[] = "$pad* $class";
            $text[] = '';
        }
        
        // Config keys
        $text[] = $this->_title2('Configuration Keys');
        $k = count($text);
        foreach ($this->api[$class]['config_keys'] as $name => $info) {
            $text[] = "* [[$class::Config.$name | `$name`]]: {$info['summ']}";
        }
        if (count($text) == $k) {
            $text[] = 'None.';
        }
        $text[] = '';
        
        // Constants
        $text[] = $this->_title2('Constants');
        $k = count($text);
        foreach ($this->api[$class]['constants'] as $name => $info) {
            $text[] = "* [[$class::$name | $name]]";
        }
        if (count($text) == $k) {
            $text[] = 'None.';
        }
        $text[] = '';
        
        // Public properties
        $tmp = array();
        foreach ($this->api[$class]['properties'] as $name => $info) {
            if ($info['access'] == 'public') {
                $tmp[] = "[[$class::\$$name | `\$$name`]]\n: {$info['summ']}\n";
            }
        }
        
        $text[] = $this->_title2('Public Properties');
        if ($tmp) {
            $text[] = "These are all the public properties in the $class class.\n";
            $text[] = "You can also view the list of [[$class::Properties | all public, protected, and private properties]].\n";
            $text = array_merge($text, $tmp);
        } else {
            $text[] = "The $class class has no public properties; try the list of [[$class::Properties | all properties]].\n";
        }
        
        // Public methods
        $text[] = $this->_title2('Public Methods');
        $text[] = "These are all the public methods in the $class class.\n";
        $text[] = "You can also view the list of [[$class::Methods | all public, protected, and private methods]].\n";
        
        $k = count($text);
        foreach ($this->api[$class]['methods'] as $name => $info) {
            if ($info['access'] == 'public') {
                
                $summ = trim($info['summ']);
                
                if (! $summ) {
                    $summ = '-?-';
                }
            
                $text[] = "[[$class::$name() | `$name()`]]\n: $summ\n";
            }
        }
        
        if (count($text) == $k) {
            $text[] = "None.\n";
        }
        
        $text[] = '';
        
        // done
        $this->_write("class", "$class/Overview", $text);
    }
    
    /**
     * 
     * Writes the index file for a single class.
     * 
     * @param string $class The class to write index for.
     * 
     * @return void
     * 
     */
    public function writeClassIndex($class)
    {
        $text = array();
        $text[] = 'Overview';
        $text[] = 'Config';
        $text[] = 'Constants';
        $text[] = 'Properties';
        $text[] = 'Methods';
        foreach (array_keys($this->api[$class]["methods"]) as $name) {
            $text[] = "$name()";
        }
        $this->_write("class", "$class/index", $text);
    }
    
    /**
     * 
     * Writes the Constants file.
     * 
     * @param string $class The class to write Constants for.
     * 
     * @return void
     * 
     */
    public function writeClassConstants($class)
    {
        $text = array();
        
        $list = $this->api[$class]['constants'];
        if ($list) {
            foreach ($this->api[$class]['constants'] as $name => $info) {
            
                // header
                $text[] = $this->_title2("`$name` {#class.$class.Constants.$name}");
                
                // summary
                if ($info['summ']) {
                    $text[] = "* {$info['summ']}";
                }
                
                // value
                $text[] = "* Value: (*{$info['type']}*) `{$info['value']}`";
                $text[] = "";
                
                // narrative
                if ($info['narr']) {
                    $text[] = $info['narr'];
                    $text[] = '';
                }
            }
        } else {
            $text[] = 'None.';
            $text[] = '';
        }
        
        $this->_write("class", "$class/Constants", $text);
    }
    
    /**
     * 
     * Writes the Config file.
     * 
     * @param string $class The class to write Config items for.
     * 
     * @return void
     * 
     */
    public function writeClassConfig($class)
    {
        $text = array();
        $list = $this->api[$class]['config_keys'];
        if ($list) {
            foreach ($list as $name => $info) {
                $text[] = $this->_title2("`$name` {#class.$class.Config.$name}");
                $text[] = "* (*{$info['type']}*) {$info['summ']}";
                $text[] = "* Default: `{$info['value']}`";
                $text[] = "";
            }
        } else {
            $text[] = 'None.';
            $text[] = '';
        }
        
        $this->_write("class", "$class/Config", $text);
    }
    
    /**
     * 
     * Writes the Properties file.
     * 
     * @param string $class The class to write Properties for.
     * 
     * @return void
     * 
     */
    public function writeClassProperties($class)
    {
        $text = array();
        
        $list = array(
            'public'    => array(),
            'protected' => array(),
            'private'   => array(),
        );
        
        // collect properties into the list by access/visibility
        foreach ($this->api[$class]['properties'] as $name => $info) {
            
            $tmp = array();
            
            // header
            $tmp[] = $this->_title3("`\$$name` {#class.$class.Properties.$name}");
            
            // summary
            $tmp[] = '_(' . ($info['static'] ? 'static ' : '')
                   . $info['type'] . ')_ '
                   . $info['summ'];
            $tmp[] = '';
            
            // inherited?
            if ($info['from']) {
                $tmp[] = "Inherited from [[{$info['from']}::\$$name | {$info['from']}]].";
                $tmp[] = '';
            }
            
            // narrative
            if ($info['narr']) {
                $tmp[] = $info['narr'];
                $tmp[] = '';
            }
            
            // save in the list
            $list[$info['access']][] = implode("\n", $tmp);
        }
        
        // now collapse the list into a single series
        foreach ($list as $access => $properties) {
            
            $text[] = $this->_title2(ucfirst($access));
            
            if ($properties) {
                $text = array_merge($text, $properties);
            } else {
                $text[] = 'None.';
                $text[] = '';
            }
            
            $text[] = '';
        }
        
        $this->_write("class", "$class/Properties", $text);
    }
    
    /**
     * 
     * Writes the Methods file.
     * 
     * @param string $class The class to write Methods for.
     * 
     * @return void
     * 
     */
    public function writeClassMethods($class)
    {
        $text = array();
        
        $list = array(
            'public' => array(),
            'protected' => array(),
            'private' => array(),
        );
        
        foreach ($this->api[$class]['methods'] as $name => $info) {
            
            $summ = trim($info['summ']);
            if (! $summ) {
                $summ = '-?-';
            }
            $list[$info['access']][] = "[[$class::$name() | `$name()`]]\n: $summ\n";
        }
        
        foreach ($list as $access => $methods) {
            
            $text[] = $this->_title2(ucfirst($access));
            
            if ($methods) {
                $text = array_merge($text, $methods);
            } else {
                $text[] = 'None.';
                $text[] = '';
            }
            
            $text[] = '';
        }
        
        $this->_write("class", "$class/Methods", $text);
    }
    
    /**
     * 
     * Writes an individual method file.
     * 
     * @param string $class The class to which the method belongs. 
     * 
     * @param string $name The method name. 
     * 
     * @param array $info Information about the method.
     * 
     * @return void
     * 
     */
    public function writeClassMethod($class, $name, $info)
    {
        $text = array();
        
        // method synopsis
        $text[] = '{{method: ' . $class . '::' . $info['name'];
        $tmp = "{$info['final']} {$info['static']} {$info['access']}";
        $tmp = preg_replace('/ {2,}/', ' ', trim($tmp));
        $text[] = "    @access $tmp";
        
        $params = array();
        foreach ($info['params'] as $val) {
            $tmp = "    @param {$val['type']}, ";
            if ($val['byref']) {
                $tmp .= '&';
            }
            $tmp .= '$' . $val['name'];
            if ($val['optional']) {
                $tmp .= ', ' . str_replace("\n", '', var_export($val['default'], true));
            }
            $text[] = $tmp;
            
            // add for the parameter description list
            if ($val['byref']) {
                $params[] = "* _({$val['type']})_ `&\${$val['name']}`: {$val['summ']}";
            } else {
                $params[] = "* _({$val['type']})_ `\${$val['name']}`: {$val['summ']}";
            }
        }
        
        $text[] = "    @return {$info['return']}";
        $text[] = "}}";
        $text[] = '';
        
        // summary line
        $tmp = trim($info['summ']);
        if ($tmp) {
            $text[] = $tmp;
        } else {
            $text[] = '-?-';
        }
        $text[] = '';
        
        // inherited?
        if ($info['from']) {
            $text[] = "Inherited from [[{$info['from']}::$name() | {$info['from']}]].";
            $text[] = '';
        }
        
        // parameter list
        $text[] = $this->_title2('Parameters');
        if ($params) {
            $text = array_merge($text, $params);
        } else {
            $text[] = "* None.";
        }
        $text[] = '';
        
        // return value
        $text[] = $this->_title2('Returns');
        if (! empty($info['return'])) {
            $tmp = "* _({$info['return']})_";
            if (! empty($info['tech']['return']['summ'])) {
                $tmp .= " {$info['tech']['return']['summ']}";
            }
            $text[] = $tmp;
        }
        $text[] = '';
        
        // @todo THROWS
        
        // narrative description
        $text[] = $this->_title2('Description');
        if (! trim($info['summ']) && ! trim($info['narr'])) {
            $text[] = '-?-';
            $text[] = '';
        } else {
            $text[] = trim($info['summ']);
            $text[] = '';
            $text[] = trim($info['narr']);
            $text[] = '';
        }
        
        // see-also
        if (! empty($info['tech']['see'])) {
            $text[] = $this->_title2('See Also');
            foreach ((array) $info['tech']['see'] as $val) {
                // allow for external links
                $is_wiki = $val[0] == '[';
                $is_link = $val[0] == '<';
                if (! $is_wiki && ! $is_link) {
                    // are there double-colons already?
                    if (strpos($val, '::') === false) {
                        // no colons
                        $val = "[[$class::$val | $val]]";
                    } else {
                        // has colons
                        $val = "[[$val]]";
                    }
                }
                $text[] = "* $val";
            }
        }
        
        // done
        $this->_write("class", "$class/$name()", $text);
    }
    
    /**
     * 
     * Returns level-1 title markup.
     * 
     * @param string $text The title text.
     * 
     * @return string
     * 
     */
    protected function _title1($text)
    {
        return str_pad('', strlen($text), '=') . "\n"
             . $text . "\n"
             . str_pad('', strlen($text), '=') . "\n";
    }
    
    /**
     * 
     * Returns level-2 title markup.
     * 
     * @param string $text The title text.
     * 
     * @return string
     * 
     */
    protected function _title2($text)
    {
        return $text . "\n"
             . str_pad('', strlen($text), '=') . "\n";
    }
    
    /**
     * 
     * Returns level-3 title markup.
     * 
     * @param string $text The title text.
     * 
     * @return string
     * 
     */
    protected function _title3($text)
    {
        return $text . "\n"
             . str_pad('', strlen($text), '-') . "\n";
    }
    
    /**
     * 
     * Writes a file to the target directory.
     * 
     * @param string $type The type of file to write: 'class' or 'package'.
     * 
     * @param string $file A relative file name, e.g. "class/Class_Name/Overview".
     * 
     * @param mixed $text A text string or array to write to the file; if an
     * array, is imploded with newlines and trimmed before writing.
     * 
     * @return void
     * 
     */
    protected function _write($type, $file, $text)
    {
        if (is_array($text)) {
            $text = implode("\n", $text);
        }
        
        $file = $this->_getFile($type, $file);
        $dir = dirname($file);
        
        if (file_exists($dir) && ! is_dir($dir)) {
            throw $this->_exception('ERR_CANNOT_MKDIR_FILE', array(
                'dir' => $dir,
            ));
        }
        
        if (! is_dir($dir)) {
            $result = mkdir($dir, 0777, true);
            if (! $result) {
                throw $this->_exception('ERR_MKDIR_FAILED', array(
                    'dir' => $dir,
                    'file' => $file,
                ));
            }
        }
        
        file_put_contents($file, $text);
    }
    
    /**
     * 
     * Touches a file to create it or update its timestamp.
     * 
     * @param string $type The type of file to write: 'class' or 'package'.
     * 
     * @param string $file A relative file name, e.g. "class/Class_Name/Overview".
     * 
     * @return void
     * 
     */
    protected function _touch($type, $file)
    {
        $file = $this->_getFile($type, $file);
        touch($file);
    }
    
    /**
     * 
     * Builds a target filename path in the 'class' or 'package' directory.
     * 
     * @param string $type The type of file to work with: 'class' or 'package'.
     * 
     * @param string $file A relative file name, e.g. "class/Class_Name/Overview".
     * 
     * @return string
     * 
     */
    protected function _getFile($type, $file)
    {
        if ($type == 'class') {
            $file = $this->_class_dir . "$file";
        }
        
        if ($type == 'package') {
            $file = $this->_package_dir . "$file";
        }
        
        return $file;
    }
}
