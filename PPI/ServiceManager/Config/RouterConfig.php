<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     ServiceManager
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Config;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use PPI\Module\Routing\Router;

/**
 * ServiceManager configuration for the Router component.
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class RouterConfig extends Config
{
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $serviceManager->setFactory('router', function($serviceManager) {

            $routeCollection = new RouteCollection();
            $requestContext  = new RequestContext();

            $requestContext->fromRequest($serviceManager->get('request'));

            $routerOptions = array();
//            if ($serviceManager->getOption('cache_dir') !== null) {
//                $routerOptions['cache_dir'] = $serviceManager->getOption('cache_dir');
//            }

            $router = new Router($requestContext, $routeCollection, $routerOptions);
// If we are in production mode, and have the routing file(s) have been cached, then skip route fetching on modules boot
//            if ($router->isGeneratorCached() && $router->isMatcherCached()) {
//                $this->_options['moduleConfig']['listenerOptions']['routingEnabled'] = false;
//                $routingEnabled = false;
//            }

            // Merging all the other route collections together from the modules
            $allRoutes = $serviceManager->get('module.defaultListener')->getRoutes();
            foreach ($allRoutes as $routes) {
                $routeCollection->addCollection($routes);
            }
            $router->setRouteCollection($routeCollection);

            return $router;

        });

    }
}
