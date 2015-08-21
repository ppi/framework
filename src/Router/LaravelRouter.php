<?php

/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Router;

use Illuminate\Routing\Router as BaseRouter;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\Http\Request as LaravelRequest;

/**
 * Class LaravelRouter
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class LaravelRouter extends BaseRouter
{

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * Given a request object, find the matching route
     *
     * @param Request $request
     * @return \Illuminate\Routing\Route
     */
    public function matchRequest(SymfonyRequest $request)
    {
        $laravelRequest = LaravelRequest::createFromBase($request);
        $route = $this->findRoute($laravelRequest);
        $route->setParameter('_module', $this->moduleName);
        return $route;
    }

    public function generate(SymfonyRequest $path)
    {

        $route = $this->findRoute($request);

//        $parameters = $this->getUrlMatcher($request)->match($path);
//        $route = $this->routes->get($parameters['_route']);
    }

    /**
     * @param string $name
     */
    public function setModuleName($name)
    {
        $this->moduleName = $name;
    }

}