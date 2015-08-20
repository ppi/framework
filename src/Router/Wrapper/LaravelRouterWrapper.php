<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Router\Wrapper;

use Symfony\Component\Routing\RequestContext as SymfonyRequestContext;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Routing\UrlGenerator;
use Symfony\Component\HttpFoundation\UrlMatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as SymfonyUrlGeneratorInterface;

/**
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class LaravelRouterWrapper implements SymfonyUrlGeneratorInterface, UrlMatcherInterface
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
     * @param RouterInterface $router
     */
    public function __construct(LaravelRouter $router, Request $request, UrlGenerator $urlGenerator)
    {
        $this->setRouter($router);
        $this->setRequest($request);
        $this->setUrlGenerator($urlGenerator);
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
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
    public function match($pathinfo)
    {
        $method = $this->request->getMethod(); // @todo - verify

        $action = '';
        throw new \RuntimeException('Feature incomplete');
        // @todo - what is "action" ?
        $this->router->match($method, $pathinfo, $action);
    }


}