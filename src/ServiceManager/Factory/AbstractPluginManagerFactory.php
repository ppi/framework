<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * AbstractPluginManagerFactory.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
abstract class AbstractPluginManagerFactory implements FactoryInterface
{
    const PLUGIN_MANAGER_CLASS = 'AbstractPluginManager';

    /**
     * Create and return a plugin manager.
     * Classes that extend this should provide a valid class for
     * the PLUGIN_MANGER_CLASS constant.
     *
     * @param  ServiceLocatorInterface                    $serviceLocator
     * @return \Zend\ServiceManager\AbstractPluginManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $pluginManagerClass = static::PLUGIN_MANAGER_CLASS;
        /* @var $plugins \Zend\ServiceManager\AbstractPluginManager */
        $plugins = new $pluginManagerClass();
        $plugins->setServiceLocator($serviceLocator);

        return $plugins;
    }
}
