<?php
/**
 * @author    Paul Dragoonis <dragoonis@php.net>
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @package   DataSource
 * @link      www.ppi.io
 */
namespace PPI\DataSource;
use Doctrine\MongoDB\Connection;

class Mongo
{
    protected $conn = array();

    public function __construct()
    {
    }

    /**
     * Get the doctrine mongodb driver.
     *
     * @throws DataSourceException
     * @param  array                        $config
     * @return \Doctrine\MongoDB\Connection
     */
    public function getDriver(array $config)
    {
        if (!extension_loaded('mongo')) {
            throw new DataSourceException('Mongo extension is missing');
        }

        $dsn = 'mongodb://';

        if (isset($config['username'], $config['password'])) {
            $dsn .= $config['username'] . ':' . $config['password'] . '@';
        }

        $dsn .= $config['hostname'];

        if (isset($config['port'])) {
            $dsn .= ":{$config['port']}";
        }

        if (!isset($config['options'])) {
            $config['options'] = array();
        }

        $conn = new Connection($dsn, $config['options']);
        if (isset($config['database'])) {
            return $conn->selectDatabase($config['database']);
        }

        return $conn;
    }

}
