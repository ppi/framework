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

use PPI\Framework\Router\Router;
use PPI\Framework\Router\ChainRouter;
use Symfony\Component\Routing\RouteCollection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Router Factory.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class RouterFactory implements FactoryInterface
{
    /**
     * Create and return the router.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return \PPI\Framework\Router\Router
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $requestContext  = $serviceLocator->get('RouterRequestContext');
        $routerOptions   = array();

        $logger = $serviceLocator->has('Logger') ? $serviceLocator->get('Logger') : null;

        $chainRouter = new ChainRouter($logger);

        $allModuleRoutes = $serviceLocator->get('ModuleDefaultListener')->getRoutes();
        foreach ($allModuleRoutes as $moduleRoutes) {
            // Create a new router for each module
            $moduleRouter = new Router($requestContext, $moduleRoutes, $routerOptions, $logger);
            $chainRouter->add($moduleRouter);
        }
        return $chainRouter;
    }
}
