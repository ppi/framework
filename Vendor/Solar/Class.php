<?php
/**
 * 
 * Static support methods for class information.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Class.php 4370 2010-02-11 15:41:19Z pmjones $
 * 
 */
class Solar_Class
{
    /**
     * 
     * Parent hierarchy for all classes.
     * 
     * We keep track of this so configs, locale strings, etc. can be
     * inherited properly from parent classes, and so we don't need to
     * recalculate it on each request.
     * 
     * @var array
     * 
     */
    protected static $_parents = array();
    
    /**
     * 
     * Loads a class or interface file from the include_path.
     * 
     * Thanks to Robert Gonzalez  for the report leading to this method.
     * 
     * @param string $name A Solar (or other) class or interface name.
     * 
     * @return void
     * 
     * @todo Add localization for errors
     * 
     */
    public static function autoload($name)
    {
        // did we ask for a non-blank name?
        if (trim($name) == '') {
            throw Solar::exception(
                'Solar_Class',
                'ERR_AUTOLOAD_EMPTY',
                'No class or interface named for loading.',
                array('name' => $name)
            );
        }
        
        // pre-empt further searching for the named class or interface.
        // do not use autoload, because this method is registered with
        // spl_autoload already.
        $exists = class_exists($name, false)
               || interface_exists($name, false);
        
        if ($exists) {
            return;
        }
        
        // convert the class name to a file path
        $file = Solar_Class::nameToFile($name);
        
        // include the file and check for failure. we use Solar_File::load()
        // instead of require() so we can see the exception backtrace.
        Solar_File::load($file);
        
        // if the class or interface was not in the file, we have a problem.
        // do not use autoload, because this method is registered with
        // spl_autoload already.
        $exists = class_exists($name, false)
               || interface_exists($name, false);
        
        if (! $exists) {
            throw Solar::exception(
                'Solar_Class',
                'ERR_AUTOLOAD_FAILED',
                'Class or interface does not exist in loaded file',
                array('name' => $name, 'file' => $file)
            );
        }
    }
    
    /**
     * 
     * Converts a namespace-and-classname to a file path.
     * 
     * Implements PSR-0 as defined by the PHP Project Interoperability Group.
     * 
     * <http://groups.google.com/group/php-standards/web/final-proposal>
     * 
     * @param string $spec The namespace-and-classname.
     * 
     * @return string The converted file path.
     * 
     */
    public static function nameToFile($spec)
    {
        // using namespaces? (look for last namespace separator)
        $pos = strrpos($spec, '\\');
        if ($pos === false) {
            // no namespace, class portion only
            $namespace = '';
            $class     = $spec;
        } else {
            // pre-convert namespace portion to file path
            $namespace = substr($spec, 0, $pos);
            $namespace = str_replace('\\', DIRECTORY_SEPARATOR, $namespace)
                       . DIRECTORY_SEPARATOR;
            
            // class portion
            $class = substr($spec, $pos + 1);
        }
        
        // convert class underscores, and done
        return $namespace
             . str_replace('_',  DIRECTORY_SEPARATOR, $class)
             . '.php';
    }
    
    /**
     * 
     * Returns an array of the parent classes for a given class.
     * 
     * @param string|object $spec The class or object to find parents
     * for.
     * 
     * @param bool $include_class If true, the class name is element 0,
     * the parent is element 1, the grandparent is element 2, etc.
     * 
     * @return array
     * 
     */
    public static function parents($spec, $include_class = false)
    {
        if (is_object($spec)) {
            $class = get_class($spec);
        } else {
            $class = $spec;
        }
        
        // do we need to load the parent stack?
        if (empty(Solar_Class::$_parents[$class])) {
            // use SPL class_parents(), which uses autoload by default.  use
            // only the array values, not the keys, since that messes up BC.
            $parents = array_values(class_parents($class));
            Solar_Class::$_parents[$class] = array_reverse($parents);
        }
        
        // get the parent stack
        $stack = Solar_Class::$_parents[$class];
        
        // add the class itself?
        if ($include_class) {
            $stack[] = $class;
        }
        
        // done
        return $stack;
    }
    
    /**
     * 
     * Returns the directory for a specific class, plus an optional
     * subdirectory path.
     * 
     * @param string|object $spec The class or object to find parents
     * for.
     * 
     * @param string $sub Append this subdirectory.
     * 
     * @return string The class directory, with optional subdirectory.
     * 
     */
    public static function dir($spec, $sub = null)
    {
        if (is_object($spec)) {
            $class = get_class($spec);
        } else {
            $class = $spec;
        }
        
        // convert the class to a base directory to stem from
        $base = str_replace('_', DIRECTORY_SEPARATOR, $class);
        
        // does the directory exist?
        $dir = Solar_Dir::exists($base);
        if (! $dir) {
            throw Solar::exception(
                'Solar_Class',
                'ERR_NO_DIR_FOR_CLASS',
                'Directory does not exist',
                array('class' => $class, 'base' => $base)
            );
        } else {
            return Solar_Dir::fix($dir . DIRECTORY_SEPARATOR. $sub);
        }
    }
    
    /**
     * 
     * Returns the path to a file under a specific class.
     * 
     * @param string|object $spec The class or object to use as the base path.
     * 
     * @param string $file Append this file path.
     * 
     * @return string The path to the file under the class.
     * 
     */
    public static function file($spec, $file)
    {
        $dir = Solar_Class::dir($spec);
        return Solar_File::exists($dir . $file);
    }
    
    /**
     * 
     * Find the vendor name of a given class or object; this is effectively
     * the part of the class name that comes before the first underscore.
     * 
     * @param mixed $spec An object, or a class name.
     * 
     * @return string The vendor name of the class or object.
     * 
     */
    public static function vendor($spec)
    {
        if (is_object($spec)) {
            $class = get_class($spec);
        } else {
            $class = $spec;
        }
        
        // find the first underscore
        $pos = strpos($class, '_');
        if ($pos !== false) {
            // return the part up to the first underscore
            return substr($class, 0, $pos);
        } else {
            // no underscores, must be an arch-class
            return $class;
        }
    }
    
    /**
     * 
     * Find the vendors of a given class or object and its parents.
     * 
     * @param mixed $spec An object, or a class name.
     * 
     * @return array The vendor names of the class or object hierarchy.
     * 
     */
    public static function vendors($spec)
    {
        // vendor listing
        $stack = array();
        
        // get the list of parents
        $parents = Solar_Class::parents($spec, true);
        
        // look through vendor names
        $old = null;
        foreach ($parents as $class) {
            $new = Solar_Class::vendor($class);
            if ($new != $old) {
                // not the same, add the current vendor name and suffix
                $stack[] = $new;
            }
            // retain old vendor for next loop
            $old = $new;
        }
        
        return $stack;
    }
}
