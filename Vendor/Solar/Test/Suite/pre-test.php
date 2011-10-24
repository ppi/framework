<?php
// the test class to work with
$class = $_SERVER['argv'][1];
try {
    // can we instantiate it?
    $test = new $class();
} catch (Exception $e) {
    // failure at construction
    echo $e;
    if ($e instanceof Solar_Test_Exception) {
        // this was an "exit" exception
        $info = $e->getInfo();
        exit((int) $info['exit']);
    } else {
        // this was an unknown kind of exception
        exit(Solar_Test::EXIT_TERM);
    }
}
// construction succeeded
exit(Solar_Test::EXIT_PASS);