<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     ServiceManager
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Config;

use PPI\Module\Listener\ListenerOptions;
use PPI\Module\Listener\DefaultListenerAggregate as PPIDefaultListenerAggregate;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for the Module component.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class ModuleConfig extends Config
{
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $options = $serviceManager->get('options');

        // listener options
        $serviceManager->setFactory('module.listenerOptions', function($serviceManager) use ($options) {
            return new ListenerOptions($options['moduleConfig']['listenerOptions']);
        });

        // default listener
        $serviceManager->setFactory('module.defaultListener', function($serviceManager) {
            $listener = new PPIDefaultListenerAggregate($serviceManager->get('module.listenerOptions'));
            $listener->setServiceManager($serviceManager);

            return $listener;
        });

        // Module Manager
        $serviceManager->setFactory('module.manager', function($serviceManager) use ($options) {
            $moduleManager = new ModuleManager($options['moduleConfig']['activeModules']);
            $moduleManager->getEventManager()->attachAggregate($serviceManager->get('module.defaultListener'));
            $moduleManager->loadModules();

            return $moduleManager;
        });
    }
}
