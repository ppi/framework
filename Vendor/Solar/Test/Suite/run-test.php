<?php
// load this test class
$class = $_SERVER['argv'][1];

// run this test method in the class
$method = $_SERVER['argv'][2];

// test instance
$test = new $class();

// set the error handler for the test
set_error_handler(array($test, 'error'));

// even though the handler deals with errors (and does not
// print them), we still want error display turned on,
// because the error handler **does not** catch fatal
// errors.
ini_set('display_errors', true);

// run the test
try {
    $test->preTest();
    $test->$method();
    // even if we get through the method, it should have made at least one
    // assertion. if not, nothing was actually "tested".
    if (! $test->getAssertCount()) {
        $test->todo('made no assertions');
    }
} catch (Exception $e) {
    // output the exception as diagnostic info
    $test->diag($e);
    // clean up
    $test->postTest();
    // how to exit?
    if ($e instanceof Solar_Test_Exception) {
        // this is an "exit" exception
        $info = $e->getInfo();
        exit((int) $info['exit']);
    } else {
        // unknown exception, call it premature termination
        exit(Solar_Test::EXIT_TERM);
    }
}

// pass
$test->postTest();
exit(Solar_Test::EXIT_PASS);
