<?php
/**
 * 
 * Utility class to create symlinks.
 * 
 * Supports symlinks on Unix-like systems (BSD, Linux, Mac OS X) and Windows
 * systems that have the `mklink` command (NT6 and later).  For more on
 * the `mklink` command, see <http://www.maxi-pedia.com/mklink>.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Symlink.php 4703 2010-09-14 17:13:31Z pmjones $
 * 
 */
class Solar_Symlink
{
    /**
     * 
     * Makes a symbolic link to a file or directory.
     * 
     * @param string $src The source path of the real file or directory.
     * 
     * @param string $tgt The target path for where to put the symlink.
     * 
     * @param string $dir Change to this directory before creating the
     * symlink, typically the target directory; this helps when making
     * relative symlinks.
     * 
     * @return string The last line from the [[php::exec() | ]] call to
     * create the symlink.
     * 
     */
    public static function make($src, $tgt, $dir = null)
    {
        // are we on a windows system prior to NT6?
        $is_win = strtolower(substr(PHP_OS, 0, 3)) == 'win';
        if ($is_win && php_uname('r') < 6) {
            throw Solar_Symlink::_exception('ERR_WINDOWS_VERSION');
        }
        
        // massage the change-dir a bit
        $dir = trim($dir);
        if ($dir) {
            $dir = Solar_Dir::fix($dir);
        }
        
        // is the source a directory or a file?
        $path    = $dir . $src;
        $is_dir  = Solar_Dir::exists($path);
        $is_file = Solar_File::exists($path);
        if (! $is_dir && ! $is_file) {
            // no source found
            throw Solar_Symlink::_exception('ERR_SOURCE_NOT_FOUND', array(
                'src' => $src,
                'tgt' => $tgt,
                'dir' => $dir,
                'path' => $path,
            ));
        }
        
        // find any existing path to the target
        if ($is_dir) {
            $path = Solar_Dir::exists($dir . $tgt);
        } else {
            $path = Solar_File::exists($dir . $tgt);
        }
        
        // does the target exist already?
        if ($path) {
            throw Solar_Symlink::_exception('ERR_TARGET_EXISTS', array(
                'src' => $src,
                'tgt' => $tgt,
                'dir' => $dir,
                'path' => $path,
            ));
        }
        
        // escape arguments for the command
        $src = escapeshellarg($src);
        $tgt = escapeshellarg($tgt);
        $dir = escapeshellarg($dir);
        
        if ($is_win && $is_dir) {
            // windows directory
            return Solar_Symlink::_makeWinDir($src, $tgt, $dir);
        } elseif ($is_win && $is_file) {
            // windows file
            return Solar_Symlink::_makeWinFile($src, $tgt, $dir);
        } else {
            // non-windows
            return Solar_Symlink::_make($src, $tgt, $dir);
        }
    }
    
    /**
     * 
     * Returns the command to create a symlink on non-Windows systems.
     * 
     * @param string $src The source path of the real file or directory.
     * 
     * @param string $tgt The target path for where to put the symlink.
     * 
     * @param string $dir Change to this directory before creating the
     * symlink, typically the target directory; this helps when making
     * relative symlinks.
     * 
     * @return string The command to make a directory symlink.
     * 
     */
    protected static function _make($src, $tgt, $dir)
    {
        // the command
        $cmd = '';
        
        // change directories?
        if ($dir) {
            $cmd = "cd $dir;";
        }
        
        // make the link. redirect stderr to stdout so we can capture the
        // last line, in case of errors.
        $cmd .= "ln -s $src $tgt 2>&1";
        
        // done!
        return exec($cmd);
    }
    
    /**
     * 
     * Creates a directory symlink on Windows systems.
     * 
     * @param string $src The source path of the real directory.
     * 
     * @param string $tgt The target path for where to put the symlink.
     * 
     * @param string $dir Change to this directory before creating the
     * symlink, typically the target directory; this helps when making
     * relative symlinks.
     * 
     * @return string Empty on success, or the error message on failure.
     * 
     */
    protected static function _makeWinDir($src, $tgt, $dir)
    {
        // the command
        $cmd = '';
        
        // change directories?
        if ($dir) {
            $cmd = "cd $dir & ";
        }
        
        // make the link
        $cmd .= "mklink /D $tgt $src";
        
        // run the command
        $result = exec($cmd);
        
        // windows returns a message on both success and failure.
        // the success message starts with 
        if (substr($result, 0, 21) == 'symbolic link created') {
            return '';
        } else {
            return $result;
        }
    }
    
    /**
     * 
     * Creates a file symlink on Windows systems.
     * 
     * @param string $src The source path of the real file.
     * 
     * @param string $tgt The target path for where to put the symlink.
     * 
     * @param string $dir Change to this directory before creating the
     * symlink, typically the target directory; this helps when making
     * relative symlinks.
     * 
     * @return string Empty on success, or the error message on failure.
     * 
     */
    protected static function _makeWinFile($src, $tgt, $dir)
    {
        // the command
        $cmd = '';
        
        // change directories?
        if ($dir) {
            $cmd = "cd $dir & ";
        }
        
        // make the link
        $cmd .= "mklink $tgt $src";
        
        // run the command
        $result = exec($cmd);
        
        // windows returns a message on both success and failure.
        // the success message starts with 
        if (substr($result, 0, 21) == 'symbolic link created') {
            return '';
        } else {
            return $result;
        }
    }
    
    /**
     * 
     * Removes a file or directory symbolic link.
     * 
     * Actually, this will remove the file or directory even if it's not
     * a symbolic link, so be careful with it.
     * 
     * @param string $path The symbolic link to remove.
     * 
     * @return void
     * 
     */
    public static function remove($path)
    {
        // are we on a windows system prior to NT6?
        $is_win = strtolower(substr(PHP_OS, 0, 3)) == 'win';
        if ($is_win && php_uname('r') < 6) {
            throw Solar_Symlink::_exception('ERR_WINDOWS_VERSION');
        }
        
        // does the path exist for removal?
        $is_dir  = Solar_Dir::exists($path);
        $is_file = Solar_File::exists($path);
        if (! $is_dir && ! $is_file) {
            throw Solar_Symlink::_exception('ERR_PATH_NOT_FOUND', array(
                'path' => $path,
            ));
        }
        
        // how to remove?
        if ($is_win) {
            // have to double up on removals because windows reports all
            // symlinks as files, even if they point to dirs.
            @unlink($path);
            Solar_Dir::rmdir($path);
        } else {
            // unix file or dir
            @unlink($path);
        }
    }
    
    /**
     * 
     * Returns a localized exception object.
     * 
     * @param string $code The error code.
     * 
     * @param array $info Additional error information.
     * 
     * @return Solar_Exception
     * 
     */
    protected static function _exception($code, $info = null)
    {
        $class  = 'Solar_Symlink';
        $locale = Solar_Registry::get('locale');
        return Solar::exception(
            $class,
            $code,
            $locale->fetch($class, $code, 1, $info),
            (array) $info
        );
    }
}