<?php
/**
 * 
 * Utility class for static file methods.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: File.php 3495 2008-10-06 17:08:34Z pmjones $
 * 
 */
class Solar_File
{
    /**
     * 
     * The path of the file currently being used by Solar_File::load().
     * 
     * @var string
     * 
     * @see load()
     * 
     */
    protected static $_file;
    
    /**
     * 
     * Hack for [[php::file_exists() | ]] that checks the include_path.
     * 
     * Use this to see if a file exists anywhere in the include_path.
     * 
     * {{code: php
     *     $file = 'path/to/file.php';
     *     if (Solar_File::exists('path/to/file.php')) {
     *         include $file;
     *     }
     * }}
     * 
     * @param string $file Check for this file in the include_path.
     * 
     * @return mixed If the file exists and is readble in the include_path,
     * returns the path and filename; if not, returns boolean false.
     * 
     */
    public static function exists($file)
    {
        // no file requested?
        $file = trim($file);
        if (! $file) {
            return false;
        }
        
        // using an absolute path for the file?
        // dual check for Unix '/' and Windows '\',
        // or Windows drive letter and a ':'.
        $abs = ($file[0] == '/' || $file[0] == '\\' || $file[1] == ':');
        if ($abs && file_exists($file)) {
            return $file;
        }
        
        // using a relative path on the file
        $path = explode(PATH_SEPARATOR, ini_get('include_path'));
        foreach ($path as $base) {
            // strip Unix '/' and Windows '\'
            $target = rtrim($base, '\\/') . DIRECTORY_SEPARATOR . $file;
            if (file_exists($target)) {
                return $target;
            }
        }
        
        // never found it
        return false;
    }
    
    /**
     * 
     * Returns the OS-specific directory for temporary files, with a file
     * name appended.
     * 
     * @param string $file The file name to append to the temporary directory
     * path.
     * 
     * @return string The temp directory and file name.
     * 
     */
    public static function tmp($file)
    {
        // convert slashes to OS-specific separators,
        // then remove leading and trailing separators
        $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
        $file = trim($file, DIRECTORY_SEPARATOR);
        
        // return the tmp dir plus file name
        return Solar_Dir::tmp() . $file;
    }
    
    /**
     * 
     * Uses [[php::include() | ]] to run a script in a limited scope.
     * 
     * @param string $file The file to include.
     * 
     * @return mixed The return value of the included file.
     * 
     */
    public static function load($file)
    {
        Solar_File::$_file = Solar_File::exists($file);
        if (! Solar_File::$_file) {
            // could not open the file for reading
            $code = 'ERR_FILE_NOT_READABLE';
            $text = Solar_Registry::get('locale')->fetch('Solar', $code);
            throw Solar::exception(
                'Solar',
                $code,
                $text,
                array('file' => $file)
            );
        }
        
        // clean up the local scope, then include the file and
        // return its results.
        unset($file);
        return include Solar_File::$_file;
    }
    
}