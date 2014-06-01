<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI;

use PPI\App as BaseApp;
use Symfony\Component\Routing\Route as Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher as UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;


/**
 * The PPI MicroApp bootstrap class.
 *
 * This class sets various app settings, and allows you to override classes used in the bootup process.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @package    PPI
 * @subpackage Core
 */
class MicroApp extends BaseApp
{

    protected $router;
    protected $routes = array();
    protected $routeCollection;

    public function get($uri, $callback)
    {
        $this->add($uri, 'get', $callback);
    }

    public function post($uri, $callback)
    {
        $this->add($uri, 'post', $callback);
    }

    public function add($uri, $method, $callback)
    {
        $this->routes[md5($uri.$method)] = array(
            'method'   => $method,
            'uri'      => $uri,
            'callback' => $callback
        );
    }

    public function boot()
    {
        parent::boot();

        return $this;

    }

    public function dispatch()
    {

        // @todo - create the RouteCollection
        // @todo - run the matcher against this and get a route back

        $this->router = $this->serviceManager->get('MicroRouter');

        $routeCollection = new RouteCollection();
        foreach($this->routes as $routeKey => $r) {
            $route = new Route($r['uri']);
            $route->setMethods($r['method']);

            $routeCollection->add($routeKey, $route); // @todo - dont md5() this.
        }

        $request = $this->getRequest();

        $requestContext = $this->getServiceManager()->get('RouterRequestContext');
        $requestContext->fromRequest($request);

//        $router = new Router($requestContext, $routeCollection, array(), $this->getServiceManager()->get('Logger'));

        $matcher = new UrlMatcher($routeCollection, $requestContext);
        // @todo - try catch this
        $routeAttributes = $matcher->match($request->getPathInfo());
        $matchedRouteKey = $routeAttributes['_route'];

        $this->routes[$matchedRouteKey]['callback']($this->getServiceManager());

        // @todo - run matching against the $request and this $collection
die('dispatch');
        return $this;

    }

    public function handleRouting()
    {
        $hasMatch = false;
        try {
            $this->serviceManager->get('MicroRouterListener')->match($this->getRequest());
//            $route = ;
//            $method = ;
            $this->callbacks[$method.$route]($this->getServiceManager);
            $hasMatch = true;
        } catch (\Exception $e) {
            if ($this->debug) {
                $this->log('critical', $e);
                throw ($e);
            }
        }

        if ($hasMatch === false) {
        }


    }

}