<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */
namespace PPI\Module\Routing;

use Symfony\Component\Routing\Router as BaseRouter,
    Symfony\Component\Routing\RequestContext;

/**
 * The PPI router
 *
 * @author Paul Dragoonis (dragoonis@php.net)
 * @package    PPI
 * @subpackage Module
 */
class Router extends BaseRouter
{
    /**
     * Constructor.
     *
     * @param RequestContext $requestContext
     * @param type           $collection
     * @param array          $options
     *
     * @return void
     */
    public function __construct(RequestContext $requestContext, $collection, array $options = array())
    {
        parent::setOptions($options);

        $this->collection = $collection;
        $this->context = $requestContext;

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
            $this->options['cache_dir'].
            DIRECTORY_SEPARATOR.
            $this->options['matcher_cache_class'].
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
            $this->options['cache_dir'].
            DIRECTORY_SEPARATOR.
            $this->options['generator_cache_class'].
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
