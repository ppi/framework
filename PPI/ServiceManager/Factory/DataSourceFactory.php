<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use PPI\DataSource\DataSource;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DataSource Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io> <vitor@noiselabs.org>
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

        if (isset($config['datasource']) &&
            isset($config['datasource']['connections'])) {
            return new DataSource($config['datasource']['connections']);
        }

        // FIXME: is it OK to create a DataSource service with no connections?
        return new DataSource();
    }
}
