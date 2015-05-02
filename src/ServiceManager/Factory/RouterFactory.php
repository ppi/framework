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
use Zend\ServiceManager\FactoryInterface;
use PPI\Framework\Router\Wrapper\SymfonyRouterWrapper;
use Zend\ServiceManager\ServiceLocatorInterface;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;
/**
 * Router Factory.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class RouterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ChainRouter
     * @throws \Exception
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $requestContext  = $serviceLocator->get('RouterRequestContext');
        $routerOptions   = array();

        $logger = $serviceLocator->has('Logger') ? $serviceLocator->get('Logger') : null;

        $chainRouter = new ChainRouter($logger);
        $chainRouter->setContext($requestContext);

        $allModuleRoutes = $serviceLocator->get('ModuleDefaultListener')->getRoutes();
        foreach ($allModuleRoutes as $moduleName => $moduleRoutes) {

            switch(true) {
                case $moduleRoutes instanceof SymfonyRouteCollection:
                    // Create a new router for each module
                    $sfRouter = new Router($requestContext, $moduleRoutes, $routerOptions, $logger);
                    $sfRouterWrapper = new SymfonyRouterWrapper($sfRouter);
                    break;

//                case $moduleRoutes instanceof AuraRouter:
//                    break;

                default:
                    throw new \Exception('Unexpected routes value return from module: ' . $moduleName . ' - found value: ' . gettype($routes));
            }

            $chainRouter->add($sfRouterWrapper);
        }
        return $chainRouter;
    }
}
