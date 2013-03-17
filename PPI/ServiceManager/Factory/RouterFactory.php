<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use PPI\Router\Router;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Router Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class RouterFactory implements FactoryInterface
{
    /**
     * Create and return the router.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return \PPI\Router\Router
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $routeCollection = new RouteCollection();
        $requestContext  = $serviceLocator->get('RouterRequestContext');
        $routerOptions = array();
        $logger = $serviceLocator->get('Logger');

        $router = new Router($requestContext, $routeCollection, $routerOptions, $logger);

        $allRoutes = $serviceLocator->get('ModuleDefaultListener')->getRoutes();
        foreach ($allRoutes as $routes) {
            $routeCollection->addCollection($routes);
        }
        $router->setRouteCollection($routeCollection);

        return $router;
    }
}
