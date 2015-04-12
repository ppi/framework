<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\ServiceManager\Fixtures;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract factory that keeps track of the number of times it is instantiated.
 */
class FooCounterAbstractFactory implements AbstractFactoryInterface
{
    /**
     * @var int
     */
    public static $instantiationCount = 0;

    /**
     * Increments instantiation count.
     */
    public function __construct()
    {
        static::$instantiationCount += 1;
    }

    /**
     * {@inheritDoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($name == 'foo') {
            return true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return new Foo();
    }
}
