<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager;

/**
 * ServiceManager builder.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ServiceManagerBuilder extends ServiceManager
{
    protected $config;

    public function __construct(array $config = array())
    {
        $this->config = $config;

        $smConfig = isset($this->config['service_manager']) ? $this->config['service_manager'] : array();
        parent::__construct(new Config\ServiceManagerConfig($smConfig));
    }

    public function build()
    {
        $this->compile();
        if (!isset($this->config['framework'])) {
            $this->config['framework'] = array();
        }
        $this->setService('ApplicationConfig', $this->config);

        foreach(array(
            new Config\MonologConfig(),
            new Config\SessionConfig(),
            new Config\TemplatingConfig()
        ) as $serviceConfig) {
            $serviceConfig->configureServiceManager($this);
        }

        return $this;
    }

    /**
     * Resolves parameter values using Symfony's ParameterBag.
     */
    public function compile()
    {
        if (isset($this->config['parameters'])) {
            $parameterBag = new ParameterBag($this->config['parameters']);
            $parameterBag->resolve();
            $this->setService('config.parameter_bag', $parameterBag);
            $this->config['parameters'] = $parameterBag->all();
        } else {
            $this->config['parameters'] = array();
            $this->setService('config.parameter_bag', new ParameterBag());
        }
    }
}
