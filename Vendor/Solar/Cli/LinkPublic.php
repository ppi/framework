<?php
/**
 * 
 * Solar command to create symlinks to the public assets for a class
 * hierarchy.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: LinkPublic.php 4553 2010-05-05 01:28:46Z pmjones $
 * 
 */
class Solar_Cli_LinkPublic extends Solar_Controller_Command
{
    /**
     * 
     * Creates symlinks to the public assets for a class hierarchy.
     * 
     * @param string $class The base class name under which to start
     * looking for Public directories to make symlinks for.
     * 
     * @return void
     * 
     */
    protected function _exec($class = null)
    {
        // we need a class name, at least
        if (! $class) {
            throw $this->_exception('ERR_NO_CLASS');
        }
        
        $this->_outln("Making public symlinks for '$class' ...");
        
        $system = Solar::$system;
        
        $map = Solar::factory('Solar_Class_Map');
        $map->setBase("$system/include");
        
        $list = array_keys($map->fetch($class));
        foreach ($list as $class) {
            $dir = str_replace('_', '/', $class);
            if (Solar_Dir::exists("$system/include/$dir/Public")) {
                $this->_link($class);
            }
        }
        
        $this->_outln('... done.');
    }
    
    /**
     * 
     * Creates a symlink in "docroot/public" for a given class.
     * 
     * @param string $class The class name.
     * 
     * @return void
     * 
     */
    protected function _link($class)
    {
        // array of class-name parts
        $arr = explode('_', $class);
        
        // the last part of the class name where to put the symlink
        $tgt = array_pop($arr);
        
        // make the rest of the array into a subdirectory path
        $sub = implode('/', $arr);
        
        // where is the source (original) directory located, relatively?
        $k   = count($arr);
        $src = "";
        for ($i = 0; $i < $k; $i++) {
            $src .= "../";
        }
        $src .= "../../include/$sub/$tgt/Public";
        
        // need the system root
        $system = Solar::$system;
        
        // make sure we have a place to make the symlink
        $dir = "docroot/public/$sub";
        if (! Solar_Dir::exists("$system/$dir")) {
            $this->_out("    Making public directory $dir ... ");
            Solar_Dir::mkdir("$system/$dir", 0755, true);
            $this->_outln("done.");
        }
        
        // make the symlink
        $this->_out("    Making public symlink for $class ... ");
        try {
            $err = Solar_Symlink::make($src, $tgt, $dir);
            if ($err) {
                $this->_outln("failed.");
                $this->_errln("    $err");
            } else {
                $this->_outln("done.");
            }
        } catch (Exception $e) {
            $this->_outln('failed.');
            $this->_errln('    ' . $e->getMessage());
        }
    }
}
