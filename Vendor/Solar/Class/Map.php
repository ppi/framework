<?php
/**
 * 
 * Creates an array of class-to-file mappings for a class hierarchy.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Map.php 4416 2010-02-23 19:52:43Z pmjones $
 * 
 */
class Solar_Class_Map extends Solar_Base
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string base The base directory of the class hierarchy.  Default is the
     *   base directory for this class, typically the PEAR directory.
     * 
     * @var array
     * 
     */
    protected $_Solar_Class_Map = array(
        'base' => null,
    );
    
    /**
     * 
     * The class-to-file mappings.
     * 
     * @var array
     * 
     */
    protected $_map = array();
    
    /**
     * 
     * The path to the base of the class hierarchy.
     * 
     * By default, uses the base path for this class in the file system.
     * 
     * @var string
     * 
     */
    protected $_base;
    
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
        if ($this->_config['base']) {
            $this->setBase($base);
        } else {
            $base = $this->_findBaseByClass(__CLASS__);
            $this->setBase($base);
        }
    }
    
    /**
     * 
     * Sets the base directory for the class map.
     * 
     * @param string $base The base directory.
     * 
     * @return void
     * 
     */
    public function setBase($base)
    {
        $this->_base = Solar_Dir::fix($base);
    }
    
    /**
     * 
     * Gets the base directory for the class map.
     * 
     * @return string The base directory.
     * 
     */
    public function getBase()
    {
        return $this->_base;
    }
    
    /**
     * 
     * Gets the class-to-file map for a class hierarchy.
     * 
     * @param string $class Start mapping with this class.
     * 
     * @return array The class-to-file mappings.
     * 
     */
    public function fetch($class = null)
    {
        // reset the map
        $this->_map = array();
        
        // if starting with a specific class, add to the path
        // and look for that file specifically.
        if ($class) {
            
            // add to the base path for the file
            $path = $this->_base
                  . str_replace('_', DIRECTORY_SEPARATOR, $class);
            
            // which file would the class be in?
            $file = $path . '.php';
            
            // add the mapping if the file exists
            if (file_exists($file)) {
                $this->_map[$class] = $file;
            }
            
            // append a directory separator so we can descend into
            // the child classes of the requested class.
            $path .= DIRECTORY_SEPARATOR;
            
        } else {
            
            // start at the top of the hierarchy using the "fixed"
            // base path.
            $path = $this->_base;
            
        }
        
        // now build the subdirectory class-to-file mappings, if the subdir
        // actually exists
        if (is_dir($path)) {
            $iter = new RecursiveDirectoryIterator($path);
            $this->_fetch($iter);
        }
        
        // sort by class name, and we're done.
        ksort($this->_map);
        return $this->_map;
    }
    
    /**
     * 
     * Finds the base directory from the include-path to the requested class.
     * 
     * @param string $class The requested class file.
     * 
     * @return string The base directory.
     * 
     */
    protected function _findBaseByClass($class)
    {
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        $base = Solar_File::exists($file);
        if ($base) {
            $neglen = -1 * strlen($file);
            $base = substr($base, 0, $neglen);
            return $base;
        }
        
        // no base yet, look for a dir (drop the .php, add a separator)
        $dir = substr($file, 0, -4);
        $base = Solar_Dir::exists($dir);
        if ($base) {
            $neglen = -1 * strlen($dir);
            $base = substr($base, 0, $neglen);
            return $base;
        }
        
        // still no base, we have a problem
        throw $this->_exception('ERR_NO_BASE_DIR', array(
            'class' => $class,
        ));
    }
    
    /**
     * 
     * Recursively iterates through a directory looking for class files.
     * 
     * Skips CVS directories, and all files and dirs not starting with
     * a capital letter (such as dot-files).
     * 
     * @param RecursiveDirectoryIterator $iter Directory iterator.
     * 
     * @return void
     * 
     */
    protected function _fetch(RecursiveDirectoryIterator $iter)
    {
        for ($iter->rewind(); $iter->valid(); $iter->next()) {
            
            // preliminaries
            $path    = $iter->current()->getPathname();
            $file    = basename($path);
            $capital = ctype_alpha($file[0]) && $file == ucfirst($file);
            $phpfile = strripos($file, '.php');
            
            // check for valid class files
            if ($iter->isDot() || ! $capital) {
                // skip dot-files (including dot-file directories), as
                // well as files/dirs not starting with a capital letter
                continue;
            } elseif ($iter->isDir() && $file == 'CVS') {
                // skip CVS directories
                continue;
            } elseif ($iter->isDir() && $iter->hasChildren()) {
                // descend into child directories
                $this->_fetch($iter->getChildren());
            } elseif ($iter->isFile() && $phpfile) {
                // map the .php file to a class name
                $len   = strlen($this->_base);
                $class = substr($path, $len, -4); // drops .php
                $class = str_replace(DIRECTORY_SEPARATOR, '_', $class);
                $this->_map[$class] = $path;
            }
        }
    }
}