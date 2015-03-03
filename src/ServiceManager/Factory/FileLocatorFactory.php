<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use PPI\Framework\Config\AppFileLocator as FileLocator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * FileLocator Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class FileLocatorFactory implements FactoryInterface
{
    /**
     * Create and return the datasource service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return \PPI\Framework\DataSource\DataSource;
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config     = $serviceLocator->get('Config');
        $appRootDir = $config['parameters']['app.root_dir'];

        return new FileLocator($serviceLocator->get('ModuleManager'), $appRootDir);
    }
}
