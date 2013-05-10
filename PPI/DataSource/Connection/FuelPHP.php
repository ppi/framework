<?php

namespace PPI\DataSource\Connection;

use PPI\DataSource\ConnectionInferface;
use FuelPHP\Database\DB;

class FuelPHP implements ConnectionInferface
{
    protected $config;
    protected $connections = array();

    public function __construct(array $connections)
    {
        $this->config = $connections;
    }

    public function getConnectionByName($name)
    {
        if ( ! isset($this->connections[$name])) {
            $this->connections[$name] = DB::connection($this->config[$name]);
        }

        return $this->connections[$name];
    }

    public function supports($library)
    {
        return $library === 'fuelphp';
    }
}