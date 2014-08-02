<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use PPI\Router\RouterListener;
use Symfony\Component\Routing\RequestContext;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * RouterListener Factory.
 *
 * @author     Paul Dragoonis <paul@ppi.io>
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class RouterListenerFactory implements FactoryInterface
{
    /**
     * Create and return the router.
     *
     * @param  ServiceLocatorInterface    $serviceLocator
     * @return \PPI\Router\RouterListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $router         = $serviceLocator->get('Router');
        $requestContext = $serviceLocator->get('RouterRequestContext');
        $logger         = $serviceLocator->get('Logger');
        $requestStack   = $serviceLocator->get('RequestStack');

        return new RouterListener($router, $requestContext, $logger, $requestStack);
    }
}
