<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Config Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class ConfigFactory implements FactoryInterface
{
    /**
     * Create the application configuration service.
     *
     * Retrieves the Module Manager from the service locator, and executes
     * {@link Zend\ModuleManager\ModuleManager::loadModules()}.
     *
     * It then retrieves the config listener from the module manager, and from
     * that the merged configuration.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return array|\Traversable
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mm = $serviceLocator->get('ModuleManager');
        $mm->loadModules();
        $moduleParams = $mm->getEvent()->getParams();

        $config = ArrayUtils::merge(
            $moduleParams['configListener']->getMergedConfig(false),
            $serviceLocator->get('ApplicationConfig')
        );

        $parametersBag = $serviceLocator->get('ApplicationParameters');

        $config['parameters'] = isset($config['parameters']) ?
            ArrayUtils::merge($parametersBag->all(), $config['parameters']) :
            $config['parameters'] = $parametersBag->all();

        return $parametersBag->resolveArray($config);
    }
}
