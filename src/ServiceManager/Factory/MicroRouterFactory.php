<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use PPI\Framework\Router\Router;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Router Factory.
 *
 * @author     Paul Dragoonis (paul@ppi.io)
 * @package    PPI
 * @subpackage ServiceManager
 */
class MicroRouterFactory implements FactoryInterface
{
    /**
     * Create and return the router.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return \PPI\Framework\Router\Router
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $routeCollection = new RouteCollection();
        $requestContext  = $serviceLocator->get('RouterRequestContext');
        $routerOptions   = array();

        $logger = $serviceLocator->has('logger') ? $serviceLocator->get('Logger') : null;

        $router = new Router($requestContext, $routeCollection, $routerOptions, $logger);

        // @todo - consider making a base router class, and then have a ModuleRouterFactory to pull module routes
        // @todo - find a way to set routes on this after we instantiate this factory

        // @todo - let you add new routes on demand and call $router->setRouteCollection()
        return $router;
    }
}
