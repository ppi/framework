<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2015 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\FrameworkTest\Router;

use PPI\Framework\Router\RouterListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;

/**
 * Class RouterListenerTest.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class RouterListenerTest extends \PHPUnit_Framework_TestCase
{
    private $requestStack;

    protected function setUp()
    {
        $this->requestStack = $this->getMock('Symfony\Component\HttpFoundation\RequestStack', array(), array(), '',
            false);
    }

    /**
     * @dataProvider getPortData
     */
    public function testPort($defaultHttpPort, $defaultHttpsPort, $uri, $expectedHttpPort, $expectedHttpsPort)
    {
        $urlMatcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcherInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $context = new RequestContext();
        $context->setHttpPort($defaultHttpPort);
        $context->setHttpsPort($defaultHttpsPort);
        $urlMatcher->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context));
        $routerListener = new RouterListener($urlMatcher, null, null, $this->requestStack);
        $request        = $this->createRequestForUri($uri);
        $routerListener->match($request);
        $this->assertEquals($expectedHttpPort, $context->getHttpPort());
        $this->assertEquals($expectedHttpsPort, $context->getHttpsPort());
        $this->assertEquals(0 === strpos($uri, 'https') ? 'https' : 'http', $context->getScheme());
    }
    public function getPortData()
    {
        return array(
            array(80, 443, 'http://localhost/', 80, 443),
            array(80, 443, 'http://localhost:90/', 90, 443),
            array(80, 443, 'https://localhost/', 80, 443),
            array(80, 443, 'https://localhost:90/', 80, 90),
        );
    }
    /**
     * @param string $uri
     *
     * @return Request
     */
    private function createRequestForUri($uri)
    {
        $request = Request::create($uri);
        $request->attributes->set('_controller', null); // Prevents going in to routing process

        return $request;
    }
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidMatcher()
    {
        new RouterListener(new \stdClass(), null, null, $this->requestStack);
    }
    public function testRequestMatcher()
    {
        $request        = Request::create('http://localhost/');
        $requestMatcher = $this->getMock('Symfony\Component\Routing\Matcher\RequestMatcherInterface');
        $requestMatcher->expects($this->once())
            ->method('matchRequest')
            ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Request'))
            ->will($this->returnValue(array()));
        $routerListener = new RouterListener($requestMatcher, new RequestContext(), null, $this->requestStack);
        $routerListener->match($request);
    }
    public function testSubRequestWithDifferentMethod()
    {
        $request        = Request::create('http://localhost/', 'post');
        $requestMatcher = $this->getMock('Symfony\Component\Routing\Matcher\RequestMatcherInterface');
        $requestMatcher->expects($this->any())
            ->method('matchRequest')
            ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Request'))
            ->will($this->returnValue(array()));
        $context = new RequestContext();
        $requestMatcher->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context));
        $routerListener = new RouterListener($requestMatcher, new RequestContext(), null, $this->requestStack);
        $routerListener->match($request);

        // sub-request with another HTTP method
        $request = Request::create('http://localhost/', 'get');
        $routerListener->match($request);
        $this->assertEquals('GET', $context->getMethod());
    }
}
