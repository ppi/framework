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

/**
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class LaravelRouterWrapper implements RouterInterface
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
     * @return
     */
    public function getContext()
    {
        return $this->requestContext;
    }

    /**
     * @done - run this to ensure the SF route collection comes back out
     *
     * @return mixed
     */
    public function getRouteCollection()
    {
        return $this->router->getRoutes();
    }

    /**
     * @done - run this to ensure it generates the correct routes
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $refType = $referenceType === self::ABSOLUTE_PATH;
        return $this->urlGenerator->route($name, $parameters, $refType);
    }

    /**
     * {@inheritdoc}
     * @todo - find out how to get the matching route from the router
     * findRoute() is protected and we must find out how to publicly get
     */
    public function match($pathinfo)
    {

    }


}