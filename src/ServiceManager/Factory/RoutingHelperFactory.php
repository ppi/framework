<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use PPI\Framework\Router\RoutingHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * RoutingHelper Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class RoutingHelperFactory implements FactoryInterface
{
    /**
     * Create and return the routing helper.
     *
     * @param  ServiceLocatorInterface           $serviceLocator
     * @return \PPI\Framework\Module\Routing\RoutingHelper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $params = array();

        return new RoutingHelper($params);
    }
}
