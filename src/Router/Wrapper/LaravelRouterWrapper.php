<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Router\Wrapper;

use Illuminate\Routing\Route;
use Symfony\Component\Routing\RequestContext as SymfonyRequestContext;
use Illuminate\Http\Request as LaravelRequest;
use PPI\Framework\Router\LaravelRouter;
use Illuminate\Routing\UrlGenerator;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\CompiledRoute;

/**
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class LaravelRouterWrapper implements UrlGeneratorInterface, RequestMatcherInterface
{

    /**
     * @var LaravelRouter
     */
    protected $router;

    /**
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @param LaravelRouter $router
     */
    public function __construct(LaravelRouter $router, LaravelRequest $request, UrlGenerator $urlGenerator)
    {
        $this->router = $router;
        $this->request = $request;
        $this->setUrlGenerator($urlGenerator);
    }

    /**
     * @param UrlGenerator $urlGenerator
     */
    public function setUrlGenerator(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param SymfonyRequestContext $context
     */
    public function setContext(SymfonyRequestContext $context)
    {
        $this->requestContext = $context;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return RequestContext
     */
    public function getContext()
    {
        return $this->requestContext;
    }

    /**
     * @done - run this to ensure the SF route collection comes back out
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->router->getRoutes();
    }

    /**
     * @todo - run this to ensure it generates the correct routes
     * @param $name
     * @param array $parameters
     * @param $referenceType
     * @return string
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $refType = $referenceType === self::ABSOLUTE_PATH;
        return $this->urlGenerator->route($name, $parameters, $refType);
    }

    /**
     *
     * @todo - find out how to get the matching route from the router
     * findRoute() is protected and we must find out how to publicly get
     *
     * @param string $pathinfo
     */
    public function matchRequest(SymfonyRequest $request)
    {
        /**
         * @var Route
         */
        $route = $this->router->matchRequest($request);
        return $this->parseParameters($route);
    }

    /**
     * @param string $moduleName
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    public function parseParameters(Route $route)
    {
        $parameters = $route->parameters();
        $parameters['_route'] = $this->request->getPathInfo();

        $action = $route->getAction();

        if (is_array($action) && isset($action['uses'])) {
            if (is_callable($action['uses'])) {
                $parameters['_controller'] = $action['uses'];
                $parameters['action'] = $action['uses'];
            }
            return $parameters;
        } else if (is_string($action) && strpos($action, '@')) {
            list($parameters['_controller'], $parameters['action']) = explode('@', $action);
            return $parameters;
        }


        throw new \RuntimeException('Unable to parse laravel route parameters');
    }

}