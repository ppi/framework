<?php
function __autoload($class) {
    $class_file = str_replace('_', DIRECTORY_SEPARATOR, $class) . ".php";
    include_once $class_file;
}

function solar_load_test_files($dir)
{
    $list = glob($dir . DIRECTORY_SEPARATOR . "[A-Z]*.php");
    foreach ($list as $class_file) {
        include_once $class_file;
    }
    
    $list = glob($dir . DIRECTORY_SEPARATOR . "[A-Z]*", GLOB_ONLYDIR);
    foreach ($list as $sub) {
        solar_load_test_files($sub);
    }
}

// report all errors
error_reporting(E_ALL|E_STRICT);

// look in this directory for tests
$dir = rtrim($_SERVER['argv'][1], DIRECTORY_SEPARATOR);

// starting with this class
$class = $_SERVER['argv'][2];
if (! $class) {
    $class = null;
}

// method prefix?
$method = $_SERVER['argv'][3];
if (! $method) {
    $method = null;
}

// "only" the class and/or method?
$only = (bool) $_SERVER['argv'][4];

// find the top-level file for the class
$class_file = $dir
            . DIRECTORY_SEPARATOR
            . str_replace('_', DIRECTORY_SEPARATOR, $class)
            . ".php";
            
if (file_exists($class_file) && is_readable($class_file)) {
    require_once $class_file;
}

// load all test files under the class dir, if it's not the only one to test
if (! $only) {
    $subdir = substr($class_file, 0, -4);
    solar_load_test_files($subdir);
}

// now that all the files are loaded, let's see what classes we found
$test_classes = get_declared_classes();
sort($test_classes);
$data = array('plan' => 0, 'tests' => array());
$count = 0;

foreach ($test_classes as $test_class) {
    // is it a Test_* class?
    if (substr($test_class, 0, 5) != 'Test_') {
        continue;
    }
    
    // ignore abstracts and interfaces
    $reflect = new ReflectionClass($test_class);
    if ($reflect->isAbstract() || $reflect->isInterface()) {
        continue;
    }
    
    // is it an "only" class?
    if ($only && $test_class != $class) {
        continue;
    }
    
    // find all the test*() methods in the Test_* class
    $test_methods = get_class_methods($test_class);
    foreach ($test_methods as $test_method) {
        
        // skip non test*() methods
        if (substr($test_method, 0, 4) != 'test') {
            continue;
        }
        
        // are we looking for only one method to test?
        if ($only && $method) {
            
            // match only the one exact method
            if ($method != $test_method) {
                continue;
            }
            
            // add only this one method to the plan, and break out
            $data['plan'] ++;
            $data['tests'][$test_class][] = $test_method;
            break;
        }
        
        // not looking for only one method
        if ($method) {
            // look for a matching prefix
            $prefix = substr($test_method, 0, strlen($method));
            if ($method == $prefix) {
                // add the test class and method to the plan
                $data['plan'] ++;
                $data['tests'][$test_class][] = $test_method;
            }
        } else {
            // not looking for a prefix, add the method
            $data['plan'] ++;
            $data['tests'][$test_class][] = $test_method;
        }
    }
}

// dump the serialized data
echo serialize($data) . PHP_EOL;

// exit code 104 is "EXIT_PASS"
exit(104);
