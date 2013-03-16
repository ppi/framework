<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Config;

use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ServiceManagerConfig implements ConfigInterface
{
    /**
     * Services that can be instantiated without factories
     *
     * @var array
     */
    protected $invokables = array(
        'SharedEventManager' => 'Zend\EventManager\SharedEventManager',
        'ModuleEvent'        => 'Zend\ModuleManager\ModuleEvent',
    );

    /**
     * Service factories
     *
     * @var array
     */
    protected $factories = array(
        'Config'                => 'PPI\ServiceManager\Factory\ConfigFactory',
        'DataSource'            => 'PPI\ServiceManager\Factory\DataSourceFactory',
        'EventManager'          => 'PPI\ServiceManager\Factory\EventManagerFactory',
        'ModuleDefaultListener' => 'PPI\ServiceManager\Factory\ModuleDefaultListenerFactory',
        'ModuleManager'         => 'PPI\ServiceManager\Factory\ModuleManagerFactory',
        'Request'               => 'PPI\ServiceManager\Factory\RequestFactory',
        'Response'              => 'PPI\ServiceManager\Factory\ResponseFactory',
        'Router'                => 'PPI\ServiceManager\Factory\RouterFactory',
        'RoutingHelper'         => 'PPI\ServiceManager\Factory\RoutingHelperFactory'
    );

    /**
     * Abstract factories
     *
     * @var array
     */
    protected $abstractFactories = array();

    /**
     * Aliases
     *
     * @var array
     */
    protected $aliases = array(
        // Zend alias
        'Configuration'                             => 'Config',
        'Zend\EventManager\EventManagerInterface'   => 'EventManager',
        // PPI alias
        'logger'                                    => 'monolog.logger',
        'module.defaultListener'                    => 'ModuleDefaultListener',
        'routing.helper'                            => 'RoutingHelper'
    );

    /**
     * Shared services
     *
     * Services are shared by default; this is primarily to indicate services
     * that should NOT be shared
     *
     * @var array
     */
    protected $shared = array(
        'EventManager' => false,
    );

    /**
     * Constructor
     *
     * Merges internal arrays with those passed via configuration
     *
     * @param array $configuration
     */
    public function __construct(array $configuration = array())
    {
        if (isset($configuration['invokables'])) {
            $this->invokables = array_merge($this->invokables, $configuration['invokables']);
        }

        if (isset($configuration['factories'])) {
            $this->factories = array_merge($this->factories, $configuration['factories']);
        }

        if (isset($configuration['abstract_factories'])) {
            $this->abstractFactories = array_merge($this->abstractFactories, $configuration['abstract_factories']);
        }

        if (isset($configuration['aliases'])) {
            $this->aliases = array_merge($this->aliases, $configuration['aliases']);
        }

        if (isset($configuration['shared'])) {
            $this->shared = array_merge($this->shared, $configuration['shared']);
        }
    }

    /**
     * Configure the provided service manager instance with the configuration
     * in this class.
     *
     * In addition to using each of the internal properties to configure the
     * service manager, also adds an initializer to inject ServiceManagerAware
     * and ServiceLocatorAware classes with the service manager.
     *
     * @param  ServiceManager $serviceManager
     * @return void
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        foreach ($this->invokables as $name => $class) {
            $serviceManager->setInvokableClass($name, $class);
        }

        foreach ($this->factories as $name => $factoryClass) {
            $serviceManager->setFactory($name, $factoryClass);
        }

        foreach ($this->abstractFactories as $factoryClass) {
            $serviceManager->addAbstractFactory($factoryClass);
        }

        foreach ($this->aliases as $name => $service) {
            $serviceManager->setAlias($name, $service);
        }

        foreach ($this->shared as $name => $value) {
            $serviceManager->setShared($name, $value);
        }

        $serviceManager->addInitializer(function ($instance) use ($serviceManager) {
            if ($instance instanceof ServiceManagerAwareInterface) {
                $instance->setServiceManager($serviceManager);
            }
        });

        $serviceManager->addInitializer(function ($instance) use ($serviceManager) {
            if ($instance instanceof ServiceLocatorAwareInterface) {
                $instance->setServiceLocator($serviceManager);
            }
        });

        $serviceManager->setService('ServiceManager', $serviceManager);
        $serviceManager->setAlias('Zend\ServiceManager\ServiceLocatorInterface', 'ServiceManager');
        $serviceManager->setAlias('PPI\ServiceManager\ServiceManager', 'ServiceManager');
    }
}
