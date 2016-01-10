<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Router\Wrapper;

use Aura\Router\Router as AuraRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * A wrapper around the Aura Router component to make it compliant with the PPI(Symfony-CMF) ChainRouter.
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class AuraRouterWrapper implements RouterInterface, RequestMatcherInterface
{
    /**
     * @var AuraRouter
     */
    protected $router;

    /**
     * @var @todo typehint this
     */
    protected $context;

    /**
     * @param AuraRouter $router
     */
    public function __construct(AuraRouter $router)
    {
        $this->setRouter($router);
    }

    /**
     * @param AuraRouter $router
     */
    public function setRouter(AuraRouter $router)
    {
        $this->router = $router;
    }

    /**
     * @param RequestContext $context
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return \Aura\Router\RouteCollection
     */
    public function getRouteCollection()
    {
        // @todo - need to check the signature of these, with what's expected of Symfony's RouteCollection
        return $this->router->getRoutes();
    }

    /**
     * @param string      $name
     * @param array       $parameters
     * @param bool|string $referenceType
     *
     * @return false|string
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $ret = $this->router->generate($name, $parameters);
        if ($ret === false) {
            throw new RouteNotFoundException('Unable to generate route for: ' . $name);
        }

        return $ret;
    }

    /**
     * @param string $pathinfo
     *
     * @throws \Exception
     *
     * @return array
     */
    public function match($pathinfo)
    {
        return $this->doMatch($pathinfo);
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return array
     */
    public function matchRequest(Request $request)
    {
        return $this->doMatch($request->getPathInfo(), $request);
    }

    /**
     * @param $pathinfo
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function doMatch($pathinfo, Request $request = null)
    {
        $matchedRoute = $this->router->match($pathinfo, $request->server->all());
        if ($matchedRoute === false) {
            throw new ResourceNotFoundException();
        }

        $routeParams = $matchedRoute->params;

        // The 'action' key always exists and defaults to the Route Name, so we check accordingly
        if (!isset($routeParams['controller']) && $routeParams['action'] === $matchedRoute->name) {
            throw new \Exception('Matched the route: ' . $matchedRoute->name . ' but unable to locate
            any controller/action params to dispatch');
        }

        // We need _controller, to that symfony ControllerResolver can pick this up
        if (!isset($routeParams['_controller'])) {
            if (isset($routeParams['controller'])) {
                $routeParams['_controller'] = $routeParams['controller'];
            } elseif (isset($routeParams['action'])) {
                $routeParams['_controller'] = $routeParams['action'];
            } else {
                throw new \Exception('Unable to determine the controller from route: ' . $matchedRoute->name);
            }
        }

        $routeParams['_route'] = $matchedRoute->name;

        // If the controller is an Object, and 'action' is defaulted to the route name - we default to __invoke
        if ($routeParams['action'] === $matchedRoute->name) {
            $routeParams['action'] = '__invoke';
        }

        return $routeParams;
    }
}
