<?php
/**
 * This file is part of the PPI Framework.
 *
 * @category    PPI
 * @package     ServiceManager
 * @copyright   Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        http://www.ppi.io
 */

namespace PPI\ServiceManager;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * A simple implementation of ServiceLocatorAwareInterface.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
abstract class ServiceLocatorAware implements ServiceLocatorAwareInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator A \Zend\ServiceManager\ServiceLocatorInterface instance
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator);
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
