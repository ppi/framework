<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\ServiceManager\TestAsset;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FooFactory.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class FooFactory implements FactoryInterface, MutableCreationOptionsInterface
{
    /**
     * @var array
     */
    protected $creationOptions;

    /**
     * @param array $creationOptions
     */
    public function __construct(array $creationOptions = array())
    {
        $this->creationOptions = $creationOptions;
    }

    /**
     * @param array $creationOptions
     */
    public function setCreationOptions(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }

    /**
     * @return array
     */
    public function getCreationOptions()
    {
        return $this->creationOptions;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Foo();
    }
}
