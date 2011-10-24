<?php
/**
 * 
 * Command to run a Solar test series.
 * 
 * Examples
 * ========
 * 
 * `./script/solar run-tests Test_Vendor_Class`
 * : runs all methods for the test class and its subdirectories
 * 
 * `./script/solar run-tests Test_Vendor_Class --only `
 * : runs all methods for the one test class (no subdirectories)
 * 
 * `./script/solar run-tests Test_Vendor_Class::testMethod`
 * : runs all methods starting with "testMethod" for the test class and its 
 *   subdirectories
 * 
 * `./script/solar run-tests Test_Vendor_Class::testMethod --only`
 * : runs only the one Test_Vendor_Class::testMethod() and no others
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: RunTests.php 4436 2010-02-25 21:38:34Z pmjones $
 * 
 */
class Solar_Cli_RunTests extends Solar_Controller_Command
{
    /**
     * 
     * Runs the tests for a class, descending into subdirectories unless
     * otherwise specified.
     * 
     * @param string $spec The Test_Class or Test_Class::testMethod to run.
     * 
     * @return void
     * 
     */
    protected function _exec($spec = null)
    {
        if (! $spec) {
            throw $this->_exception('ERR_NO_TEST_SPEC');
        }
        
        // look for a :: in the class name; if it's there, split into class
        // and method
        $pos = strpos($spec, '::');
        if ($pos) {
            $class  = substr($spec, 0, $pos);
            $method = substr($spec, $pos+2);
        } else {
            $class = $spec;
            $method = null;
        }
        
        // run just the one test?
        $only = (bool) $this->_options['only'];
        
        // look for a test-config file?
        $test_config = null;
        if ($this->_options['test_config']) {
            // find the real path to the test_config file
            $test_config = realpath($this->_options['test_config']);
            if ($test_config === false) {
                throw $this->_exception('ERR_TEST_CONFIG_REALPATH', array(
                    'file'     => $this->_options['test_config'],
                ));
            }
        }
        
        // set up a test suite object 
        $suite = Solar::factory('Solar_Test_Suite', array(
            'verbose'       => $this->_options['verbose'],
            'test_config'   => $test_config,
            'stop_on_fail'  => $this->_options['stop_on_fail'],
        ));
        
        // run the suite
        $suite->run($class, $method, $only);
    }
}
