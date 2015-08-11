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

use PPI\Framework\Router\ChainRouter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use PPI\Framework\Router\Router as SymfonyRouter;
use PPI\Framework\Router\Wrapper\SymfonyRouterWrapper;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

use Illuminate\Routing\Router as LaravelRouter;
use PPI\Framework\Router\Wrapper\LaravelRouterWrapper;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;

use Aura\Router\Router as AuraRouter;
use PPI\Framework\Router\Wrapper\AuraRouterWrapper;

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
        $request = $serviceLocator->get('Request');
        $requestContext  = $serviceLocator->get('RouterRequestContext');
        $routerOptions   = array();

        $logger = $serviceLocator->has('Logger') ? $serviceLocator->get('Logger') : null;

        $chainRouter = new ChainRouter($logger);
        $chainRouter->setContext($requestContext);

        $allModuleRoutes = $serviceLocator->get('ModuleDefaultListener')->getRoutes();

        // For each module, add a matching instance type to the chain router
        foreach ($allModuleRoutes as $moduleName => $moduleRoutingResponse) {

            switch(true) {
                case $moduleRoutingResponse instanceof SymfonyRouteCollection:
                    $sfRouter = new SymfonyRouter($requestContext, $moduleRoutingResponse, $routerOptions, $logger);
                    $sfRouterWrapper = new SymfonyRouterWrapper($sfRouter);
                    $chainRouter->add($sfRouterWrapper);
                    break;

                case $moduleRoutingResponse instanceof AuraRouter:
                    $auraRouterWrapper = new AuraRouterWrapper($moduleRoutingResponse);
                    $chainRouter->add($auraRouterWrapper);
                    break;

                case $moduleRoutingResponse instanceof LaravelRouter:
                    $laravelUrlGenerator = new LaravelUrlGenerator($moduleRoutingResponse->getRoutes(), $request);
                    $laravelRouterWrapper = new LaravelRouterWrapper(
                        $moduleRoutingResponse, $request, $laravelUrlGenerator
                    );
                    $chainRouter->add($laravelRouterWrapper);
                    break;

                default:
                    throw new \Exception('Unexpected routes value return from module: ' . $moduleName .
                        '. found value: ' . gettype($routes));
            }
        }

        return $chainRouter;
    }
}
