<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use PPI\DataSource\ConnectionManager;

use PPI\DataSource\Connection\DoctrineDBAL as DoctrineDBALConnection;
use PPI\DataSource\Connection\DoctrineMongoDB as DoctrineMongoDBConnection;
use PPI\DataSource\Connection\FuelPHP as FuelPHPConnection;
use PPI\DataSource\Connection\Laravel as LaravelConnection;
use PPI\DataSource\Connection\Monga as MongaConnection;
use PPI\DataSource\Connection\ZendDb as ZendDbConnection;

/**
 * DataSource Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @author     Paul Dragoonis <paul@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class DataSourceFactory implements FactoryInterface
{

    protected $connectionClassMap = array(
        'laravel'          => 'PPI\DataSource\Connection\Laravel',
        'doctrine_dbal'    => 'PPI\DataSource\Connection\DoctrineDBAL',
        'doctrine_mongdb'  => 'PPI\DataSource\Connection\DoctrineMongoDB',
        'fuelphp'          => 'PPI\DataSource\Connection\FuelPHP',
        'monga'            => 'PPI\DataSource\Connection\Monga',
        'zend_db'          => 'PPI\DataSource\Connection\ZendDb'
    );

    /**
     * Create and return the datasource service.
     *
     * @param  ServiceLocatorInterface     $serviceLocator
     * @return \PPI\DataSource\DataSource;
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $config         = $serviceLocator->get('ApplicationConfig');
        $allConnections = $libraryToConnMap = $configMap = array();

        // Early return
        if(!isset($config['datasource']['connections'])) {
            return new ConnectionManager($allConnections, $this->connectionClassMap);
        }

        foreach($config['datasource']['connections'] as $name => $config) {

            $allConnections[$name]                = $config;
            $configMap[$config['library']][$name] = $config;
        }

        return new ConnectionManager($allConnections, $this->connectionClassMap);
        
    }
}
