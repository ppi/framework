<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Router\Wrapper;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;
use Aura\Router\Router as AuraRouter;

/**
 *
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class AuraRouterWrapper implements RouterInterface
{

    /**
     * @var AuraRouter
     */
    protected $router;

    /**
     * @param AuraRouter $router
     */
    public function __construct(AuraRouter $router)
    {
        $this->setRouter($router);
    }

    /**
     * @param AuraRouter $router
     */
    public function setRouter(AuraRouter $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        $ret = $this->router->match($pathinfo);
        var_dump(__METHOD__, $ret); exit;
    }

}