<?php

namespace PPI\DataSource\Connection;

use PPI\DataSource\ConnectionInferface;
use FuelPHP\Database\DB;

class FuelPHP implements ConnectionInferface
{
    protected $config;
    protected $conns = array();

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConnectionByName($name)
    {

        if(!isset($this->config[$name])) {
            throw new \Exception('No fuel db connection found named: ' . $name);
        }

        if (!isset($this->conns[$name])) {
            $this->conns[$name] = DB::connection($this->config[$name]);
        }

        return $this->conns[$name];
    }

    public function supports($library)
    {
        return $library === 'fuelphp';
    }
}