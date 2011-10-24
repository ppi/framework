<?php
return array(
    'source' => array(
        'descr'   => 'The source directory, typically the PEAR directory.',
        'param'   => 'optional',
    ),
    'class_dir' => array(
        'descr'   => 'Write class API docs to this directory.',
        'param'   => 'required', 
    ),
    'package_dir' => array(
        'descr'   => 'Write package docs to this directory.',
        'param'   => 'required', 
    ),
    'docbook_dir' => array(
        'descr'   => 'Convert the docs to DocBook and write them to this directory.',
        'param'   => 'required', 
    ),
    'lint' => array(
        'descr'   => 'Do not make docs, just lint the sources and report errors.',
        'value'   => false,
        'filters' => array('validateBool', 'sanitizeBool'),
    ),
);
