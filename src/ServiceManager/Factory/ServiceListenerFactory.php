<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use Zend\ModuleManager\Listener\ServiceListener;
use Zend\ModuleManager\Listener\ServiceListenerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ServiceListener Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class ServiceListenerFactory implements FactoryInterface
{
    /**
     * @var string
     */
    const MISSING_KEY_ERROR = 'Invalid service listener options detected, %s array must contain %s key.';

    /**
     * @var string
     */
    const VALUE_TYPE_ERROR = 'Invalid service listener options detected, %s must be a string, %s given.';

    /**
     * Default mvc-related service configuration -- can be overridden by modules.
     *
     * @var array
     */
    protected $defaultServiceConfig = array(
        'invokables'    => array(
            'SharedEventManager' => 'Zend\EventManager\SharedEventManager',
            'ModuleEvent'        => 'Zend\ModuleManager\ModuleEvent',
            'filesystem'         => 'Symfony\Component\Filesystem\Filesystem',
            'RequestStack'       => 'Symfony\Component\HttpFoundation\RequestStack',
        ),
        'factories'     => array(
            'Config'                => 'PPI\Framework\ServiceManager\Factory\ConfigFactory',
            'ControllerNameParser'  => 'PPI\Framework\ServiceManager\Factory\ControllerNameParserFactory',
            'ControllerResolver'    => 'PPI\Framework\ServiceManager\Factory\ControllerResolverFactory',
            'DataSource'            => 'PPI\Framework\ServiceManager\Factory\DataSourceFactory',
            'EventManager'          => 'PPI\Framework\ServiceManager\Factory\EventManagerFactory',
            'FileLocator'           => 'PPI\Framework\ServiceManager\Factory\FileLocatorFactory',
            'MicroRouter'           => 'PPI\Framework\ServiceManager\Factory\MicroRouterFactory',
            'Request'               => 'PPI\Framework\ServiceManager\Factory\RequestFactory',
            'Response'              => 'PPI\Framework\ServiceManager\Factory\ResponseFactory',
            'RoutePluginManager'    => 'PPI\Framework\ServiceManager\Factory\RoutePluginManagerFactory',
            'Router'                => 'PPI\Framework\ServiceManager\Factory\RouterFactory',
            'RouterListener'        => 'PPI\Framework\ServiceManager\Factory\RouterListenerFactory',
            'RouterRequestContext'  => 'PPI\Framework\ServiceManager\Factory\RouterRequestContextFactory',
            'RoutingHelper'         => 'PPI\Framework\ServiceManager\Factory\RoutingHelperFactory',
        ),
        'aliases'       => array(
            'Configuration'                             => 'Config',
            'config.parameter_bag'                      => 'ApplicationParameters',
            'templating.loader'                         => 'templating.loader.filesystem',
        ),
    );

    /**
     * Create the service listener service.
     *
     * Tries to get a service named ServiceListenerInterface from the service
     * locator, otherwise creates a Zend\ModuleManager\Listener\ServiceListener
     * service, passing it the service locator instance and the default service
     * configuration, which can be overridden by modules.
     *
     * It looks for the 'service_listener_options' key in the application
     * config and tries to add service manager as configured. The value of
     * 'service_listener_options' must be a list (array) which contains the
     * following keys:
     *   - service_manager: the name of the service manage to create as string
     *   - config_key: the name of the configuration key to search for as string
     *   - interface: the name of the interface that modules can implement as string
     *   - method: the name of the method that modules have to implement as string
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @throws \InvalidArgumentException For invalid configurations.
     * @throws \RuntimeException
     *
     * @return ServiceListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $configuration = $serviceLocator->get('ApplicationConfig');

        if ($serviceLocator->has('ServiceListenerInterface')) {
            $serviceListener = $serviceLocator->get('ServiceListenerInterface');

            if (!$serviceListener instanceof ServiceListenerInterface) {
                throw new \RuntimeException(
                    'The service named ServiceListenerInterface must implement ' .
                    'Zend\ModuleManager\Listener\ServiceListenerInterface'
                );
            }

            $serviceListener->setDefaultServiceConfig($this->defaultServiceConfig);
        } else {
            $serviceListener = new ServiceListener($serviceLocator, $this->defaultServiceConfig);
        }

        if (isset($configuration['service_listener_options'])) {
            if (!is_array($configuration['service_listener_options'])) {
                throw new \InvalidArgumentException(sprintf(
                    'The value of service_listener_options must be an array, %s given.',
                    gettype($configuration['service_listener_options'])
                ));
            }

            foreach ($configuration['service_listener_options'] as $key => $newServiceManager) {
                if (!isset($newServiceManager['service_manager'])) {
                    throw new \InvalidArgumentException(sprintf(self::MISSING_KEY_ERROR, $key, 'service_manager'));
                } elseif (!is_string($newServiceManager['service_manager'])) {
                    throw new \InvalidArgumentException(sprintf(
                        self::VALUE_TYPE_ERROR,
                        'service_manager',
                        gettype($newServiceManager['service_manager'])
                    ));
                }
                if (!isset($newServiceManager['config_key'])) {
                    throw new \InvalidArgumentException(sprintf(self::MISSING_KEY_ERROR, $key, 'config_key'));
                } elseif (!is_string($newServiceManager['config_key'])) {
                    throw new \InvalidArgumentException(sprintf(
                        self::VALUE_TYPE_ERROR,
                        'config_key',
                        gettype($newServiceManager['config_key'])
                    ));
                }
                if (!isset($newServiceManager['interface'])) {
                    throw new \InvalidArgumentException(sprintf(self::MISSING_KEY_ERROR, $key, 'interface'));
                } elseif (!is_string($newServiceManager['interface'])) {
                    throw new \InvalidArgumentException(sprintf(
                        self::VALUE_TYPE_ERROR,
                        'interface',
                        gettype($newServiceManager['interface'])
                    ));
                }
                if (!isset($newServiceManager['method'])) {
                    throw new \InvalidArgumentException(sprintf(self::MISSING_KEY_ERROR, $key, 'method'));
                } elseif (!is_string($newServiceManager['method'])) {
                    throw new \InvalidArgumentException(sprintf(
                        self::VALUE_TYPE_ERROR,
                        'method',
                        gettype($newServiceManager['method'])
                    ));
                }

                $serviceListener->addServiceManager(
                    $newServiceManager['service_manager'],
                    $newServiceManager['config_key'],
                    $newServiceManager['interface'],
                    $newServiceManager['method']
                );
            }
        }

        return $serviceListener;
    }
}
