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

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router as BaseRouter;
use Symfony\Component\Routing\RouterInterface;

/**
 * The PPI Router.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class Router extends BaseRouter implements RouterInterface
{
    /**
     * Constructor.
     *
     * @param RequestContext       $requestContext The context
     * @param RouteCollection|null $collection
     * @param array                $options
     * @param LoggerInterface      $logger         A logger instance
     */
    public function __construct(RequestContext $requestContext, RouteCollection $collection = null,
                                array $options = array(), LoggerInterface $logger = null)
    {
        parent::setOptions($options);

        $this->context    = $requestContext;
        $this->collection = $collection;
        $this->logger     = $logger;
    }

    /**
     * Set the route collection.
     *
     * @param RouteCollection|null $collection
     */
    public function setRouteCollection(RouteCollection $collection = null)
    {
        $this->collection = $collection;
    }

    /**
     * Has the cache matcher class been generated.
     *
     * @return bool
     */
    public function isMatcherCached()
    {
        return file_exists(
            $this->options['cache_dir'] .
            DIRECTORY_SEPARATOR .
            $this->options['matcher_cache_class'] .
            '.php'
        );
    }

    /**
     * Has the cache url generator class been generated.
     *
     * @return bool
     */
    public function isGeneratorCached()
    {
        return file_exists(
            $this->options['cache_dir'] .
            DIRECTORY_SEPARATOR .
            $this->options['generator_cache_class'] .
            '.php'
        );
    }

    /**
     * Warm up the matcher and generator parts of the router.
     */
    public function warmUp()
    {
        $this->getMatcher();
        $this->getGenerator();
    }
}
