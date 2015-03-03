<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use PPI\Framework\Config\ConfigurationProviderInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * AbstractFactory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
abstract class AbstractFactory implements FactoryInterface, ConfigurationProviderInterface
{
    /**
     * Process an array with the application configuration.
     *
     * @param  array                                        $config
     * @param  \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return array
     */
    abstract protected function processConfiguration(array $config, ServiceLocatorInterface $serviceLocator = null);

    /**
     * Merges configuration.
     */
    protected function mergeConfiguration(array $defaults, array $config)
    {
        return ArrayUtils::merge($defaults, $config);
    }
}
