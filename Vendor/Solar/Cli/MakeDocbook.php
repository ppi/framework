<?php
/**
 * 
 * Generates package and API documentation files.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @subpackage Solar_Cli_MakeDocbook
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: MakeDocbook.php 4611 2010-06-19 09:13:31Z pmjones $
 * 
 */
class Solar_Cli_MakeDocbook extends Solar_Controller_Command
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string class_dir The directory in which wiki-based class docs
     * are stored.
     * 
     * @config string package_dir The directory in which wiki-based package 
     * docs are stored.
     * 
     * @config string docbook_dir The directory in which to save DocBook
     * files.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cli_MakeDocbook = array(
        'class_dir'   => null,
        'package_dir' => null,
        'docbook_dir' => null,
    );
    
    /**
     * 
     * Read class API source files from this directory.
     * 
     * @var string
     * 
     */
    protected $_class_dir;
    
    /**
     * 
     * Read package source files from this directory.
     * 
     * @var string
     * 
     */
    protected $_package_dir;
    
    /**
     * 
     * Write docbook files to this directory.
     * 
     * @var string
     * 
     */
    protected $_docbook_dir;
    
    /**
     * 
     * A Solar_Markdown object for converting wiki docs to DocBook.
     * 
     * @var Solar_Markdown_Apidoc
     * 
     */
    protected $_markdown;
    
    /**
     * 
     * An array of DocBook template files.
     * 
     * @var array
     * 
     */
    protected $_templates;
    
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
        
        $this->_markdown = Solar::factory('Solar_Markdown_Apidoc');
    }
    
    /**
     * 
     * Main action: parse the classes and write documentation.
     * 
     * @param string $vendor Parse for this vendor name.
     * 
     * @return void
     * 
     */
    protected function _exec($vendor = null)
    {
        $begin = time();
        
        if (! $vendor) {
            $vendor = 'Solar';
        }
        
        $this->_setDirs();
        $this->_loadTemplates();
        
        // convert
        $this->_outln('Convert docs to docbook files.');
        $this->_buildFoundation($vendor);
        $this->_convertPackages();
        $this->_convertClasses();
        
        // done!
        $time = time() - $begin;
        $this->_outln("Wiki docs converted to DocBook in $time seconds.");
    }
    
    /**
     * 
     * Sets directory paths for reading and writing.
     * 
     * @return void
     * 
     */
    protected function _setDirs()
    {
        // get the source class dir
        $class_dir = $this->_options['class_dir'];
        if ($class_dir) {
            $this->_class_dir = Solar_Dir::fix($class_dir);
        }
        
        // do we have a class dir?
        if (! $this->_class_dir) {
            throw $this->_exception('ERR_NO_CLASS_DIR');
        }
        
        // get the source package dir (if any)
        $package_dir = $this->_options['package_dir'];
        if ($package_dir) {
            $this->_package_dir = Solar_Dir::fix($package_dir);
        }
        
        // do we have a package dir?
        if (! $this->_package_dir) {
            throw $this->_exception('ERR_NO_PACKAGE_DIR');
        }
        
        // get the target docbook dir (if any)
        $docbook_dir = $this->_options['docbook_dir'];
        if ($docbook_dir) {
            $this->_docbook_dir = Solar_Dir::fix($docbook_dir);
        }
        
        // do we have a docbook dir?
        if (! $this->_docbook_dir) {
            throw $this->_exception('ERR_NO_DOCBOOK_DIR');
        }
    }
    
    /**
     * 
     * Loads DocBook template files from the Data directory.
     * 
     * @return void
     * 
     */
    protected function _loadTemplates()
    {
        $dir = Solar_Class::dir($this, 'Data');
        $list = glob("$dir/*.txt");
        foreach ($list as $file) {
            $name = basename($file);
            $name = str_replace('.txt', '.xml', $name);
            $this->_templates[$name] = file_get_contents($file);
        }
    }
    
    /**
     * 
     * Writes the top-level apidoc DocBook files.
     * 
     * @param string $vendor The top-level vendor class we're building API
     * docs for.
     * 
     * @return void
     * 
     */
    protected function _buildFoundation($vendor)
    {
        $this->_out("Build foundation ... ");
        $tmpl = 'apidoc.xml';
        $data = array('{:vendor}' => $vendor);
        $file = 'apidoc.xml';
        $this->_save($tmpl, $data, $file);
        $this->_outln("done.");
    }
    
    /**
     * 
     * Converts all wiki-based package docs to DocBook.
     * 
     * @return void
     * 
     */
    protected function _convertPackages()
    {
        $list = glob("{$this->_package_dir}/*");
        foreach ($list as $key => $val) {
            $name = basename($val);
            $list[$key] = $name;
        }
        $key = array_search('index', $list);
        unset($list[$key]);
        
        $this->_out('Converting packages ... ');
        $this->_convertPackageToc($list);
        $this->_convertPackageIndex($list);
        
        foreach ($list as $package) {
            $this->_convertPackage($package);
        }
        
        $this->_outln('done.');
    }
    
    /**
     * 
     * Converts the wiki-based package table of contents to DocBook.
     * 
     * @param array $list The list of package names.
     * 
     * @return void
     * 
     */
    protected function _convertPackageToc($list)
    {
        $xinc = array();
        foreach ($list as $package) {
            $href = "package/$package.xml";
            $xinc[] = "    <xi:include href=\"$href\" />";
        }
        $xinc = implode("\n", $xinc);
        
        $tmpl = 'package.xml';
        $data = array('{:xinc}' => $xinc);
        $file = 'apidoc/package.xml';
        $this->_save($tmpl, $data, $file);
    }
    
    /**
     * 
     * Converts the wiki-based package index to DocBook.
     * 
     * @param array $list The list of package names.
     * 
     * @return void
     * 
     */
    protected function _convertPackageIndex($list)
    {
        $packages = file_get_contents("{$this->_package_dir}/index");
        
        $tmpl = 'package-index.xml';
        $data = array(
            '{:packages}' => $packages,
        );
        $file = 'apidoc/package/index.xml';
        $this->_saveMarkdown($tmpl, $data, $file);
    }
    
    /**
     * 
     * Converts a single wiki-based package file to DocBook.
     * 
     * @param string $package The package name.
     * 
     * @return void
     * 
     */
    protected function _convertPackage($package)
    {
        $tmpl = 'package-overview.xml';
        $data = array(
            '{:name}' => $package,
            '{:info}' => file_get_contents("$this->_package_dir/$package"),
        );
        $file = "apidoc/package/$package.xml";
        $this->_saveMarkdown($tmpl, $data, $file);
    }
    
    /**
     * 
     * Converts all wiki-based class files to DocBook.
     * 
     * @return void
     * 
     */
    protected function _convertClasses()
    {
        $list = glob("{$this->_class_dir}/*");
        foreach ($list as $key => $val) {
            $name = basename($val);
            $list[$key] = $name;
        }
        $key = array_search('index', $list);
        unset($list[$key]);
        
        $this->_out("Converting classes TOC and index ... ");
        $this->_convertClassToc($list);
        $this->_convertClassIndex($list);
        $this->_outln('done.');
        
        $this->_outln("Convert classes.");
        foreach ($list as $class) {
            $this->_convertClass($class);
        }
    }
    
    /**
     * 
     * Converts all wiki-based class files to DocBook.
     * 
     * @param array $list The list of class names.
     * 
     * @return void
     * 
     */
    protected function _convertClassToc($list)
    {
        $xinc = array();
        foreach ($list as $class) {
            $href = "class/$class.xml";
            $xinc[] = "    <xi:include href=\"$href\" />";
        }
        $xinc = implode("\n", $xinc);
        
        $tmpl = 'class.xml';
        $data = array('{:xinc}' => $xinc);
        $file = 'apidoc/class.xml';
        $this->_save($tmpl, $data, $file);
    }
    
    /**
     * 
     * Converts the wiki-based class index to DocBook.
     * 
     * @param array $list The list of package names.
     * 
     * @return void
     * 
     */
    protected function _convertClassIndex($list)
    {
        $classes = file_get_contents("{$this->_class_dir}/index");
        
        $tmpl = 'class-index.xml';
        $data = array(
            '{:classes}' => $classes,
        );
        $file = 'apidoc/class/index.xml';
        $this->_saveMarkdown($tmpl, $data, $file);
    }
    
    /**
     * 
     * Converts a single wiki-based class file set to DocBook.
     * 
     * @param string $class The class file set to convert.
     * 
     * @return void
     * 
     */
    protected function _convertClass($class)
    {
        $this->_out("$class ... ");
        
        // file set for the class
        $list = glob("{$this->_class_dir}/$class/*");
        
        // toc: part 1
        $xinc = array();
        $skip = array('Overview', 'Config', 'Constants', 'Properties', 'Methods');
        foreach ($skip as $name) {
            if ($name == 'index') {
                continue;
            }
            $xinc[] = "    <xi:include href=\"$class/$name.xml\" />";
        }
        
        // toc: part 2
        $skip[] = 'index';
        foreach ($list as $name) {
            $name = preg_replace('[^A-Za-z0-9_]', '', basename($name));
            if (in_array($name, $skip)) {
                continue;
            }
            $xinc[] = "    <xi:include href=\"$class/$name.xml\" />";
        }
        
        // write toc
        $xinc = implode("\n", $xinc);
        $tmpl = 'class-toc.xml';
        $data = array(
            '{:class}' => $class,
            '{:xinc}' => $xinc,
        );
        $file = "apidoc/class/$class.xml";
        $this->_save($tmpl, $data, $file);
        
        // overview
        $tmpl = 'class-overview.xml';
        $data = array(
            '{:class}' => $class,
            '{:info}' => file_get_contents("$this->_class_dir/$class/Overview"),
        );
        $file = "apidoc/class/$class/Overview.xml";
        $this->_saveMarkdown($tmpl, $data, $file);
        
        // config
        $tmpl = 'class-config.xml';
        $data = array(
            '{:class}' => $class,
            '{:info}' => file_get_contents("$this->_class_dir/$class/Config"),
        );
        $file = "apidoc/class/$class/Config.xml";
        $this->_saveMarkdown($tmpl, $data, $file);
        
        // constants
        $tmpl = 'class-constants.xml';
        $data = array(
            '{:class}' => $class,
            '{:info}' => file_get_contents("$this->_class_dir/$class/Constants"),
        );
        $file = "apidoc/class/$class/Constants.xml";
        $this->_saveMarkdown($tmpl, $data, $file);
        
        // properties
        $tmpl = 'class-properties.xml';
        $data = array(
            '{:class}' => $class,
            '{:info}' => file_get_contents("$this->_class_dir/$class/Properties"),
        );
        $file = "apidoc/class/$class/Properties.xml";
        $this->_saveMarkdown($tmpl, $data, $file);
        
        // all methods
        $tmpl = 'class-methods.xml';
        $data = array(
            '{:class}' => $class,
            '{:info}' => file_get_contents("$this->_class_dir/$class/Methods"),
        );
        $file = "apidoc/class/$class/Methods.xml";
        $this->_saveMarkdown($tmpl, $data, $file);
        
        // individual methods
        $skip = array('index', 'Overview', 'Config', 'Constants', 'Properties', 'Methods');
        foreach ($list as $method) {
            $method = basename($method);
            if (in_array($method, $skip)) {
                continue;
            }
            
            $tmpl = 'class-method.xml';
            $name = preg_replace('/[^A-Za-z0-9_]/', '', $method);
            $data = array(
                '{:class}' => $class,
                '{:method}' => $method,
                '{:xmlid}' => $name,
                '{:info}' => file_get_contents("$this->_class_dir/$class/$method"),
            );
            
            $file = "apidoc/class/$class/$method.xml";
            
            $this->_saveMarkdown($tmpl, $data, $file);
        }
        
        $this->_outln('done.');
    }
    
    /**
     * 
     * Writes a file to the target directory.
     * 
     * @param string $tmpl The XML template to use.
     * 
     * @param array $data Data to populate into the template.
     * 
     * @param string $file The DocBook file name.
     * 
     * @return void
     * 
     */
    protected function _save($tmpl, $data, $file)
    {
        // save to file
        $file = "{$this->_docbook_dir}/$file";
        $dir = dirname($file);
        if (! is_dir($dir)) {
            $result = mkdir($dir, 0777, true);
            if (! $result) {
                throw $this->_exception('ERR_MKDIR_FAILED', array(
                    'dir' => $dir,
                    'file' => $file,
                ));
            }
        }
        
        // interpolate into the template
        $text = $this->_templates[$tmpl];
        $text = str_replace(
            array_keys($data),
            array_values($data),
            $text
        );
        
        // save to the target file
        file_put_contents($file, $text);
    }
    
    /**
     * 
     * Transforms from wiki markup to DocBook, and saves it to the target 
     * directory.
     * 
     * @param string $tmpl The XML template to use.
     * 
     * @param array $data Data to populate into the template.
     * 
     * @param string $file The DocBook file name.
     * 
     * @return void
     * 
     */
    protected function _saveMarkdown($tmpl, $data, $file)
    {
        foreach ($data as $key => $val) {
            // only transform if there is at least one newline in it
            if (strpos($val, "\n")) {
                $data[$key] = $this->_markdown->transform($val);
            }
        }
        $this->_save($tmpl, $data, $file);
    }
}
