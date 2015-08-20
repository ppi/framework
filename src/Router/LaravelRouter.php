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
use Illuminate\Http\Request;

/**
 * Class LaravelRouter
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class LaravelRouter extends BaseRouter
{

    /**
     * Given a request object, find the matching route
     *
     * @param Request $request
     * @return \Illuminate\Routing\Route
     */
    public function matchFromUrl(Request $request)
    {
        return $this->findRoute($request);
    }

}