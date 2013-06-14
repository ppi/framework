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
use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 *
 *
 */
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
