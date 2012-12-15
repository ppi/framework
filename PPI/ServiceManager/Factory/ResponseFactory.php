<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use Symfony\Component\HttpFoundation\Response as HttpResponse,
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ServiceManager configuration for the HttpResponse component.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ResponseFactory implements FactoryInterface
{
    /**
     * Create and return a response instance.
     *
     * @param  ServiceLocatorInterface                    $serviceLocator
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return HttpResponse::createFromGlobals();
    }
}
