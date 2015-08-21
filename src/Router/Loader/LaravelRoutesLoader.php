<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Router\Loader;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Routing\Router as LaravelRouter;

/**
 * LaravelRoutesLoader class.
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class LaravelRoutesLoader
{

    /**
     * @var LaravelRouter
     */
    protected $router;

    public function __construct(LaravelRouter $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $path
     * @return LaravelRouter
     * @throws \Exception
     */
    public function load($path)
    {

        if(!is_readable($path)) {
            throw new \InvalidArgumentException('Invalid laravel routes path found: ' . $path);
        }

        // localising the object so the $path file can reference $router;
        $router = $this->router;

        // The included file must return the laravel router
        include $path;

        if(!($router instanceof LaravelRouter)) {
            throw new \Exception('Invalid return value from '
                . pathinfo($path, PATHINFO_FILENAME)
                . ' expected instance of LaravelRouter'
            );
        }

        return $router;
    }

}
