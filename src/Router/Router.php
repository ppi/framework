<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Router;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router as BaseRouter;

/**
 * The PPI Router.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage Router
 */
class Router extends BaseRouter implements RouterInterface
{
    /**
     * Constructor.
     *
     * @param RequestContext  $requestContext The context
     * @param type            $collection
     * @param array           $options
     * @param LoggerInterface $logger         A logger instance
     *
     * @return void
     */
    public function __construct(RequestContext $requestContext, $collection, array $options = array(), LoggerInterface $logger = null)
    {
        parent::setOptions($options);

        $this->collection = $collection;
        $this->context    = $requestContext;
        $this->logger     = $logger;
    }

    /**
     * Set the route collection
     *
     * @param type $collection
     *
     * @return void
     */
    public function setRouteCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Has the cache matcher class been generated
     *
     * @return boolean
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
     * Has the cache url generator class been generated
     *
     * @return boolean
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
     * Warm up the matcher and generator parts of the router
     *
     * @return void
     */
    public function warmUp()
    {
        $this->getMatcher();
        $this->getGenerator();
    }
}
