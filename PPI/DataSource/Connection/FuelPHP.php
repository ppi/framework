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