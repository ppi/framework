<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use Symfony\Component\HttpFoundation\Request as HttpRequest,
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ServiceManager configuration for the HttpRequest component.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class RequestFactory implements FactoryInterface
{
    /**
     * Create and return a request instance.
     *
     * @param  ServiceLocatorInterface                   $serviceLocator
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new HttpRequest();
    }
}
