<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * A simple implementation of ServiceLocatorAwareInterface.
 *
 * @author     Vítor Brandão <vitor@ppi.io>
 * @package    PPI
 * @subpackage ServiceManager
 */
abstract class ServiceLocatorAware implements ServiceLocatorAwareInterface
{
    /**
     * @todo Add inline documentation.
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Set serviceManager instance.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return void
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Retrieve serviceManager instance
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
