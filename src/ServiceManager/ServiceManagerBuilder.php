<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager;

use Psr\Log\NullLogger;

/**
 * ServiceManager builder.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class ServiceManagerBuilder extends ServiceManager
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->config = $config;

        $smConfig = isset($this->config['service_manager']) ? $this->config['service_manager'] : array();
        parent::__construct(new Config\ServiceManagerConfig($smConfig));
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function build(array $parameters = array())
    {
        if (!isset($this->config['framework'])) {
            $this->config['framework'] = array();
        }

        // Core parameters set by PPI\Framework\App
        $parametersBag = new ParameterBag($parameters);
        $parametersBag->resolve();
        $this->setService('ApplicationParameters', $parametersBag);

        // Settings provided by the application itself on App boot, config provided by modules is not included
        $this->setService('ApplicationConfig', $parametersBag->resolveArray($this->config));

        if (false === $this->has('Logger')) {
            $this->setService('Logger', new NullLogger());
        }

        foreach (array(
            new Config\SessionConfig(),
            new Config\TemplatingConfig(),
        ) as $serviceConfig) {
            $serviceConfig->configureServiceManager($this);
        }

        return $this;
    }
}
