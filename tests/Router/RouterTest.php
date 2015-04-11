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

use PPI\Framework\Router\Router;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RouterTest.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    private $router = null;

    protected function setUp()
    {
        $requestContext = $this->getMock('Symfony\Component\Routing\RequestContext');
        $collection     = $this->getMock('Symfony\Component\Routing\RouteCollection');
        $options        = array();
        $logger         = null;

        $this->router   = new Router($requestContext, $collection, $options, $logger);
    }

    public function testSetOptionsWithSupportedOptions()
    {
        $this->router->setOptions([
            'cache_dir'     => './cache',
            'debug'         => true,
            'resource_type' => 'ResourceType',
        ]);
        $this->assertSame('./cache', $this->router->getOption('cache_dir'));
        $this->assertTrue($this->router->getOption('debug'));
        $this->assertSame('ResourceType', $this->router->getOption('resource_type'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the following options: "option_foo", "option_bar"
     */
    public function testSetOptionsWithUnsupportedOptions()
    {
        $this->router->setOptions([
            'cache_dir'     => './cache',
            'option_foo'    => true,
            'option_bar'    => 'baz',
            'resource_type' => 'ResourceType',
        ]);
    }

    public function testSetOptionWithSupportedOption()
    {
        $this->router->setOption('cache_dir', './cache');
        $this->assertSame('./cache', $this->router->getOption('cache_dir'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the "option_foo" option
     */
    public function testSetOptionWithUnsupportedOption()
    {
        $this->router->setOption('option_foo', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the "option_foo" option
     */
    public function testGetOptionWithUnsupportedOption()
    {
        $this->router->getOption('option_foo', true);
    }

    /**
     * @return array
     */
    public function provideMatcherOptionsPreventingCaching()
    {
        return array(
            array('cache_dir'),
            array('matcher_cache_class'),
        );
    }

    /**
     * @return array
     */
    public function provideGeneratorOptionsPreventingCaching()
    {
        return array(
            array('cache_dir'),
            array('generator_cache_class'),
        );
    }

    public function testMatchRequestWithUrlMatcherInterface()
    {
        $matcher = $this->getMock('Symfony\Component\Routing\Matcher\UrlMatcherInterface');
        $matcher->expects($this->once())->method('match');
        $p = new \ReflectionProperty($this->router, 'matcher');
        $p->setAccessible(true);
        $p->setValue($this->router, $matcher);
        $this->router->matchRequest(Request::create('/'));
    }

    public function testMatchRequestWithRequestMatcherInterface()
    {
        $matcher = $this->getMock('Symfony\Component\Routing\Matcher\RequestMatcherInterface');
        $matcher->expects($this->once())->method('matchRequest');
        $p = new \ReflectionProperty($this->router, 'matcher');
        $p->setAccessible(true);
        $p->setValue($this->router, $matcher);
        $this->router->matchRequest(Request::create('/'));
    }
}
