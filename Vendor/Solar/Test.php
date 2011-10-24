<?php
/**
 * 
 * A single unit test.
 * 
 * @category Solar
 * 
 * @package Solar_Test Unit-testing tools.
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Test.php 4380 2010-02-14 16:06:52Z pmjones $
 * 
 */
class Solar_Test extends Solar_Base
{
    /**
     * Exit code for premature termination from error or exception.
     */
    const EXIT_TERM = 0;
    
    /**
     * Exit code for a failed test.
     */
    const EXIT_FAIL = 101;
    
    /**
     * Exit code for an incomplete test.
     */
    const EXIT_TODO = 102;
    
    /**
     * Exit code for a skipped test.
     */
    const EXIT_SKIP = 103;
    
    /**
     * Exit code for a successful test.
     */
    const EXIT_PASS = 104;
    
    /**
     * 
     * A running count of how many times an assert*() method is called.
     * 
     * @var int
     * 
     */
    protected $_assert_count = 0;
    
    /**
     * 
     * Variable dumper for debug output.
     * 
     * @var Solar_Debug_Var
     * 
     */
    protected $_debug;
    
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
        $this->_debug = Solar::factory('Solar_Debug_Var');
    }
    
    /**
     * 
     * Teardown after the entire unit test.
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
    }
    
    /**
     * 
     * Runs before each test method; used for preparing state.
     * 
     * @return void
     * 
     */
    public function preTest()
    {
    }
    
    /**
     * 
     * Runs after each test method; used for restoring state.
     * 
     * @return void
     * 
     */
    public function postTest()
    {
    }
    
    /**
     * 
     * Returns the number of assertions made by this test.
     * 
     * @return int
     * 
     */
    public function getAssertCount()
    {
        return $this->_assert_count;
    }
    
    /**
     * 
     * Resets the assertion counter to zero.
     * 
     * @return void
     * 
     */
    public function resetAssertCount()
    {
        $this->_assert_count = 0;
    }
    
    /**
     * 
     * Asserts that a variable is boolean true.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertTrue($actual)
    {
        $this->_assert_count ++;
        
        if ($actual !== true) {
            $this->fail(
                'Expected true, actually not-true',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is not boolean true.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotTrue($actual)
    {
        $this->_assert_count ++;
        
        if ($actual === true) {
            $this->fail(
                'Expected not-true, actually true',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is boolean false.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertFalse($actual)
    {
        $this->_assert_count ++;
        
        if ($actual !== false) {
            $this->fail(
                'Expected false, actually not-false',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is not boolean false.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotFalse($actual)
    {
        $this->_assert_count ++;
        
        if ($actual === false) {
            $this->fail(
                'Expected not-false, actually false',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is PHP null.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNull($actual)
    {
        $this->_assert_count ++;
        
        if ($actual !== null) {
            $this->fail(
                'Expected null, actually not-null',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a variable is not PHP null.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotNull($actual)
    {
        $this->_assert_count ++;
        
        if ($actual === null) {
            $this->fail(
                'Expected not-null, actually null',
                array(
                    'actual' => $this->_export($actual),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a object is an instance of a class.
     * 
     * @param object $actual The object to test.
     * 
     * @param string $expect The expected class name.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertInstance($actual, $expect)
    {
        $this->_assert_count ++;
        
        if (! is_object($actual)) {
            $this->fail(
                'Expected object, actually ' . gettype($actual),
                array(
                    'actual' => $this->_export($actual),
                )
            );
        }
        
        if (! class_exists($expect, false)) {
            $this->fail(
                "Expected class '$expect' not loaded for comparison"
            );
        }
        
        if (!($actual instanceof $expect)) {
            $this->fail(
                "Expected instance of class '$expect', actually '" . get_class($actual) . "'"
            );
        }
        
        return true;
    }
    
    /**
     * 
     * Asserts that a object is not an instance of a class.
     * 
     * @param object $actual The object to test.
     * 
     * @param string $expect The non-expected class name.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotInstance($actual, $expect)
    {
        $this->_assert_count ++;
        
        if (! is_object($actual)) {
            $this->fail(
                "Expected object, actually "  . gettype($actual),
                array(
                    'actual' => $this->_export($actual),
                )
            );
        }
        
        if (! class_exists($expect, false)) {
            $this->fail(
                "Expected class '$expect' not loaded for comparison"
            );
        }
        
        if ($actual instanceof $expect) {
            $this->fail(
                "Expected instance not-of class '$expect', actually is"
            );
        }
        
        return true;
    }
    
    /**
     * 
     * Asserts that two variables have the same type and value.
     * 
     * When used on objects, asserts the two variables are 
     * references to the same object.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The expected value.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertSame($actual, $expect)
    {
        $this->_assert_count ++;
        
        if (is_array($actual)) {
            $this->_ksort($actual);
        }
        
        if (is_array($expect)) {
            $this->_ksort($expect);
        }
        
        if ($actual !== $expect) {
            $this->fail(
                'Expected same, actually not-same',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that two variables are not the same type and value.
     * 
     * When used on objects, asserts the two variables are not
     * references to the same object.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The non-expected result.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotSame($actual, $expect)
    {
        $this->_assert_count ++;
        
        if (is_array($actual)) {
            $this->_ksort($actual);
        }
        
        if (is_array($expect)) {
            $this->_ksort($expect);
        }
        
        if ($actual === $expect) {
            $this->fail(
                'Expected not-same, actually same',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that two variables are equal; type is not strict.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The expected value.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertEquals($actual, $expect)
    {
        $this->_assert_count ++;
        
        if (is_array($actual)) {
            $this->_ksort($actual);
        }
        
        if (is_array($expect)) {
            $this->_ksort($expect);
        }
        
        if ($actual != $expect) {
            $this->fail(
                'Expected equals, actually not-equals',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that two variables are not equal; type is not strict.
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The expected value.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotEquals($actual, $expect)
    {
        $this->_assert_count ++;
        
        if (is_array($actual)) {
            $this->_ksort($actual);
        }
        
        if (is_array($expect)) {
            $this->_ksort($expect);
        }
        
        if ($actual == $expect) {
            $this->fail(
                'Expected not-equals, actually equals',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a value matches a regular expression pattern
     * using [[php::preg_match() | ]].
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The regular expression pattern.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertRegex($actual, $expect)
    {
        $this->_assert_count ++;
        if (! preg_match($expect, $actual)) {
            $this->fail(
                'Expected pattern match, actually not a match',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that a value does not match a regular expression pattern
     * using [[php::preg_match() | ]].
     * 
     * @param mixed $actual The variable to test.
     * 
     * @param mixed $expect The regular expression pattern.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertNotRegex($actual, $expect)
    {
        $this->_assert_count ++;
        if (preg_match($expect, $actual)) {
            $this->fail(
                'Expected no pattern match, actually matches',
                array(
                    'actual' => $this->_export($actual),
                    'expect' => $this->_export($expect),
                )
            );
        } else {
            return true;
        }
    }
    
    /**
     * 
     * Asserts that an object property meets criteria.
     * 
     * The object property may be public, protected, or private.
     * 
     * @param object $object The object to test.
     * 
     * @param string $property The property to inspect.
     * 
     * @param string $test The Solar_Test_Assert method to call.
     * 
     * @param mixed $expect The expected result from the test method.
     * 
     * @return bool The assertion result.
     * 
     */
    public function assertProperty($object, $property, $test, $expect = null)
    {
        $this->_assert_count ++;
        
        if (! is_object($object)) {
            $this->fail("Expected object, actually " . gettype($object));
        }
        
        // introspect the object and look for the property
        $class = get_class($object);
        $found = false;
        $reflect = new ReflectionObject($object);
        foreach ($reflect->getProperties() as $prop) {
        
            // $val is a ReflectionProperty object
            $name = $prop->getName();
            if ($name != $property) {
                // skip it, not the one we're looking for
                continue;
            }
            
            // found the requested property
            $found = true;
            $copy = (array) $object;
            
            // get the actual value.  the null-char
            // trick for accessing protected and private
            // properties comes from Mike Naberezny.
            if ($prop->isPublic()) {
                $actual = $copy[$name];
            } elseif ($prop->isProtected()) {
                $actual = $copy["\0*\0$name"];
            } else {
                $actual = $copy["\0$class\0$name"];
            }
            
            // done
            break;
        }
        
        // did we find $object->$property?
        if (! $found) {
            $this->fail(
                "Did not find expected property '$property' " .
                "in object of class '$class'"
            );
        }
        
        // test the property value
        $method = 'assert' . ucfirst($test);
        return $this->$method($actual, $expect);
    }
    
    /**
     * 
     * Prints diagnostic output.
     * 
     * @param mixed $spec The diagnostic output. If a string, prints line-by-
     * line; otherwise, prints a var_export() of the value line-by-line.
     * 
     * @param string $label The label for the diagnostic output, if any.
     * 
     * @return void
     * 
     */
    public function diag($spec, $label = null)
    {
        // print the label if any
        if ($label) {
            $this->diag($label);
        }
        
        // print the diagnostic output
        if ($spec instanceof Exception) {
            $text = $spec->__toString();
            $this->diag($text);
        } elseif (is_string($spec)) {
            $lines = explode(PHP_EOL, $spec);
            foreach ($lines as $line) {
                echo "# " . $line . PHP_EOL;
            }
        } else {
            $dump = var_export($spec, true);
            $this->diag($dump);
        }
    }
    
    /**
     * 
     * Throws an exception indicating a failed test.
     * 
     * @param string $text The failed-test message.
     * 
     * @param array $info Additional info for the exception.
     * 
     * @return void
     * 
     */
    public function fail($text = null, $info = null)
    {
        $info['exit'] = Solar_Test::EXIT_FAIL;
        throw Solar::factory('Solar_Test_Exception_Fail', array(
            'class' => get_class($this),
            'code'  => 'ERR_FAIL',
            'text'  => ($text ? $text : $this->locale('ERR_FAIL')),
            'info'  => $info,
        ));
    }
    
    /**
     * 
     * Throws an exception indicating an incomplete test.
     * 
     * @param string $text The incomplete-test message.
     * 
     * @param array $info Additional info for the exception.
     * 
     * @return void
     * 
     */
    public function todo($text = null, $info = null)
    {
        $info['exit'] = Solar_Test::EXIT_TODO;
        throw Solar::factory('Solar_Test_Exception_Todo', array(
            'class' => get_class($this),
            'code'  => 'ERR_TODO',
            'text'  => ($text ? $text : $this->locale('ERR_TODO')),
            'info'  => $info,
        ));
    }
    
    /**
     * 
     * Throws an exception indicating a skipped test.
     * 
     * @param string $text The skipped-test message.
     * 
     * @param array $info Additional info for the exception.
     * 
     * @return void
     * 
     */
    public function skip($text = null, $info = null)
    {
        $info['exit'] = Solar_Test::EXIT_SKIP;
        throw Solar::factory('Solar_Test_Exception_Skip', array(
            'class' => get_class($this),
            'code'  => 'ERR_SKIP',
            'text'  => ($text ? $text : $this->locale('ERR_SKIP')),
            'info'  => $info,
        ));
    }
    
    /**
     * 
     * Error handler for this test; throws a test failure.
     * 
     * @param int $code The PHP error level code.
     * 
     * @param string $text The PHP error string.
     * 
     * @param string $file The file where the error occurred.
     * 
     * @param int $line The line number in that file.
     * 
     * @return void
     * 
     */
    public function error($code, $text, $file, $line)
    {
        // if using @ to suppress error reporting, don't fail.
        if (ini_get('error_reporting') == 0) {
            return;
        }
        
        // figure out the error level
        $type = array(
            E_STRICT       => 'PHP Strict',
            E_WARNING      => 'PHP Warning',
            E_NOTICE       => 'PHP Notice',
            E_USER_ERROR   => 'PHP User Error',
            E_USER_WARNING => 'PHP User Warning',
            E_USER_NOTICE  => 'PHP User Notice',
        );
        
        if (empty($type[$code])) {
            $level = 'Unknown PHP Error';
        } else {
            $level = $type[$code];
        }
        
        // throw a failure
        $this->fail("$level: $text", array(
            'file' => $file,
            'line' => $line,
        ));
    }
    
    /**
     * 
     * Returns the output from Solar_Debug_Var::fetch() for a variable.
     * 
     * @param mixed $var The variable dump.
     * 
     * @return string
     * 
     */
    protected function _export($var)
    {
        return trim($this->_debug->fetch($var));
    }
    
    /**
     * 
     * Recursively [[php::ksort() | ]] an array.
     * 
     * Used so that order of array elements does not affect equality.
     * 
     * @param array $array The array to sort.
     * 
     * @return void
     * 
     */
    protected function _ksort(&$array)
    {
        ksort($array);
        foreach($array as $key => $val) {
            if (is_array($val)) {
                $this->_ksort($array[$key]);
            }
        }
    }
}
