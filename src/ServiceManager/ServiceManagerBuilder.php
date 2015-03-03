<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager;

use PPI\Framework\Log\LoggerProxy;

/**
 * ServiceManager builder.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ServiceManagerBuilder extends ServiceManager
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config = array())
    {
        $this->config = $config;

        $smConfig = isset($this->config['service_manager']) ? $this->config['service_manager'] : array();
        parent::__construct(new Config\ServiceManagerConfig($smConfig));
    }

    /**
     * @param  array $parameters
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

        $loggerProxy = new LoggerProxy();
        if ($this->has('Logger')) {
            $loggerProxy->setLogger($this->get('Logger'));
        }

        $this->setService('Logger', $loggerProxy);

        foreach (array(
            new Config\SessionConfig(),
            new Config\TemplatingConfig(),
        ) as $serviceConfig) {
            $serviceConfig->configureServiceManager($this);
        }

        return $this;
    }
}
