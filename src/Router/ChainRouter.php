<?php

/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Router;

use Symfony\Cmf\Component\Routing\ChainRouter as BaseChainRouter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ChainRouter.
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class ChainRouter extends BaseChainRouter
{
    /**
     * @var Request
     */
    protected $matchedRouteRequest;

    private $routingCache;

    /**
     * @param array $parameters
     *
     * @return string
     */
    public function parametersToString(array $parameters)
    {
        $pieces = array();
        foreach ($parameters as $key => $val) {
            $pieces[] = sprintf('"%s": "%s"', $key, (is_string($val) ? $val : json_encode($val)));
        }

        return implode(', ', $pieces);
    }

    /**
     * @return bool
     */
    public function hasRouters()
    {
        $routers = $this->sortRouters();

        return !empty($routers);
    }

    /**
     * @todo - might not need this afterall
     *
     * @return Request
     */
    public function getMatchedRouteRequest()
    {
        return $this->matchedRouteRequest;
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     */
    public function matchRequest(Request $request)
    {
        try {
            $parameters = parent::matchRequest($request);
            $this->matchedRouteRequest = $request;
            return $parameters;
        } catch (\Exception $e) {
            throw $e;
        }


    }

}
