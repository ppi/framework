<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use PPI\Framework\DataSource\ConnectionManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DataSource Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @author     Paul Dragoonis <paul@ppi.io>
 */
class DataSourceFactory implements FactoryInterface
{
    protected $connectionClassMap = array(
        'laravel'          => 'PPI\Framework\DataSource\Connection\Laravel',
        'doctrine_dbal'    => 'PPI\Framework\DataSource\Connection\DoctrineDBAL',
        'doctrine_mongdb'  => 'PPI\Framework\DataSource\Connection\DoctrineMongoDB',
        'fuelphp'          => 'PPI\Framework\DataSource\Connection\FuelPHP',
        'monga'            => 'PPI\Framework\DataSource\Connection\Monga',
        'zend_db'          => 'PPI\Framework\DataSource\Connection\ZendDb',
    );

    /**
     * Create and return the datasource service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return \PPI\Framework\DataSource\DataSource;
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config         = $serviceLocator->get('ApplicationConfig');
        $allConnections = $libraryToConnMap = $configMap = array();

        // Early return
        if (!isset($config['datasource']['connections'])) {
            return new ConnectionManager($allConnections, $this->connectionClassMap);
        }

        foreach ($config['datasource']['connections'] as $name => $config) {
            $allConnections[$name]                = $config;
            $configMap[$config['library']][$name] = $config;
        }

        return new ConnectionManager($allConnections, $this->connectionClassMap);
    }
}
