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
            $this->conns[$name] = DB::connection($this->config[$name]);
        }

        return $this->conns[$name];
    }

    public function supports($library)
    {
        return $library === 'monga';
    }
}