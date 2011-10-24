<?php
/**
 * 
 * Solar command to make a page-controller app structure.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: MakeApp.php 4436 2010-02-25 21:38:34Z pmjones $
 * 
 */
class Solar_Cli_MakeApp extends Solar_Controller_Command
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string extends The default page-controller class to extend.
     * 
     * @config string extends_model The page-controller class to extend when
     * a model name is the basis for the app.
     * 
     * @var array
     * 
     */
    protected $_Solar_Cli_MakeApp = array(
        'extends'       => null,
        'extends_model' => null,
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
     * The name of the app class.
     * 
     * @var string
     * 
     */
    protected $_class;
    
    /**
     * 
     * The directory for the app class.
     * 
     * @var string
     * 
     */
    protected $_class_dir;
    
    /**
     * 
     * The filename for the app class.
     * 
     * @var string
     * 
     */
    protected $_class_file;
    
    /**
     * 
     * What base app to extend?
     * 
     * @var string
     * 
     */
    protected $_extends = null;
    
    /**
     * 
     * The model name for the model class.
     * 
     * @var string
     * 
     */
    protected $_model_name = null;
    
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
        
        $this->_outln('Making app.');
        
        // we need a target directory
        $this->_setTarget();
        
        // using a model?
        $this->_setModelName();
        
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
        
        // create the View, Locale, Helper, Layout dirs
        $this->_createDirs();
        
        // write the app class itself
        $this->_writeAppClass();
        
        // write Locale/en_US.php
        $this->_writeLocale();
        
        // write files in the View dir
        $this->_writeViews();
        
        // link public dir for app
        $link_public = Solar::factory('Solar_Cli_LinkPublic');
        $link_public->exec($class);
        
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
    protected function _writeAppClass()
    {
        // emit feedback
        $this->_outln("App class '{$this->_class}' extends '{$this->_extends}'.");
        $this->_outln("Preparing to write to '{$this->_target}'.");
        
        // using app, or app-model?
        if ($this->_model_name) {
            $tpl_key = 'app-model';
        } else {
            $tpl_key = 'app';
        }
        
        // get the app class template
        $text = $this->_parseTemplate($tpl_key);
        
        // write the app class
        if (file_exists($this->_class_file)) {
            $this->_outln('App class already exists.');
        } else {
            $this->_outln('Writing app class.');
            file_put_contents($this->_class_file, $text);
        }
    }
    
    /**
     * 
     * Creates the application directories..
     * 
     * @return void
     * 
     */
    protected function _createDirs()
    {
        $dir = $this->_class_dir;
        
        if (! file_exists($dir)) {
            $this->_outln('Creating app directory.');
            mkdir($dir, 0755, true);
        } else {
            $this->_outln('App directory exists.');
        }
        
        $list = array('Layout', 'Locale', 'Public', 'View');
        
        foreach ($list as $sub) {
            if (! file_exists("$dir/$sub")) {
                $this->_outln("Creating app $sub directory.");
                mkdir("$dir/$sub", 0755, true);
            } else {
                $this->_outln("App $sub directory exists.");
            }
        }
    }
    
    /**
     * 
     * Writes the en_US application locale file.
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
     * Writes the application view files.
     * 
     * @return void
     * 
     */
    protected function _writeViews()
    {
        if (! $this->_model_name) {
            $list = array('index');
        } else {
            $list = array();
        }
        
        foreach ($list as $view) {
            
            $text = $this->_parseTemplate("view-$view");
            
            $file = $this->_class_dir . "/View/$view.php";
            if (file_exists($file)) {
                $this->_outln("View '$view' exists.");
            } else {
                $this->_outln("Writing '$view' view.");
                file_put_contents($file, $text);
            }
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
            '{:model_name}'     => $this->_model_name,
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
            // however, only do this on non-view files.
            if (substr($key, 0, 4) != 'view') {
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
        
        // explicit as config value?
        if ($this->_model_name) {
            $extends = $this->_config['extends_model'];
        } else {
            $extends = $this->_config['extends'];
        }
        if ($extends) {
            $this->_extends = $extends;
            return;
        }
        
        // look at the vendor name and find a controller class
        $vendor = Solar_Class::vendor($class);
        if ($this->_model_name) {
            $name = "{$vendor}_Controller_Bread";
            $file = $this->_target . "$vendor/Controller/Bread.php";
        } else {
            $name = "{$vendor}_Controller_Page";
            $file = $this->_target . "$vendor/Controller/Page.php";
        }
        if (file_exists($file)) {
            $this->_extends = $name;
            return;
        }
        
        // final fallback: Solar_Controller_Page
        $this->_extends = 'Solar_Controller_Page';
        return;
    }
    
    /**
     * 
     * Sets the model class and var name the app class will use.
     * 
     * @return void
     * 
     */
    protected function _setModelName()
    {
        $model_name = $this->_options['model_name'];
        if ($model_name) {
            $this->_model_name = $model_name;
        } else {
            $this->_model_name = null;
        }
    }
}
