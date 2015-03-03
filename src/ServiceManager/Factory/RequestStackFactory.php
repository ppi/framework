<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use Symfony\Component\HttpFoundation\RequestStack;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * RequestStackFactory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class RequestStackFactory implements FactoryInterface
{
    /**
     * Create and return a RequestStack instance.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return \Symfony\Component\HttpFoundation\RequestStack
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RequestStack();
    }
}
