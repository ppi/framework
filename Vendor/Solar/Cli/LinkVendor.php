<?php
/**
 * 
 * Solar command to create the links to the Vendor source directory.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: LinkVendor.php 4697 2010-09-12 23:41:46Z pmjones $
 * 
 * @todo Make Vendor_App_Hello, Vendor_Cli_Help.  Also make Vendor_App_Base
 * and Vendor_Cli_Base?
 * 
 */
class Solar_Cli_LinkVendor extends Solar_Controller_Command
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
        
        $this->_outln("Making links for vendor '$vendor' ...");
        
        // build "foo-bar" and "FooBar" versions of the vendor name.
        $this->_inflect = Solar_Registry::get('inflect');
        $this->_dashes  = $this->_inflect->camelToDashes($vendor);
        $this->_studly  = $this->_inflect->dashesToStudly($this->_dashes);
        
        $links = array(
            
            // include/Vendor -> ../source/vendor/Vendor
            array(
                'dir' => "include",
                'tgt' => $this->_studly,
                'src' => "../source/{$this->_dashes}/$this->_studly",
            ),
            
            // include/Test/Vendor => ../../source/vendor/tests/Test/Vendor
            array(
                'dir' => "include/Test",
                'tgt' => $this->_studly,
                'src' => "../../source/{$this->_dashes}/tests/Test/$this->_studly",
            ),
            
            // include/Mock/Vendor => ../../source/vendor/tests/Mock/Vendor
            array(
                'dir' => "include/Mock",
                'tgt' => $this->_studly,
                'src' => "../../source/{$this->_dashes}/tests/Mock/$this->_studly",
            ),
            
            // include/Fixture/Vendor => ../../source/vendor/tests/Fixture/Vendor
            array(
                'dir' => "include/Fixture",
                'tgt' => $this->_studly,
                'src' => "../../source/{$this->_dashes}/tests/Fixture/$this->_studly",
            ),
            
            // script/vendor -> ../source/solar/script/solar
            array(
                'dir' => "script",
                'tgt' => $this->_dashes,
                'src' => "../source/solar/script/solar",
            ),
        );
        
        $system = Solar::$system;
        
        foreach ($links as $link) {
            
            // $dir, $src, $tgt
            extract($link);
            
            // fix for windows
            $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
            $src = str_replace('/', DIRECTORY_SEPARATOR, $src);
            $tgt = str_replace('/', DIRECTORY_SEPARATOR, $tgt);
            
            $this->_out("    Making link '$dir/$tgt' ... ");
            try {
                $err = Solar_Symlink::make($src, $tgt, "$system/$dir");
                if ($err) {
                    $this->_outln("failed.");
                    $this->_errln("    $err");
                } else {
                    $this->_outln("done.");
                }
            } catch (Exception $e) {
                $this->_outln("failed.");
                $this->_errln('    ' . $e->getMessage());
            }
        }
        
        // done with this part
        $this->_outln("... done.");
        
        // make public links
        $link_public = Solar::factory('Solar_Cli_LinkPublic');
        $link_public->exec($vendor);
        
        // done for real
        $this->_outln(
                "Remember to add '{$this->_studly}_App' to the "
              . "['Solar_Controller_Front']['classes'] element "
              . "in your config file so that it finds your apps."
        );

        $this->_outln(
                "Remember to add '{$this->_studly}_Model' to the "
              . "['Solar_Sql_Model_Catalog']['classes'] element "
              . "in your config file so that it finds your models."
        );
    }
}
