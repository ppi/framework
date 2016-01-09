<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest;

use PPI\Framework\ServiceManager\ServiceManager;
use PPI\FrameworkTest\Fixtures\AppForDispatchTest;
use PPI\FrameworkTest\Fixtures\AppForTest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AppTest.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $env     = 'test_env';
        $debug   = true;
        $rootDir = __DIR__;
        $name    = 'testName';
        $app     = new AppForTest(array(
            'environment'   => $env,
            'debug'         => $debug,
            'rootDir'       => $rootDir,
            'name'          => $name,
        ));

        $this->assertEquals($env, $app->getEnvironment());
        $this->assertEquals($debug, $app->isDebug());
        $this->assertEquals($rootDir, $app->getRootDir());
        $this->assertEquals($name, $app->getName());
        $this->assertFalse($app->isBooted());
        $this->assertLessThanOrEqual(microtime(true), $app->getStartTime());
        $this->assertNull($app->getServiceManager());
        $this->assertNull($app->getContainer());
    }

    public function testClone()
    {
        $env   = 'test_env';
        $debug = true;
        $app   = new AppForTest(array('environment' => $env, 'debug' => $debug));

        $clone = clone $app;

        $this->assertEquals($env, $clone->getEnvironment());
        $this->assertEquals($debug, $clone->isDebug());
        $this->assertFalse($clone->isBooted());
        $this->assertLessThanOrEqual(microtime(true), $clone->getStartTime());
        $this->assertNull($clone->getContainer());
    }

    public function testGetRootDir()
    {
        $app = new AppForTest(array(
            'environment'   => 'test',
            'debug'         => true,
            'rootDir'       => __DIR__,
        ));

        $this->assertEquals(__DIR__, realpath($app->getRootDir()));

        chdir(__DIR__);
        $app = new AppForTest(array('environment' => 'test', 'debug' => true));
        $this->assertEquals(__DIR__, realpath($app->getRootDir()));
    }

    public function testGetName()
    {
        $app = new AppForTest(array(
            'environment'   => 'test',
            'debug'         => true,
            'rootDir'       => __DIR__,
        ));
        $this->assertEquals(basename(__DIR__), $app->getName());

        $app = new AppForTest(array(
            'environment'   => 'test',
            'debug'         => true,
            'rootDir'       => __DIR__,
            'name'          => 'testName',
        ));
        $this->assertEquals('testName', $app->getName());
    }

    public function testRun()
    {
        $app = new AppForDispatchTest(array(
            'environment'   => 'test',
            'debug'         => true,
            'rootDir'       => __DIR__,
        ));

        $mockRouter = $this->getMockBuilder('\PPI\Framework\Router\ChainRouter')
            ->disableOriginalConstructor()->getMock();
        $mockRouter->expects($this->once())->method('warmUp');
        $mockRouter->expects($this->once())->method('matchRequest')
            ->willReturn(array('_controller' => 'TestController'));

        $mockControllerResolver = $this->getMockBuilder('\PPI\Framework\Module\Controller\ControllerResolver')
            ->disableOriginalConstructor()->getMock();
        $mockControllerResolver->expects($this->once())->method('getController')
            ->willReturnCallback(function () {
                return function () { return new Response('Working Response'); };
            }
        );
        $mockControllerResolver->expects($this->once())->method('getArguments')->willReturn(array());

        $sm = new ServiceManager();
        $sm->setAllowOverride(true);
        $sm->set('Router', $mockRouter);
        $sm->set('ControllerResolver', $mockControllerResolver);
        $app->setServiceManager($sm);

        $response = $app->run();
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($response->getContent(), 'Working Response');
    }
}
