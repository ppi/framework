<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\ServiceManager;

use PPI\Framework\ServiceManager\ServiceManagerBuilder;
use Psr\Log\NullLogger;

class ServiceManagerBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testNullLoggerIsUsedIfNoneProvided()
    {
        $builder = new ServiceManagerBuilder(array());
        $serviceManager = $builder->build($this->getAppParameters());

        $this->assertTrue($serviceManager->has('logger'));
        $this->assertInstanceOf('Psr\Log\NullLogger', $serviceManager->get('logger'));
    }

    public function testNullLoggerIsNotUsedIfAnotherLoggerProvided()
    {
        $builder = new ServiceManagerBuilder(array(
            'service_manager' => array(
                'invokables' => array(
                    'Logger' => 'PPI\FrameworkTest\ServiceManager\MyLogger',
                ),
            ),
        ));
        $serviceManager = $builder->build($this->getAppParameters());

        $this->assertTrue($serviceManager->has('logger'));
        $this->assertInstanceOf('Psr\Log\NullLogger', $serviceManager->get('logger'));
    }

    /**
     * @return array
     *
     * NOTE: the following app.* parameters are required by the TemplatingConfig.
     */
    private function getAppParameters()
    {
        return array(
            'app.root_dir'  => 'foo',
            'app.cache_dir' => 'bar',
            'app.charset'   => 'baz',
        );
    }
}

class MyLogger extends NullLogger
{
}
