<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;


use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Router Factory.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class RouterFactory implements FactoryInterface
{
    /**
     * @todo - move this to a separate method() - consider how to inject custom-defined arbitrary chain router entries
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @throws \Exception
     *
     * @return ChainRouter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $routerOptions   = array();
        $logger = $serviceLocator->has('Logger') ? $serviceLocator->get('Logger') : null;
        $chainRouter = new ChainRouter($logger);

        return $chainRouter;
    }
}
