<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use Symfony\Component\Routing\RequestContext;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * RouterRequestContext Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class RouterRequestContextFactory extends AbstractFactory
{
    /**
     * Create and return the router.
     *
     * @param  ServiceLocatorInterface    $serviceLocator
     * @return \PPI\Router\RouterListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $this->processConfiguration($serviceLocator->get('ApplicationConfig'));

        return new RequestContext('', 'GET', $config['host'], $config['scheme'], $config['http_port'], $config['https_port']);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationDefaults()
    {
        return array('framework' => array(
            'router' => array(
                // request_context
                'host'          => 'localhost',
                'scheme'        => 'http',
                // request_listener
                'http_port'     => '80',
                'https_port'    => '443'
            )
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function processConfiguration(array $config, ServiceLocatorInterface $serviceLocator = null)
    {
        $defaults = $this->getConfigurationDefaults();
        $defaults = $defaults['framework']['router'];

        return isset($config['framework']['router']) ?
            $this->mergeConfiguration($defaults, $config['framework']['router']) :
            $defaults;
    }
}
