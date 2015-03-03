<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\Framework\ServiceManager\Factory;

use PPI\Framework\Module\Controller\ControllerNameParser;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * ControllerNameParserFactory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class ControllerNameParserFactory implements FactoryInterface
{
    /**
     * Create and return a ControllerNameParser instance.
     *
     * @param  ServiceLocatorInterface                     $serviceLocator
     * @return \PPI\Framework\Module\Controller\ControllerNameParser
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $moduleManager = $serviceLocator->get('ModuleManager');

        return new ControllerNameParser($moduleManager);
    }
}
