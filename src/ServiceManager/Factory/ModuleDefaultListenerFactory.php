<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use PPI\Module\Listener\ListenerOptions;
use PPI\Module\Listener\DefaultListenerAggregate;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ModuleDefaultListener Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ModuleDefaultListenerFactory implements FactoryInterface
{
    /**
     * Creates and returns the default module listeners, providing them configuration
     * from the "module_listener_options" key of the ApplicationConfig
     * service. Also sets the default config glob path.
     *
     * @param  ServiceLocatorInterface  $serviceLocator
     * @return DefaultListenerAggregate
     *
     * @note If ListenerOptions becomes a service use "ModuleListenerOptions" or "module.listenerOptions" as the key.
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('ApplicationConfig');
        $config = isset($config['modules']['module_listener_options']) ?
            $config['modules']['module_listener_options'] : array();

        /*
         * "module_listener_options":
         *
         * This should be an array of paths in which modules reside.
         * If a string key is provided, the listener will consider that a module
         * namespace, the value of that key the specific path to that module's
         * Module class.
         */
        if (!isset($config['module_paths'])) {
            $paths = array();
            $cwd = getcwd() . '/';
            foreach (array('modules', 'vendor') as $dir) {
                if (is_dir($dir = $cwd . $dir)) {
                    $paths[] = $dir;
                }
            }

            $config['module_paths'] = $paths;
        }

        // "extra_module_paths" is an invention of PPI (aka doesn't exist in ZF2).
        if (isset($config['extra_module_paths'])) {
            $config['module_paths'] = array_merge($config['module_paths'], $config['extra_module_paths']);
        }

        return new DefaultListenerAggregate(new ListenerOptions($config));
    }
}
