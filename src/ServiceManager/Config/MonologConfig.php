<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2014 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Config;

use Zend\ServiceManager\ServiceManager;

/**
 * ServiceManager configuration for Monolog services.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class MonologConfig extends AbstractConfig
{
    protected $nestedHandlers = array();

    /**
     * Create and return the logger.
     * @see https://github.com/symfony/MonologBundle/blob/master/DependencyInjection/MonologExtension.php
     *
     * {@inheritdoc}
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $configs = $serviceManager->get('Config');
        $configs['parameters'] = array_merge(array(
            "monolog.logger.class"                  => "PPI\Log\Logger",
            "monolog.gelf.publisher.class"          => "Gelf\MessagePublisher",
            "monolog.handler.stream.class"          => "Monolog\Handler\StreamHandler",
            "monolog.handler.group.class"           => "Monolog\Handler\GroupHandler",
            "monolog.handler.buffer.class"          => "Monolog\Handler\BufferHandler",
            "monolog.handler.rotating_file.class"   => "Monolog\Handler\RotatingFileHandler",
            "monolog.handler.syslog.class"          => "Monolog\Handler\SyslogHandler",
            "monolog.handler.null.class"            => "Monolog\Handler\NullHandler",
            "monolog.handler.test.class"            => "Monolog\Handler\TestHandler",
            "monolog.handler.gelf.class"            => "Monolog\Handler\GelfHandler",
            "monolog.handler.firephp.class"         => "Symfony\Bridge\Monolog\Handler\FirePHPHandler",
            "monolog.handler.chromephp.class"       => "Symfony\Bridge\Monolog\Handler\ChromePhpHandler",
            "monolog.handler.debug.class"           => "Symfony\Bridge\Monolog\Handler\DebugHandler",
            "monolog.handler.swift_mailer.class"    => "Monolog\Handler\SwiftMailerHandler",
            "monolog.handler.native_mailer.class"   => "Monolog\Handler\NativeMailerHandler",
            "monolog.handler.socket.class"          => "Monolog\Handler\SocketHandler",
            "monolog.handler.pushover.class"        => "Monolog\Handler\PushoverHandler",
            "monolog.handler.fingers_crossed.class" => "Monolog\Handler\FingersCrossedHandler",
            "monolog.handler.fingers_crossed.error_level_activation_strategy.class"
                                                    => "Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy"
        ), $configs['parameters']);

        $config = $this->processConfiguration($configs, $serviceManager);
        $handlersToChannels = array();

        if (isset($config['handlers'])) {
            $handlers = array();

            foreach ($config['handlers'] as $name => $handler) {
                $handlers[$handler['priority']][] = array(
                    'id'       => $this->buildHandler($serviceManager, $configs['parameters'], $name, $handler),
                    'channels' => isset($handler['channels']) ? $handler['channels'] : null
                );
            }

            $sortedHandlers = array();
            foreach ($handlers as $priorityHandlers) {
                foreach (array_reverse($priorityHandlers) as $handler) {
                    $sortedHandlers[] = $handler;
                }
            }

            foreach ($sortedHandlers as $handler) {
                if (!in_array($handler['id'], $this->nestedHandlers)) {
                    $handlersToChannels[$handler['id']] = $handler['channels'];
                }
            }
        }

        $loggerClass = $configs['parameters']['monolog.logger.class'];
        $serviceManager->setFactory('monolog.logger', function($serviceManager) use ($loggerClass, $handlersToChannels) {
            $logger = new $loggerClass('app');
            foreach ($handlersToChannels as $handler => $channels) {
                   $logger->pushHandler($serviceManager->get($handler));
            }

            return $logger;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationDefaults()
    {
        return array('monolog' => array());
    }

    protected function buildHandler(ServiceManager $serviceManager, array $parameters, $name, array $handler)
    {
        $handlerId = $this->getHandlerId($name);
        $class = $parameters[sprintf('monolog.handler.%s.class', $handler['type'])];
        $handler['level'] = is_int($handler['level']) ? $handler['level'] : constant('Monolog\Logger::'.strtoupper($handler['level']));

        $serviceManager->setFactory($handlerId, function($serviceManager) use ($class, $handler) {
            switch ($handler['type']) {
            case 'stream':
                return new $class($handler['path'], $handler['level'], $handler['bubble']);
            }

            /*
             * TODO:
             * <code>
             if (!empty($handler['formatter'])) {
                $definition->addMethodCall('setFormatter', array(new Reference($handler['formatter'])));
             }
             */
        });

        return $handlerId;
    }

    protected function getHandlerId($name)
    {
        return sprintf('monolog.handler.%s', $name);
    }

    /**
     * {@inheritDoc}
     */
    protected function processConfiguration(array $config, ServiceManager $serviceManager = null)
    {
        $alias = $this->getAlias();
        if (!isset($configs[$alias])) {
            return array();
        }

        $parameterBag = $serviceManager->get('config.parameter_bag');
        $config = $configs[$alias];

        if (isset($config['handlers'])) {
            foreach (array_keys($config['handlers']) as $k) {
                if (!isset($config['handlers'][$k]['priority'])) {
                    $config['handlers'][$k]['priority'] = 0;
                }
                if (!isset($config['handlers'][$k]['bubble'])) {
                    $config['handlers'][$k]['bubble'] = true;
                }
                if (isset($config['handlers'][$k]['path'])) {
                    $config['handlers'][$k]['path'] = $parameterBag->resolveString($config['handlers'][$k]['path']);
                }
            }
        }

        return $config;
    }
}
