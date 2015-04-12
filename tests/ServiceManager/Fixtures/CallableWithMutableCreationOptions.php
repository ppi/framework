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
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Implements multiple interface invokable object mock.
 */
class CallableWithMutableCreationOptions implements MutableCreationOptionsInterface
{
    /**
     * @param array $options
     */
    public function setCreationOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @param $cName
     * @param $rName
     *
     * @return \stdClass
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator, $cName, $rName)
    {
        return new stdClass();
    }
}
