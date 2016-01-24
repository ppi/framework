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

use PPI\Framework\Http\Request as HttpRequest;
use PPI\Framework\Http\Response as HttpResponse;
use PPI\Framework\ServiceManager\ServiceManager;
use PPI\FrameworkTest\Fixtures\AppForDispatchTest;
use PPI\FrameworkTest\Fixtures\AppForTest;
use PPI\FrameworkTest\Fixtures\ControllerForAppTest;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AppTest.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class AppTest extends \PHPUnit_Framework_TestCase
{

    private $contollerUnderTest;

    public function setUp()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    public function testConstructor()
    {
        $env = 'test_env';
        $debug = true;
        $rootDir = __DIR__;
        $name = 'testName';
        $app = new AppForTest(array('environment' => $env, 'debug' => $debug, 'rootDir' => $rootDir, 'name' => $name,));

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
        $env = 'test_env';
        $debug = true;
        $app = new AppForTest(array('environment' => $env, 'debug' => $debug));

        $clone = clone $app;

        $this->assertEquals($env, $clone->getEnvironment());
        $this->assertEquals($debug, $clone->isDebug());
        $this->assertFalse($clone->isBooted());
        $this->assertLessThanOrEqual(microtime(true), $clone->getStartTime());
        $this->assertNull($clone->getContainer());
    }

    public function testGetRootDir()
    {
        $app = new AppForTest(array('environment' => 'test', 'debug' => true, 'rootDir' => __DIR__,));

        $this->assertEquals(__DIR__, realpath($app->getRootDir()));

        chdir(__DIR__);
        $app = new AppForTest(array('environment' => 'test', 'debug' => true));
        $this->assertEquals(__DIR__, realpath($app->getRootDir()));
    }

    public function testGetName()
    {
        $app = new AppForTest(array('environment' => 'test', 'debug' => true, 'rootDir' => __DIR__,));
        $this->assertEquals(basename(__DIR__), $app->getName());

        $app = new AppForTest(array('environment' => 'test', 'debug' => true, 'rootDir' => __DIR__, 'name' => 'testName',));
        $this->assertEquals('testName', $app->getName());
    }

    public function testRunWithControllerIndexAction()
    {
        $app = new AppForDispatchTest(array('environment' => 'test', 'debug' => true, 'rootDir' => __DIR__,));

        $this->controllerUnderTest = [new ControllerForAppTest(), 'indexAction'];

        $app = $this->setupAppMocks($app, $this->setupMockRouter(), $this->setupMockControllerResolver());

        $request = HttpRequest::createFromGlobals();
        $response = new HttpResponse();
        $output = $this->runApp($app, $request, $response);

        $this->assertEquals($output, 'Working Response From Controller Index Action');
    }

    public function testRunWithControllerInvokeAction()
    {
        $app = new AppForDispatchTest(array('environment' => 'test', 'debug' => true, 'rootDir' => __DIR__,));

        $this->controllerUnderTest = new ControllerForAppTest();

        $app = $this->setupAppMocks($app, $this->setupMockRouter(), $this->setupMockControllerResolver());

        $request = HttpRequest::createFromGlobals();
        $response = new HttpResponse();
        $output = $this->runApp($app, $request, $response);

        $this->assertEquals($output, 'Working Response From Controller Invoke Action');
    }

    public function testDispatch()
    {
        $app = new AppForDispatchTest(array('environment' => 'test', 'debug' => true, 'rootDir' => __DIR__,));

        $this->controllerUnderTest = [new ControllerForAppTest(), 'indexAction'];

        $app = $this->setupAppMocks($app, $this->setupMockRouter(), $this->setupMockControllerResolver());

        $request = HttpRequest::createFromGlobals();
        $response = new HttpResponse();

        $response = $app->dispatch($request, $response);
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($response->getContent(), 'Working Response From Controller Index Action');
    }

    private function setupMockRouter()
    {
        $mockRouter = $this->getMockBuilder('PPI\Framework\Router\ChainRouter')->disableOriginalConstructor()->getMock();
        $mockRouter->expects($this->once())->method('warmUp');
        $mockRouter->expects($this->once())->method('matchRequest')->willReturn(array('_controller' => 'ControllerForAppTest'));

        return $mockRouter;
    }

    public function setupMockControllerResolver()
    {

        $mockControllerResolver = $this->getMockBuilder('PPI\Framework\Module\Controller\ControllerResolver')->disableOriginalConstructor()->getMock();

        $mockControllerResolver->expects($this->once())->method('getController')->willReturn(
            $this->controllerUnderTest
        );

        $mockControllerResolver->expects($this->once())->method('getArguments')->willReturn(array());

        return $mockControllerResolver;
    }

    private function setupAppMocks($app, $mockRouter, $mockControllerResolver)
    {
        $sm = new ServiceManager();
        $sm->setAllowOverride(true);
        $sm->set('Router', $mockRouter);
        $sm->set('ControllerResolver', $mockControllerResolver);
        $app->setServiceManager($sm);

        return $app;
    }

    private function runApp($app, $request, $response)
    {
        ob_start();
        $response = $app->run($request, $response);
        $output = ob_get_clean();

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);

        return $output;
    }

}
