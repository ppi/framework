<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework;

use PPI\Framework\App as BaseApp;
use Symfony\Component\Routing\Matcher\UrlMatcher as UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as Route;
use Symfony\Component\Routing\RouteCollection;
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
        $this->routes[md5($uri . $method)] = array(
            'method'   => $method,
            'uri'      => $uri,
            'callback' => $callback,
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
        foreach ($this->routes as $routeKey => $r) {
            $route = new Route($r['uri']);
            $route->setMethods($r['method']);

            $routeCollection->add($routeKey, $route); // @todo - dont md5() this.
        }

        $request = $this->getRequest();

        $requestContext = $this->getServiceManager()->get('RouterRequestContext');
        $requestContext->fromRequest($request);

        $matcher = new UrlMatcher($routeCollection, $requestContext);
        // @todo - try catch this
        $routeAttributes = $matcher->match($request->getPathInfo());
        $matchedRouteKey = $routeAttributes['_route'];

        $this->routes[$matchedRouteKey]['callback']($this->getServiceManager());

        // @todo - handle when the callback returns a Response object and send that to the client.
        return $this;
    }
}
