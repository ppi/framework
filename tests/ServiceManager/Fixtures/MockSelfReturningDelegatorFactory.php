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

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Mock factory that logs delegated service instances and returns itself instead of the original service.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class MockSelfReturningDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * @var mixed[]
     */
    public $instances = array();

    /**
     * {@inheritDoc}
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        $this->instances[] = call_user_func($callback);

        return $this;
    }
}
