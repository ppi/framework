<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use PPI\Framework\Module\Controller\ControllerResolver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ControllerResolverFactory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ControllerResolverFactory implements FactoryInterface
{
    /**
     * Create and return a ControllerResolver instance.
     *
     * @param  ServiceLocatorInterface                   $serviceLocator
     * @return \PPI\Framework\Module\Controller\ControllerResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $parser = $serviceLocator->get('ControllerNameParser');
        $logger = $serviceLocator->has('Logger') ? $serviceLocator->get('Logger') : null;

        return new ControllerResolver($serviceLocator, $parser, $logger);
    }
}
