<?php
/**
 * 
 * Solar command to remove the links to the Vendor source directory.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: UnlinkVendor.php 4701 2010-09-14 01:21:36Z pmjones $
 * 
 * @todo Make Vendor_App_Hello, Vendor_Cli_Help.  Also make Vendor_App_Base
 * and Vendor_Cli_Base?
 * 
 */
class Solar_Cli_UnlinkVendor extends Solar_Controller_Command
{
    /**
     * 
     * The "StudlyCaps" version of the vendor name.
     * 
     * @var string
     * 
     */
    protected $_studly = null;
    
    /**
     * 
     * The "lowercase-dashes" version of the vendor name.
     * 
     * @var string
     * 
     */
    protected $_dashes = null;
    
    /**
     * 
     * The registered Solar_Inflect instance.
     * 
     * @var Solar_Inflect
     * 
     */
    protected $_inflect;
    
    /**
     * 
     * Write out a series of dirs and symlinks for a new Vendor source.
     * 
     * @param string $vendor The Vendor name.
     * 
     * @return void
     * 
     */
    protected function _exec($vendor = null)
    {
        // we need a vendor name, at least
        if (! $vendor) {
            throw $this->_exception('ERR_NO_VENDOR');
        }
        
        $this->_outln("Removing links for vendor '$vendor' ...");
        
        // build "foo-bar" and "FooBar" versions of the vendor name.
        $this->_inflect = Solar_Registry::get('inflect');
        $this->_dashes  = $this->_inflect->camelToDashes($vendor);
        $this->_studly  = $this->_inflect->dashesToStudly($this->_dashes);
        
        // the base system dir
        $system = Solar::$system;
        
        // the links to remove (reverse order from make-vendor)
        $links = array(
            "script/{$this->_dashes}",
            "include/Fixture/{$this->_studly}",
            "include/Mock/{$this->_studly}",
            "include/Test/{$this->_studly}",
            "include/{$this->_studly}",
        );
        
        // remove the links
        foreach ($links as $link) {
            $this->_out("    Removing '$link' ... ");
            $path = "$system/$link";
            
            // fix for windows
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            
            if (Solar_File::exists($path) || Solar_Dir::exists($path)) {
                Solar_Symlink::remove($path);
                $this->_outln("done.");
            } else {
                $this->_outln("missing.");
            }
        }
        
        // done!
        $this->_outln("... done.");
        
        // reminders
        $this->_outln(
                "Remember to remove '{$this->_studly}_App' from the "
              . "['Solar_Controller_Front']['classes'] element "
              . "in your config file."
        );
        
        $this->_outln(
                "Remember to remove '{$this->_studly}_Model' from the "
              . "['Solar_Sql_Model_Catalog']['classes'] element "
              . "in your config file."
        );
        
        // need to write a recursive-remove method for Solar_Dir that will
        // delete only the symlinked files (not the real files)
        $this->_outln(
                "You will need to remove the "
              . "'docroot/public/{$this->_studly}' directory yourself, as it "
              . "may contain copies of public assets (not links)."
        );
    }
}
