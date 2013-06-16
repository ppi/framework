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
use Doctrine\DBAL\DriverManager;

class DoctrineDBAL implements ConnectionInferface
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
            throw new \Exception('No doctrine dbal connection found named: ' . $name);
        }

        if (!isset($this->conns[$name])) {
            $config = $this->normaliseConfigKeys($this->config[$name]);
            $this->conns[$name] = DriverManager::getConnection($config);
        }

        return $this->conns[$name];
    }

    public function supports($library)
    {
        return $library === 'doctrine_dbal';
    }

    public function normaliseConfigKeys($config)
    {
        $keys = array('database' => 'dbname', 'hostname' => 'host', 'username' => 'user');
        foreach ($keys as $findKey => $replaceKey) {
            if (isset($config[$findKey])) {
                $config[$replaceKey] = $config[$findKey];
                unset($config[$findKey]);
            }
        }

        return $config;
    }

}
