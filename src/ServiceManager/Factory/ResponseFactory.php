<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 *
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use PPI\Framework\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ServiceManager configuration for the HttpResponse component.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 */
class ResponseFactory implements FactoryInterface
{
    /**
     * Create and return a response instance.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return \PPI\Framework\Http\Response\Response
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Response();
    }
}
