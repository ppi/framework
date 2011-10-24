<?php
return array(
    'table' => array(
        'long'    => 'table',
        'descr'   => 'The table name for the model to use.',
        'param'   => 'required',
    ),
    'extends' => array(
        'long'    => 'extends',
        'descr'   => 'Extend the model class from this parent class name.',
        'param'   => 'required',
    ),
    'connect' => array(
        'long'    => 'connect',
        'descr'   => 'Connect to the database and fetch cols for the model setup (true/false).',
        'param'   => 'required',
        'value'   => true,
        'filters' => array('validateBool', 'sanitizeBool'),
    ),
    'adapter' => array(
        'long'    => 'adapter',
        'descr'   => 'The SQL adapter class to use.',
        'param'   => 'required',
    ),
    'host' => array(
        'long'    => 'host',
        'descr'   => 'The host for the database connection.',
        'param'   => 'required',
    ),
    'port' => array(
        'long'    => 'port',
        'descr'   => 'The port for the database connection.',
        'param'   => 'required',
    ),
    'user' => array(
        'long'    => 'user',
        'descr'   => 'The username for the database connection.',
        'param'   => 'required',
    ),
    'pass' => array(
        'long'    => 'pass',
        'descr'   => 'The password for the database connection.',
        'param'   => 'required',
    ),
    'name' => array(
        'long'    => 'name',
        'descr'   => 'The name of the database to connect to.',
        'param'   => 'required',
    ),
);
