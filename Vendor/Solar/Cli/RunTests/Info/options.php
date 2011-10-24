<?php
return array(
    'only' => array(
        'long' => 'only',
        'value' => false,
        'descr' => 'Run only the named test class; do not descend into subclass tests.',
        'filters' => array('validateBool', 'sanitizeBool')
    ),
    'verbose' => array(
        'long' => 'verbose',
        'short' => 'v',
        'value' => false,
        'descr' => 'Show all diagnostic output.',
        'filters' => array('validateBool', 'sanitizeBool')
    ),
    'test_config' => array(
        'long' => 'test-config',
        'param' => 'req',
        'value' => false,
        'descr' => 'Use this config file for the test cases themselves.',
        'filters' => array('validateString', 'sanitizeString')
    ),
    'stop_on_fail' => array(
        'long' => 'stop-on-fail',
        'value' => false,
        'descr' => 'Stop running tests when a test fails.',
        'filters' => array('validateBool', 'sanitizeBool'),
    ),
);
