<?php

namespace PPI\DataSource\Connection;

use PPI\DataSource\ConnectionInferface;
use Zend\Db\Adapter\Adapter as DbAdapter;

class ZendDb implements ConnectionInferface
{

    protected $config = array();
    protected $conns = array();

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConnectionByName($name)
    {
        if(!isset($this->config[$name])) {
            throw new \Exception('No zend_db db connection found named: ' . $name);
        }

        $config = $this->config[$name];

        if (!isset($this->conns[$name])) {
            $this->conns[$name] = new DbAdapter($config);
        }

        return $this->conns[$name];
    }

    public function supports($library)
    {
        return $library == 'zend_db';
    }

}
