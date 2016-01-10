<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use Aura\Router\Router as AuraRouter;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Routing\UrlGenerator as LaravelUrlGenerator;
use PPI\FastRoute\Wrapper\FastRouteWrapper;
use PPI\Framework\Router\ChainRouter;
use PPI\Framework\Router\Router as SymfonyRouter;
use PPI\Framework\Router\Wrapper\AuraRouterWrapper;
use PPI\Framework\Router\Wrapper\SymfonyRouterWrapper;
use PPI\LaravelRouting\Wrapper\LaravelRouterWrapper;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;
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
     * @todo - move this to a separate method() - consider how to inject custom-defined arbitrary chain router entries
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @throws \Exception
     *
     * @return ChainRouter
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
            switch (true) {
                // @todo - move this to a separate method()
                case $moduleRoutingResponse instanceof SymfonyRouteCollection:
                    $sfRouter = new SymfonyRouter($requestContext, $moduleRoutingResponse, $routerOptions, $logger);
                    $sfRouterWrapper = new SymfonyRouterWrapper($sfRouter);
                    $chainRouter->add($sfRouterWrapper);
                    break;

                // @todo - move this to a separate method()
                case $moduleRoutingResponse instanceof AuraRouter:
                    $auraRouterWrapper = new AuraRouterWrapper($moduleRoutingResponse);
                    $chainRouter->add($auraRouterWrapper);
                    break;

                // @todo - move this to a separate method()
                case $moduleRoutingResponse instanceof LaravelRouter:
                    $laravelRequest = new LaravelRequest();
                    $laravelUrlGenerator = new LaravelUrlGenerator($moduleRoutingResponse->getRoutes(), $laravelRequest);
                    $laravelRouterWrapper = new LaravelRouterWrapper(
                        $moduleRoutingResponse, $laravelRequest, $laravelUrlGenerator
                    );
                    // @todo - solve this problem
//                    $laravelRouterWrapper->setModuleName($this->getName());
                    $chainRouter->add($laravelRouterWrapper);
                    break;

                case $moduleRoutingResponse instanceof FastRouteWrapper:
                    $chainRouter->add($moduleRoutingResponse);
                    break;

                default:
                    throw new \Exception('Unexpected routes value return from module: ' . $moduleName .
                        '. found value of type: ' . gettype($moduleRoutingResponse));
            }
        }

        return $chainRouter;
    }
}
