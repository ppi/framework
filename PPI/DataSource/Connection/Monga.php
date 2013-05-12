<?php

namespace PPI\DataSource\Connection;

use PPI\DataSource\ConnectionInferface;

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

        if(!isset($this->config[$name])) {
            throw new \Exception('No monga db connection found named: ' . $name);
        }

        // @todo - todo
        if (!isset($this->conns[$name])) {
            var_dump($this->config); exit;
            $this->conns[$name] = new Monga\Connection();
        }

        return $this->conns[$name];
    }

    public function supports($library)
    {
        return $library === 'monga';
    }
}