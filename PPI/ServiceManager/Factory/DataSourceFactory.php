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

use PPI\DataSource\Connection\DoctrineDBAL as DoctrineDBALConnection;
use PPI\DataSource\Connection\Laravel as LaravelConnection;
use PPI\DataSource\ConnectionManager;

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
        
        $config = $serviceLocator->get('ApplicationConfig');
        $allConnections = $libraryToConnMap = $laravelConns = $doctrineDBALConns = array();

        if(isset($config['datasource']['connections'])) {
            foreach($config['datasource']['connections'] as $name => $conn) {
                $allConnections[$name] = $conn;
                if($conn['library'] === 'laravel') {
                    $laravelConns[$name] = $conn;
                }
                if($conn['library'] === 'doctrine_dbal') {
                    $doctrineDBALConns[$name] = $conn;
                }
            }

            if(!empty($laravelConns)) {
                $libraryToConnMap['laravel'] = new LaravelConnection($laravelConns);
            }

            if(!empty($doctrineDBALConns)) {
                $libraryToConnMap['doctrine_dbal'] = new DoctrineDBALConnection($doctrineDBALConns);
            }

        } 

        return new ConnectionManager($allConnections, $libraryToConnMap);

    }
}
