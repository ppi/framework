<?php
/**
 * 
 * Solar command to make a command-controller CLI structure.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: MakeCli.php 4436 2010-02-25 21:38:34Z pmjones $
 * 
 */
class Solar_Cli_MakeCli extends Solar_Controller_Command
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string extends The default command-controller class to extend.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cli_MakeCli = array(
        'extends' => null,
    );
    
    /**
     * 
     * The base directory where we will write the class file to, typically
     * the local PEAR directory.
     * 
     * @var string
     * 
     */
    protected $_target = null;
    
    /**
     * 
     * The name of the CLI class.
     * 
     * @var string
     * 
     */
    protected $_class;
    
    /**
     * 
     * The directory for the CLI class.
     * 
     * @var string
     * 
     */
    protected $_class_dir;
    
    /**
     * 
     * The filename for the CLI class.
     * 
     * @var string
     * 
     */
    protected $_class_file;
    
    /**
     * 
     * What base command to extend?
     * 
     * @var string
     * 
     */
    protected $_extends = null;
    
    /**
     * 
     * Array of class templates (skeletons).
     * 
     * @var array
     * 
     */
    protected $_tpl = array();
    
    /**
     * 
     * Write out a series of files and dirs for a page-controller.
     * 
     * @param string $class The target class name for the app.
     * 
     * @return void
     * 
     */
    protected function _exec($class = null)
    {
        // we need a class name, at least
        if (! $class) {
            throw $this->_exception('ERR_NO_CLASS');
        } else {
            $this->_class = $class;
        }
        
        $this->_outln('Making CLI command.');
        
        // we need a target directory
        $this->_setTarget();
        
        // extending which class?
        $this->_setExtends($class);
        
        // load the templates
        $this->_loadTemplates();
        
        // the class file locations
        $this->_class_file = $this->_target
            . str_replace('_', DIRECTORY_SEPARATOR, $this->_class)
            . '.php';
        
        // the class dir location
        $this->_class_dir = Solar_Dir::fix(
            $this->_target . str_replace('_', '/', $this->_class)
        );
        
        // create the Locale and Info dirs
        $this->_createDirs();
        
        // write the CLI class itself
        $this->_writeCliClass();
        
        // write Locale/en_US.php
        $this->_writeLocale();
        
        // write Info/help.txt
        $this->_writeInfoHelp();
        
        // write Info/options.php
        $this->_writeInfoOptions();
        
        // done!
        $this->_outln("Done.");
    }
    
    /**
     * 
     * Writes the application class file itself.
     * 
     * @return void
     * 
     */
    protected function _writeCliClass()
    {
        // emit feedback
        $this->_outln("CLI class '{$this->_class}' extends '{$this->_extends}'.");
        $this->_outln("Preparing to write to '{$this->_target}'.");
        
        // get the cli class template
        $tpl_key = 'cli';
        $text = $this->_parseTemplate($tpl_key);
        
        // write the cli class
        if (file_exists($this->_class_file)) {
            $this->_outln('CLI class already exists.');
        } else {
            $this->_outln('Writing CLI class.');
            file_put_contents($this->_class_file, $text);
        }
    }
    
    /**
     * 
     * Creates the CLI directories.
     * 
     * @return void
     * 
     */
    protected function _createDirs()
    {
        $dir = $this->_class_dir;
        
        if (! file_exists($dir)) {
            $this->_outln('Creating CLI directory.');
            mkdir($dir, 0755, true);
        } else {
            $this->_outln('CLI directory exists.');
        }
        
        $list = array('Info', 'Locale');
        
        foreach ($list as $sub) {
            if (! file_exists("$dir/$sub")) {
                $this->_outln("Creating CLI $sub directory.");
                mkdir("$dir/$sub", 0755, true);
            } else {
                $this->_outln("CLI $sub directory exists.");
            }
        }
    }
    
    /**
     * 
     * Writes the `Locale/en_US.php` locale file.
     * 
     * @return void
     * 
     */
    protected function _writeLocale()
    {
        $text = $this->_tpl['locale'];
        $file = $this->_class_dir . DIRECTORY_SEPARATOR . "/Locale/en_US.php";
        if (file_exists($file)) {
            $this->_outln('Locale file exists.');
        } else {
            $this->_outln('Writing locale file.');
            file_put_contents($file, $text);
        }
    }
    
    /**
     * 
     * Writes the `Info/help.txt` file.
     * 
     * @return void
     * 
     */
    protected function _writeInfoHelp()
    {
        $text = $this->_tpl['help'];
        $file = $this->_class_dir . DIRECTORY_SEPARATOR . "/Info/help.txt";
        if (file_exists($file)) {
            $this->_outln('Help file exists.');
        } else {
            $this->_outln('Writing help file.');
            file_put_contents($file, $text);
        }
    }
    
    /**
     * 
     * Writes the `Info/options.php` file.
     * 
     * @return void
     * 
     */
    protected function _writeInfoOptions()
    {
        $text = $this->_tpl['options'];
        $file = $this->_class_dir . DIRECTORY_SEPARATOR . "/Info/options.php";
        if (file_exists($file)) {
            $this->_outln('Options file exists.');
        } else {
            $this->_outln('Writing options file.');
            file_put_contents($file, $text);
        }
    }
    
    /**
     * 
     * Parses a template and sets placeholder values.
     * 
     * @param string $key The template array key.
     * 
     * @return string The template with placeholder values set.
     * 
     */
    protected function _parseTemplate($key)
    {
        $data = array(
            '{:class}'          => $this->_class,
            '{:extends}'        => $this->_extends,
        );
        
        return str_replace(
            array_keys($data),
            array_values($data),
            $this->_tpl[$key]
        );
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
        $dir = Solar_Class::dir($this, 'Data');
        $list = glob($dir . '*.php');
        foreach ($list as $file) {
            
            // strip .php off the end of the file name to get the key
            $key = substr(basename($file), 0, -4);
            
            // load the file template
            $this->_tpl[$key] = file_get_contents($file);
            
            // we need to add the php-open tag ourselves, instead of
            // having it in the template file, becuase the PEAR packager
            // complains about parsing the skeleton code.
            // 
            // however, only do this on non-help files.
            if ($key != 'help') {
                $this->_tpl[$key] = "<?php\n" . $this->_tpl[$key];
            }
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
        // use the solar system "include" directory.
        // that should automatically point to the right vendor for us.
        $target = Solar::$system . "/include";
        $this->_target = Solar_Dir::fix($target);
    }
    
    /**
     * 
     * Sets the class this app will extend from.
     * 
     * @param string $class The app class name.
     * 
     * @return void
     * 
     */
    protected function _setExtends($class)
    {
        // explicit as cli option?
        $extends = $this->_options['extends'];
        if ($extends) {
            $this->_extends = $extends;
            return;
        }
        
        // explicit as a config value?
        $extends = $this->_config['extends'];
        if ($extends) {
            $this->_extends = $extends;
            return;
        }
        
        // look at the vendor name and find a controller class
        $vendor = Solar_Class::vendor($class);
        $name = "{$vendor}_Controller_Command";
        $file = $this->_target . "$vendor/Controller/Command.php";
        if (file_exists($file)) {
            $this->_extends = $name;
            return;
        }
        
        // final fallback: Solar_Controller_Command
        $this->_extends = 'Solar_Controller_Command';
    }
}
