<?php

namespace PPI\DataSource\Connection;

use PPI\DataSource\ConnectionInferface;
use Doctrine\DBAL\DriverManager;

class DoctrineDBAL implements ConnectionInferface
{

	protected $conns;

    public function __construct(array $connections)
    {
    	foreach($connections as $name => $config) {
    		$conns[$name] = DriverManager::getConnection($this->normaliseConfigKeys($config));
    	}
		$this->conns = $conns;
    }

    public function getConnectionByName($name)
    {

    	if(!isset($this->conns[$name])) {
    		throw new \Exception('No doctrine dbal connection found named: ' . $name);
    	}
    	return $this->conns[$name];
    }

    public function supports($library)
    {
    	return $library === 'doctrine_dbal';
    }

    public function normaliseConfigKeys($config)
    {
        $keys = array('database' => 'dbname', 'hostname' => 'host', 'username' => 'user', 'port');
        foreach($keys as $findKey => $replaceKey) {
            if(isset($config[$findKey])) {
                $config[$replaceKey] = $config[$findKey];
                unset($config[$findKey]);
            }
        }
        return $config;
    }

}