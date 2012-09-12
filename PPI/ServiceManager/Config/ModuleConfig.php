<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */
namespace PPI\ServiceManager\Config;

use PPI\Module\Listener\ListenerOptions,
    PPI\Module\Listener\DefaultListenerAggregate as PPIDefaultListenerAggregate,
    Zend\ModuleManager\ModuleManager,
    Zend\ServiceManager\Config,
    Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for the Module component.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ModuleConfig extends Config
{
    /**
     * @todo Add inline documentation.
     *
     * @param ServiceManager $serviceManager
     *
     * @return type
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        // listener options
        $serviceManager->setFactory('module.listenerOptions', function($serviceManager) {
            return new ListenerOptions($serviceManager['moduleConfig']['listenerOptions']);
        });

        // default listener
        $serviceManager->setFactory('module.defaultListener', function($serviceManager) {
            $listener = new PPIDefaultListenerAggregate($serviceManager->get('module.listenerOptions'));
            $listener->setServiceManager($serviceManager);

            return $listener;
        });

        // Module Manager
        $serviceManager->setFactory('module.manager', function($serviceManager) {
            $moduleManager = new ModuleManager($serviceManager['moduleConfig']['activeModules']);
            $moduleManager->getEventManager()->attachAggregate($serviceManager->get('module.defaultListener'));
            $moduleManager->loadModules();

            return $moduleManager;
        });
    }

}
