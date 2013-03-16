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
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('ApplicationConfig');

        if (!isset($config['module_listener_options']) ||
            !isset($config['module_listener_options']['module_paths'])) {
            $paths = array();
            $cwd = getcwd() . '/';
            foreach (array('modules', 'vendor') as $dir) {
                if (is_dir($dir = $cwd . $dir)) {
                    $paths[] = $dir;
                }
            }

            $config['module_listener_options']['module_paths'] = $paths;
        }

        if (isset($config['module_listener_options']['extra_module_paths'])) {
            $config['module_listener_options']['module_paths'] = array_merge(
                $config['module_listener_options']['module_paths'],
                $config['module_listener_options']['extra_module_paths']
            );
        }

        $listenerOptions  = new ListenerOptions($config['module_listener_options']);

        return new DefaultListenerAggregate($listenerOptions);

    }
}
