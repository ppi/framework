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

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FooInitializer implements InitializerInterface
{
    public $sm;

    protected $var;

    public function __construct($var = null)
    {
        if ($var) {
            $this->var = $var;
        }
    }

    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
        if ($this->var) {
            list($key, $value) = each($this->var);
            $instance->{$key}  = $value;
        }
    }
}
