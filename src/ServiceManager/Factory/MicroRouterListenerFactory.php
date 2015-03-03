<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use PPI\Framework\Router\RouterListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * RouterListener Factory.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class MicroRouterListenerFactory implements FactoryInterface
{
    /**
     * Create and return the router.
     *
     * @param  ServiceLocatorInterface    $serviceLocator
     * @return \PPI\Framework\Router\RouterListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $router         = $serviceLocator->get('MicroRouter');
        $requestContext = $serviceLocator->get('RouterRequestContext');
        $logger         = $serviceLocator->get('Logger');

        return new RouterListener($router, $requestContext, $logger);
    }
}
