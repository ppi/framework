<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\Router;

use PPI\FrameworkTest\Router\Fixtures\RoutePluginManagerForTest;
use PPI\Framework\ServiceManager\ServiceManager;
use PPI\FrameworkTest\ServiceManager\Fixtures\MockSelfReturningDelegatorFactory;
use ReflectionClass;
use ReflectionObject;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\Exception\RuntimeException;

/**
 * Class RoutePluginManagerTest.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class RoutePluginManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var RoutePluginManagerForTest
     */
    protected $pluginManager;

    public function setup()
    {
        $this->serviceManager = new ServiceManager();
        $this->pluginManager  = new RoutePluginManagerForTest(new Config(array(
            'factories' => array(
                'Foo' => 'PPI\FrameworkTest\ServiceManager\Fixtures\FooFactory',
            ),
            'shared' => array(
                'Foo' => false,
            ),
        )));
    }
    public function testSetMultipleCreationOptions()
    {
        $pluginManager = new RoutePluginManagerForTest(new Config(array(
            'factories' => array(
                'Foo' => 'PPI\FrameworkTest\ServiceManager\Fixtures\FooFactory',
            ),
            'shared' => array(
                'Foo' => false,
            ),
        )));
        $refl         = new ReflectionClass($pluginManager);
        $reflProperty = $refl->getProperty('factories');
        $reflProperty->setAccessible(true);
        $value = $reflProperty->getValue($pluginManager);
        $this->assertInternalType('string', $value['foo']);
        $pluginManager->get('Foo', array('key1' => 'value1'));
        $value = $reflProperty->getValue($pluginManager);
        $this->assertInstanceOf('PPI\FrameworkTest\ServiceManager\Fixtures\FooFactory', $value['foo']);
        $this->assertEquals(array('key1' => 'value1'), $value['foo']->getCreationOptions());
        $pluginManager->get('Foo', array('key2' => 'value2'));
        $value = $reflProperty->getValue($pluginManager);
        $this->assertInstanceOf('PPI\FrameworkTest\ServiceManager\Fixtures\FooFactory', $value['foo']);
        $this->assertEquals(array('key2' => 'value2'), $value['foo']->getCreationOptions());
    }

    public function testGetFaultyRegisteredInvokableThrowsException()
    {
        $this->setExpectedException('Zend\ServiceManager\Exception\RuntimeException');
        $pluginManager = new RoutePluginManagerForTest();
        $pluginManager->setInvokableClass('helloWorld', 'IDoNotExist');
        $pluginManager->get('helloWorld');
    }

    public function testAbstractFactoryWithMutableCreationOptions()
    {
        $creationOptions = array('key1' => 'value1');
        $mock            = 'PPI\FrameworkTest\ServiceManager\Fixtures\AbstractFactoryWithMutableCreationOptions';
        $abstractFactory = $this->getMock($mock, array('setCreationOptions'));
        $abstractFactory->expects($this->once())
            ->method('setCreationOptions')
            ->with($creationOptions);
        $this->pluginManager->addAbstractFactory($abstractFactory);
        $instance = $this->pluginManager->get('classnoexists', $creationOptions);
        $this->assertTrue(is_object($instance));
    }

    public function testMutableMethodNeverCalledWithoutCreationOptions()
    {
        $mock     = 'PPI\FrameworkTest\ServiceManager\Fixtures\CallableWithMutableCreationOptions';
        $callable = $this->getMock($mock, array('setCreationOptions'));
        $callable->expects($this->never())
            ->method('setCreationOptions');
        $ref    = new ReflectionObject($this->pluginManager);
        $method = $ref->getMethod('createServiceViaCallback');
        $method->setAccessible(true);
        $method->invoke($this->pluginManager, $callable, 'foo', 'bar');
    }

    public function testCallableObjectWithMutableCreationOptions()
    {
        $creationOptions = array('key1' => 'value1');
        $mock            = 'PPI\FrameworkTest\ServiceManager\Fixtures\CallableWithMutableCreationOptions';
        $callable        = $this->getMock($mock, array('setCreationOptions'));
        $callable->expects($this->once())
            ->method('setCreationOptions')
            ->with($creationOptions);
        $ref      = new ReflectionObject($this->pluginManager);
        $property = $ref->getProperty('creationOptions');
        $property->setAccessible(true);
        $property->setValue($this->pluginManager, $creationOptions);
        $method = $ref->getMethod('createServiceViaCallback');
        $method->setAccessible(true);
        $method->invoke($this->pluginManager, $callable, 'foo', 'bar');
    }

    public function testValidatePluginIsCalledWithDelegatorFactoryIfItsAService()
    {
        $pluginManager    = $this->getMockForAbstractClass('Zend\ServiceManager\AbstractPluginManager');
        $delegatorFactory = $this->getMock('Zend\\ServiceManager\\DelegatorFactoryInterface');
        $pluginManager->setService('delegator-factory', $delegatorFactory);
        $pluginManager->addDelegator('foo-service', 'delegator-factory');
        $pluginManager->expects($this->once())
            ->method('validatePlugin')
            ->with($delegatorFactory);
        $pluginManager->create('foo-service');
    }

    public function testSingleDelegatorUsage()
    {
        $delegatorFactory = $this->getMock('Zend\\ServiceManager\\DelegatorFactoryInterface');
        /* @var $pluginManager \Zend\ServiceManager\AbstractPluginManager|\PHPUnit_Framework_MockObject_MockObject */
        $pluginManager = $this->getMockForAbstractClass('Zend\ServiceManager\AbstractPluginManager');
        $realService   = $this->getMock('stdClass', array(), array(), 'RealService');
        $delegator     = $this->getMock('stdClass', array(), array(), 'Delegator');
        $delegatorFactory
            ->expects($this->once())
            ->method('createDelegatorWithName')
            ->with(
                $pluginManager,
                'fooservice',
                'foo-service',
                $this->callback(function ($callback) use ($realService) {
                    if (!is_callable($callback)) {
                        return false;
                    }

                    return call_user_func($callback) === $realService;
                })
            )
            ->will($this->returnValue($delegator));
        $pluginManager->setFactory('foo-service', function () use ($realService) {
            return $realService;
        });
        $pluginManager->addDelegator('foo-service', $delegatorFactory);
        $pluginManager->expects($this->once())
            ->method('validatePlugin')
            ->with($delegator);
        $this->assertSame($delegator, $pluginManager->get('foo-service'));
    }

    public function testMultipleDelegatorsUsage()
    {
        /* @var $pluginManager \Zend\ServiceManager\AbstractPluginManager|\PHPUnit_Framework_MockObject_MockObject */
        $pluginManager = $this->getMockForAbstractClass('Zend\ServiceManager\AbstractPluginManager');
        $fooDelegator  = new MockSelfReturningDelegatorFactory();
        $barDelegator  = new MockSelfReturningDelegatorFactory();
        $pluginManager->addDelegator('foo-service', $fooDelegator);
        $pluginManager->addDelegator('foo-service', $barDelegator);
        $pluginManager->setInvokableClass('foo-service', 'stdClass');
        $pluginManager->expects($this->once())
            ->method('validatePlugin')
            ->with($barDelegator);
        $this->assertSame($barDelegator, $pluginManager->get('foo-service'));
        $this->assertCount(1, $barDelegator->instances);
        $this->assertCount(1, $fooDelegator->instances);
        $this->assertInstanceOf('stdClass', array_shift($fooDelegator->instances));
        $this->assertSame($fooDelegator, array_shift($barDelegator->instances));
    }

    public function testCanCheckInvalidServiceManagerIsUsed()
    {
        $sm = new ServiceManager();
        $sm->setService('bar', new \stdClass());
        /** @var \Zend\ServiceManager\AbstractPluginManager $pluginManager */
        $pluginManager = new RoutePluginManagerForTest();
        $pluginManager->setServiceLocator($sm);
        $this->setExpectedException('Zend\ServiceManager\Exception\ServiceLocatorUsageException');
        $pluginManager->get('bar');
        $this->fail('A Zend\ServiceManager\Exception\ServiceNotCreatedException is expected');
    }

    public function testWillRethrowOnNonValidatedPlugin()
    {
        $sm = new ServiceManager();
        $sm->setInvokableClass('stdClass', 'stdClass');
        /** @var \Zend\ServiceManager\AbstractPluginManager|\PHPUnit_Framework_MockObject_MockObject $pluginManager */
        $pluginManager = $this->getMockForAbstractClass('Zend\ServiceManager\AbstractPluginManager');
        $pluginManager
            ->expects($this->once())
            ->method('validatePlugin')
            ->with($this->isInstanceOf('stdClass'))
            ->will($this->throwException(new RuntimeException()));
        $pluginManager->setServiceLocator($sm);
        $this->setExpectedException('Zend\ServiceManager\Exception\ServiceLocatorUsageException');
        $pluginManager->get('stdClass');
    }

    public function testWillResetAutoInvokableServiceIfNotValid()
    {
        /** @var \Zend\ServiceManager\AbstractPluginManager|\PHPUnit_Framework_MockObject_MockObject $pluginManager */
        $pluginManager = $this->getMockForAbstractClass('Zend\ServiceManager\AbstractPluginManager');
        $pluginManager
            ->expects($this->any())
            ->method('validatePlugin')
            ->will($this->throwException(new RuntimeException()));
        $pluginManager->setInvokableClass(__CLASS__, __CLASS__);
        try {
            $pluginManager->get('stdClass');
            $this->fail('Expected the plugin manager to throw a RuntimeException, none thrown');
        } catch (RuntimeException $exception) {
            $this->assertFalse($pluginManager->has('stdClass'));
        }
        try {
            $pluginManager->get(__CLASS__);
            $this->fail('Expected the plugin manager to throw a RuntimeException, none thrown');
        } catch (RuntimeException $exception) {
            $this->assertTrue($pluginManager->has(__CLASS__));
        }
    }
}
