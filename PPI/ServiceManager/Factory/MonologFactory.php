<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager\Factory;

use PPI\Log\Logger;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Monolog Factory.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
class MonologFactory implements FactoryInterface
{
    /**
     * Create and return the logger.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return \PPI\Log\Logger
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $logger = new Logger('app');

        return $logger;
    }
}
