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
use Pixie\Connection as PixieConnection;

/**
 * Pixie Db Connection.
 *
 * @author     Muhammad Usman <hi@usman.it>
 * @author     Paul Dragoonis <paul@ppi.io>
 * @package    PPI
 * @subpackage DataSource
 */
class Pixie implements ConnectionInferface
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
            throw new \Exception('No pixie db connection found named: ' . $name);
        }

        $config = $this->config[$name];

        if (!isset($this->conns[$name])) {
            $driver = array_key_exists('driver', $config) ? $config['driver'] : 'mysql';
            $this->conns[$name] = new PixieConnection($driver, $config);
        }

        return $this->conns[$name];
    }

    public function supports($library)
    {
        return $library == 'pixie';
    }

}
