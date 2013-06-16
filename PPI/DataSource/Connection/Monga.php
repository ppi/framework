<?php

/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\DataSource\Connection;

use PPI\DataSource\ConnectionInferface;

use Monga\Connection as MongaConnection;

class Monga implements ConnectionInferface
{
    protected $config = array();
    protected $conns = array();

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConnectionByName($name)
    {

        if (!isset($this->config[$name])) {
            throw new \Exception('No monga db connection found named: ' . $name);
        }

        $config = $this->config[$name];
        if (!isset($config['hostname'])) {
            throw new \Exception('Unable to locate Monga hostname');
        }

        if (!isset($this->conns[$name])) {
            $this->conns[$name] = new MongaConnection(
                $this->constructDSN($config), $this->normaliseConfigKeys($config)
            );
        }

        return $this->conns[$name];
    }

    public function constructDSN($config)
    {
        if (!isset($config['hostname'])) {
            $config['hostname'] = 'localhost';
        }

        if (!isset($config['port'])) {
            $config['port'] = 27017;
        }

        return sprintf('mongodb://%s:%s', $config['hostname'], $config['port']);
    }

    public function normaliseConfigKeys($config)
    {
        $keys = array('database' => 'db');
        foreach ($keys as $findKey => $replaceKey) {
            if (isset($config[$findKey])) {
                $config[$replaceKey] = $config[$findKey];
                unset($config[$findKey]);
            }
        }
        unset($config['library'], $config['hostname']);

        return $config;
    }

    public function supports($library)
    {
        return $library === 'monga';
    }
}
