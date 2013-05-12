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
use PPI\DataSource\Connection\FuelPHP as FuelPHPConnection;
use PPI\DataSource\Connection\Laravel as LaravelConnection;
use PPI\DataSource\Connection\Monga as MongaConnection;

/**
 * DataSource Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class DataSourceFactory implements FactoryInterface
{
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

        if(isset($config['datasource']['connections'])) {
            foreach($config['datasource']['connections'] as $name => $conn) {

                $allConnections[$name] = $conn;

                switch($conn['library']) {

                    case 'laravel':
                        $configMap['laravel'][$name] = $conn;
                        break;

                    case 'doctrine_dbal':
                        $configMap['doctrine_dbal'][$name] = $conn;
                        break;

                    case 'fuelphp':
                        $configMap['fuelphp'][$name] = $conn;
                        break;

                    case 'monga':
                        $configMap['monga'][$name] = $conn;
                        break;
                }
            }

            if(isset($configMap['laravel']) && !empty($configMap['laravel'])) {
                $libraryToConnMap['laravel'] = new LaravelConnection($configMap['laravel']);
            }
            if(isset($configMap['doctrine_dbal']) && !empty($configMap['doctrine_dbal'])) {
                $libraryToConnMap['doctrine_dbal'] = new DoctrineDBALConnection($configMap['doctrine_dbal']);
            }
            if(isset($configMap['fuelphp']) && !empty($configMap['fuelphp'])) {
                $libraryToConnMap['fuelphp'] = new FuelPHPConnection($configMap['fuelphp']);
            }
            if(isset($configMap['monga']) && !empty($configMap['monga'])) {
                $libraryToConnMap['monga'] = new MongaConnection($configMap['monga']);
            }
        }

        return new ConnectionManager($allConnections, $libraryToConnMap);
    }
}
