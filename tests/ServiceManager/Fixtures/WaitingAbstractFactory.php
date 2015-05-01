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

use stdClass;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WaitingAbstractFactory implements AbstractFactoryInterface
{
    public $waitingService = null;

    public $canCreateCallCount = 0;

    public $createNullService = false;

    public $throwExceptionWhenCreate = false;

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $this->canCreateCallCount++;

        return $requestedName === $this->waitingService;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($this->throwExceptionWhenCreate) {
            throw new FooException('E');
        }
        if ($this->createNullService) {
            return;
        }

        return new stdClass();
    }
}
