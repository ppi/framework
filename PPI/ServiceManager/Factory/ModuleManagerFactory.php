<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use Zend\ModuleManager\Listener\DefaultListenerAggregate;
use Zend\ModuleManager\Listener\ListenerOptions;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ModuleManager Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ModuleManagerFactory implements FactoryInterface
{
    /**
     * Creates and returns the module manager
     *
     * Instantiates the default module listeners, providing them configuration
     * from the "module_listener_options" key of the ApplicationConfig
     * service. Also sets the default config glob path.
     *
     * Module manager is instantiated and provided with an EventManager, to which
     * the default listener aggregate is attached. The ModuleEvent is also created
     * and attached to the module manager.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ModuleManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (!$serviceLocator->has('ServiceListener')) {
            $serviceLocator->setFactory('ServiceListener', 'Zend\Mvc\Service\ServiceListenerFactory');
        }

        $configuration    = $serviceLocator->get('ApplicationConfig');
        $listenerOptions  = new ListenerOptions($configuration['module_listener_options']);
        $defaultListeners = new DefaultListenerAggregate($listenerOptions);
        $serviceListener  = $serviceLocator->get('ServiceListener');

        $serviceListener->addServiceManager(
            $serviceLocator,
            'service_manager',
            'Zend\ModuleManager\Feature\ServiceProviderInterface',
            'getServiceConfig'
        );
        $serviceListener->addServiceManager(
            'ControllerLoader',
            'controllers',
            'Zend\ModuleManager\Feature\ControllerProviderInterface',
            'getControllerConfig'
        );
        $serviceListener->addServiceManager(
            'ControllerPluginManager',
            'controller_plugins',
            'Zend\ModuleManager\Feature\ControllerPluginProviderInterface',
            'getControllerPluginConfig'
        );
        $serviceListener->addServiceManager(
            'ViewHelperManager',
            'view_helpers',
            'Zend\ModuleManager\Feature\ViewHelperProviderInterface',
            'getViewHelperConfig'
        );

        $events = $serviceLocator->get('EventManager');
        $events->attach($defaultListeners);
        $events->attach($serviceListener);

        $moduleEvent = new ModuleEvent;
        $moduleEvent->setParam('ServiceManager', $serviceLocator);

        $moduleManager = new ModuleManager($configuration['modules'], $events);
        $moduleManager->setEvent($moduleEvent);

        return $moduleManager;
    }
}
